<?php
require_once("docutil.php");
page_head("Client scheduling policies");
echo "

This document describes two interrelated scheduling policies
in the BOINC client:

<ul>
<li> <b>CPU scheduling policy</b>: what result to run when.
<li> <b>Work fetch policy</b>: when to contact scheduling servers,
which projects to contact, and how much work to task for.
</ul>

<h2>CPU scheduling</h2>

<p>The CPU scheduling policy aims to achieve the following goals
(in decreasing priority):</p>

<ol>

<li>
<b>Maximize CPU utilization</b>.

<li>
<b>Enforce resource shares.</b>
The ratio of CPU time allocated to projects that have work,
in a typical period of a day or two,
should be approximately the same as the ratio of
the user-specified resource shares.
If a process has no work for some period,
it does not accumulate a 'debt' of work.

<li>
<b>Satisfy result deadlines if possible.</b>

<li>
<b>Reschedule CPUs periodically.</b>
This goal stems from the large differences in duration of
results from different projects.
Participant in multiple projects
will expect to see their computers do work on each of these projects in a
reasonable time period.

<li>
<b>Minimize mean time to completion.</b>
For example, it's better to have one result from
a project complete in time T than to have two results
simultaneously complete in time 2T.

</ol>

<p>
A result is 'active' if there is a slot directory for it.
There can be more active results than CPUs.


<h3>Debt</h3>

<p>
The notion of 'debt' is used to respect the resource share allocation
for each project.
The debt to a project represents the amount of work
(in CPU time) we owe it.
Debt is decreased when CPU time is devoted to a project.
We increase the debt to a project according to the
total amount of work done in a time period scaled by the project's
resource share.

<p>
For example, consider a system participating in two projects, A and B,
with resource shares 75% and 25%, respectively.
Suppose in some time period, the system devotes 25 minutes of CPU time to project A
and 15 minutes of CPU time to project B.
We decrease the debt to A by 25 minutes and increase it by 30 minutes (75% of 25 + 15).
So the debt increases overall.
This makes sense because we expected to devote a
larger percentage of the system resources to project A than it
actually got.

<p>
The choice of projects for which to start result computations
can simply follow the debt ordering of the projects.
The algorithm computes the 'anticipated debt' to a project
(the debt we expect to owe after the time period expires)
as it chooses result computations to run.

<h3>A sketch of the CPU scheduling algorithm</h3>

<p>
This algorithm is run:
<ul>
<li> Whenever a CPU is free
<li> Whenever a new result arrives (via scheduler RPC)
<li> Whenever it hasn't run for MV seconds, for some scheduling period
MV
</ul>

<p>
We will attempt to minimize the number of active result
computations for a project by dynamically choosing results to compute
from a global pool.
When we allocate CPU time to project,
we will choose already running tasks first,
then preempted tasks, and only choose to start a new result
computation in the last resort.
This will not guarantee the above
property, but we hope it will be close to achieving it.

<ol>

<li>Decrease debts to projects according to the amount of work done for
the projects in the last period.

<li>Increase debts to projects according to the projects' resource shares.

<li>Let the anticipated debt for each project be initialized to
its current debt.

<li>Repeat until we decide on a result to compute for each processor:

<ol>

<li>Choose the project that has the largest anticipated debt and a
ready-to-compute result.

<li>Decrease the anticipated debt of the project by the expected amount of CPU time.

</ol>

<li>Preempt current result computations, and start new ones.

</ol>

<h3>Pseudocode</h3>

<pre>
data structures:
ACTIVE_TASK:
    double cpu_time_at_last_sched
    double current_cpu_time
    scheduler_state:
        PREEMPTED
        RUNNING
    next_scheduler_state    // temp
PROJECT:
    double work_done_this_period    // temp
    double debt
    double anticipated_debt // temp
    RESULT next_runnable_result

schedule_cpus():

foreach project P
    P.work_done_this_period = 0

total_work_done_this_period = 0
foreach task T that is RUNNING:
    x = T.current_cpu_time - T.cpu_time_at_last_sched
    T.project.work_done_this_period += x
    total_work_done_this_period += x

foreach P in projects:
    P.debt += P.resource_share * total_work_done_this_period
            - P.work_done_this_period

expected_pay_off = total_work_done_this_period / num_cpus

foreach P in projects:
    P.anticipated_debt = P.debt

foreach task T
    T.next_scheduler_state = PREEMPTED

do num_cpus times:
    // choose the project with the largest anticipated debt
    P = argmax { P.anticipated_debt } over all P in projects with runnable result
    if none:
        break
    if (some T (not already scheduled to run) for P is RUNNING):
        T.next_scheduler_state = RUNNING
        P.anticipated_debt -= expected_pay_off
        continue
    if (some T (not already scheduled to run) for P is PREEMPTED):
        T.next_scheduler_state = RUNNING
        P.anticipated_debt -= expected_pay_off
        continue
    if (some R in results is for P, not active, and ready to run):
        Choose R with the earliest deadline
        T = new ACTIVE_TASK for R
        T.next_scheduler_state = RUNNING
        P.anticipated_debt -= expected_pay_off

foreach task T
    if scheduler_state == PREEMPTED and next_scheduler_state = RUNNING
        unsuspend or run
    if scheduler_state == RUNNING and next_scheduler_state = PREEMPTED
        suspend (or kill)

foreach task T
    T.cpu_time_at_last_sched = T.current_cpu_time
</pre>

<h2>Work fetch policy</h2>

<p>
The CPU scheduler is at its best when projects always have runnable
results.
When the CPU scheduler chooses to run a project without a runnable
result, we say the CPU scheduler is 'starved'.

<p>
The work fetch policy has the following goal:

<ul>
<li>
Maintain sufficient work so that the CPU scheduler
is never starved.
<li>
More specifically:
given a 'connection period' parameter T (days),
always maintain sufficient work so that the CPU scheduler will
not be starved for T days,
given average work processing rate.
The client should contact scheduling servers only about every T days.
<li>
Don't fetch more work than necessary, given the above goals.
Thus, try to maintain enough work that starvation will occur
between T and 2T days from now.
</ul>

<h3>When to get work</h3>

<p>
At a given time, the CPU scheduler may need as many as

<blockquote>
min_results(P) = ceil(ncpus * P.resource_share)
</blockquote>

<p>
results for project P to avoid starvation.
The client can estimate the amount of time that will elapse until
the number of runnable results falls below min_results(P)
for some project P.
When this length of time is less than T,
it is time to get more work for project P.

<h3>A sketch of the work fetch algorithm</h3>

The algorithm sets P.work_request for each project P
and returns an 'urgency level':

<pre>
NEED_WORK_IMMEDIATELY
    CPU scheduler is currently starved (may not have idle CPU)
NEED_WORK
    Will starve within T days
DONT_NEED_WORK
    otherwise
</pre>
It can be called whenever the client can make a scheduler RPC.
<p>
<ol>
<li>
For each project P
<ol>
<li>
Let R0...Rn-1 be P's runnable results ordered by decreasing deadline.
<li>
Let S be the sum of estimated duration E(R) of R(min_results-1),...Rn-1,
where E(R) is R's FLOPS estimate
divided by this host's average processing rate times P.resource_share.
(i.e., S is the expected time until starvation for P).
<li>
If S < T
<ol>
<li>If S == 0: urgency = NEED_WORK_IMMEDIATELY
<li>P.work_request = (2T - S) * P.resource_share * average processing rate
<li>urgency = max(urgency, NEED_WORK)
</ol>
<li>
Else, P.work_request = 0
</ol>
<li>
Return urgency
</ol>

<p>
The mechanism for actually getting work checks if a project has a
non-zero work request and if so, makes the scheduler RPC call to
request the work.

<h3>Pseudocode</h3>

<pre>
data structures:
PROJECT:
    double work_request

compute_work_request():

    urgency = 0

    foreach project P:
        project_active_frac = active_frac * P.resource_share
        time_to_starvation = 0
        results_to_skip = min_results(P) - 1
        P.work_request = 0

        foreach result R for P in order of decreasing deadline:
            if results_to_skip > 0:
                results_to_skip--
                continue
            time_to_starvation += cpu_time(R) / project_active_frac

        if time_to_starvation < T:
            if time_to_starvation == 0:
                urgency = NEED_WORK_IMMEDIATELY
            P.work_request =
                (2*T - time_to_starvation)*project_active_frac
            urgency = max(NEED_WORK, urgency)

    return urgency

</pre>

";
page_tail();
?>

