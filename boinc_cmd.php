<?php

require_once("docutil.php");

page_head("The BOINC command tool");
echo "
<p>
The BOINC command tool (boinc_cmd)
provides a command-line interface to a running BOINC core client.
boinc_cmd has the following interface:
<pre>
boinc_cmd [--host hostname] [--passwd passwd] command
</pre>
The options and commands are as follows:
";
list_start();
list_bar("Core client connection");
list_item("--host hostname[:port]", "The host to connect to 
    (default: localhost)");
list_item("--password", "The password for RPC authentication
    (default: boinc_cmd will look for a file 'gui_rpc_auth.cfg'
     and use the password it contains)"
);
list_bar("Account query and attach");
list_item("--lookup_account URL email password",
    "Look up account and print account key"
);
list_item("--create_account URL email password name",
    "Create account with the given email address, password, and user name"
);
list_item("--project_attach URL auth","Attach to an account");
list_item("--join_acct_mgr URL name password",
    "Attach to an account manager (or do RPC if already attached)."
);
list_item("--quit_acct_mgr",
    "Quit the current account manager."
);
list_bar("State queries");
list_item("--get_state", "show complete client state");
list_item("--get_results", "show results");
list_item("--get_simple_gui_info", "show projects and active results");
list_item("--get_file_transfers", "show file transfers");
list_item("--get_project_status", "show status of all projects");
list_item("--get_disk_usage", "Show disk usage by project");
list_item("--get_proxy_settings", "Get proxy settings");
list_item("--get_messages seqno",
    "show messages with sequence numbers beyond the given seqno"
);
list_item("--get_host_info", "Show host info");
list_item("--get_screensaver_mode", "");
list_item("--version, -V", "show core client version");
list_bar("Control operations");
list_item("--result URL result_name
     <br>{suspend | resume | abort | graphics_window | graphics_fullscreen}
     <br>{--window_station ws} {--desktop dt} {--display dp}
     ",
     "Do operation on a result, identified by the project master URL
     and the result name.
     <ul>
     <li> suspend: temporarily stop work on result
     <li> resume: allow work on result
     <li> abort: permanently stop work on result
     <li> graphics_window: open graphics in a window.
     The optional desktop/window_station (Windows) or display (X11)
     arguments specify the display.
     <li> graphics_fullscreen: open graphics fullscreen
     </ul>
     "
);
list_item("--project URL
    <br>{reset | detach | update | suspend | resume | nomorework | allowmorework}
    ",
    "Do operation on a project, identified by its master URL.
    <ul>
    <li>reset: delete current work and get more;
    <li>detach: delete current work and don't get more;
    <li>update: contact scheduling server;
    <li>suspend: stop work for project;
    <li>result: resume work for projrect;
    <li>nomorework: finish current work but don't get more;
    <li>allowmorework: undo nomorework
    </ul>
    "
);
list_item("--file_transfer URL filename
        {retry | abort}
        ",
    "Do operation on a file transfer"
);
list_item("--set_run_mode {always | auto | never} [ duration ]",
    "Set run mode.
    <br>always: do CPU work always
    <br>auto: do work only when allowed by preferences
    <br>never: don't do work
    <p>
    If duration is zero or absent, this mode is permanent.
    Otherwise, after 'duration' seconds elapse, revert to last permanent mode.
    "
);
list_item("--set_network_mode {always | auto | never} [ duration ]",
    "Set network mode
    <br> Like set_run_mode but applies to network transfers
    "
);
list_item(
    "--set_proxy_settings
    http_server_name
    http_server_port
    http_user_name
    http_user_passwd
    socks_server_name
    socks_server_port
    socks_version
    socks5_user_name
    socks5_user_passwd
    ",
    "Set proxy settings (all fields are mandatory)"
);
list_item("--run_benchmarks", "Run CPU benchmarks");
list_item(
    "--set_screensaver_mode on|off blank_time
    <br>{--desktop desktop}
    <br>{--window_station window_station}
    <br>{--display display}
    ",
    "Tell the core client to start or stop doing fullscreen graphics,
    and going to black after blank_time seconds.
    The optional arguments specify which desktop/windows_station (Windows)
    or display (X11) to use.
    "
);
list_item("--read_global_prefs_override",
    "Tell the core client to read the <code>global_prefs_override.xml</code>
    file, and incorporate any global preferences indicated there.
    "
);
list_item("--quit", "Tell the core client to quit");
list_item("--read_cc_config", "Reread the configuration file (cc_config.xml)");
list_bar("Miscellaneous");
list_item("--help, -h", "show options and commands");
list_end();

page_tail();
?>
