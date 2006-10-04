<?php
require_once("docutil.php");
page_head("Version history");
echo"

<h3>Version 5.5</h3>
<ul>
<li> New CPU Scheduler.
<li> CPU Capability detection.
<li> Revamped logging solution.
<li> Improved security for POSIX compliant systems
(only implemented for the Mac)
<li> Add support for file compression on upload.
</ul>

<h3>Version 5.4</h3>
<ul>
<li>
BOINC now lets you use 'Account managers' - special web sites
that let you browse BOINC projects, attach/detach,
change resource share and settings,
all with point-and-click simplicity.
Account managers are also great if you have several computers -
you just have to make changes once.
A couple of excellent account managers are nearly ready for use;
stay tuned to the
<a href=http://boinc.berkeley.edu/>BOINC web site</a> for details.
<li>
Fixes a bug where a personal firewall prevents the BOINC screensaver
from running, causing your computer to lock up.
<li>
General preferences can be overridden by a local file
details are <a href=http://boinc.berkeley.edu/prefs_override.php>here</a>.
<li>
BOINC now alerts you whenever it needs you
to create a network connection.
<li> SOCKS5 proxies are now supported.
</ul>

<h3>Version 5.3</h3>
<ul>
<li> Customized clients can now be created.
<li> Account manager support has been enhanced.
<li> Farm manager support has been added.
</ul>
<h3>Version 5.2</h3>
<ul>
<li> You attach to projects using your own email address and password.
Long, random 'account keys' are no longer used.
<li> BOINC checks for proxy configuration problems
when you first attach to a project.
<li>
The file gui_rpc_auth.cfg contains a password that
protects the BOINC client from control by other users on the same host.
BOINC automatically generates a password if none is found.
If you use tools like BOINCView you may need to look it up or change it.
If you need to revert from 5.2.x to an earlier version,
you must delete gui_rpc_auth.cfg from your BOINC directory.
<li> Alert boxes now provide feedback when certain errors occur.
<li> BOINC supports 'Account Management Systems' (to be announced).
</ul>

";
page_tail();
?>