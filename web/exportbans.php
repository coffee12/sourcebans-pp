<?php
include_once("init.php");
$exportpublic = (isset($GLOBALS['config']['config.exportpublic']) && $GLOBALS['config']['config.exportpublic'] == "1");
if (!$userbank->HasAccess(ADMIN_OWNER) && !$exportpublic) {
    echo "Don't have access to this feature.";
} elseif (!isset($_GET['type'])) {
    echo "You have to specify the type. Only follow links!";
} else {
    if ($_GET['type'] == 'steam') {
        header('Content-Type: application/x-httpd-php php');
        header('Content-Disposition: attachment; filename="banned_user.cfg"');
        $database->query("SELECT authid FROM `:prefix_bans` WHERE length = '0' AND RemoveType IS NULL AND type = '0'");
        $bans = $database->resultset();
        foreach ($bans as $data) {
            print "banid 0 ".$data['authid']."\r\n";
        }
    } elseif ($_GET['type'] == 'ip') {
        header('Content-Type: application/x-httpd-php php');
        header('Content-Disposition: attachment; filename="banned_ip.cfg"');
        $database->query("SELECT ip FROM `:prefix_bans` WHERE length = '0' AND RemoveType IS NULL AND type = '1'");
        $bans = $database->resultset();
        foreach ($bans as $data) {
            print "addip 0 ".$data['ip']."\r\n";
        }
    }
}
