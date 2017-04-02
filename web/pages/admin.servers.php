<div id="admin-page-content">
<?php
if (!defined("IN_SB")) {
    echo "You should not be here. Only follow links!";
    die();
}
global $userbank, $theme;

$database->query(
    "SELECT srv.ip ip, srv.port port, srv.sid sid, mo.icon icon, srv.enabled enabled FROM `:prefix_servers` AS srv
    LEFT JOIN `:prefix_mods` AS mo ON mo.mid = srv.modid ORDER BY modid, sid"
);
$servers = $database->resultset();
$database->query("SELECT COUNT(sid) AS cnt FROM `:prefix_servers`");
$server_count = $database->single();

$server_access = array();
if ($userbank->HasAccess(SM_RCON . SM_ROOT)) {
    // Get all servers the admin has access to
    $database->query("SELECT server_id, srv_group_id FROM `:prefix_admins_servers_groups` WHERE admin_id = :adminId");
    $database->bind(':adminId', $userbank->getAid());
    $servers2 = $database->resultset();
    foreach ($servers2 as $server) {
        $server_access[] = $server['server_id'];
        if ($server['srv_group_id'] > 0) {
            $database->query("SELECT server_id FROM `:prefix_servers_groups` WHERE group_id = :groupId");
            $database->bind(':groupId', $server['srv_group_id'], \PDO::PARAM_INT);
            $servers_in_group = $database->resultset();
            foreach ($servers_in_group as $servig) {
                $server_access[] = $servig['server_id'];
            }
        }
    }
}

// Only show the RCON link for servers he's access to
foreach ($servers as &$server) {
    if (in_array($server['sid'], $server_access)) {
        $server['rcon_access'] = true;
    } else {
        $server['rcon_access'] = false;
    }
}

// List mods
$database->query("SELECT mid, name FROM `:prefix_mods` WHERE mid > 0 AND enabled = 1 ORDER BY name ASC");
$modlist = $database->resultset();
// List groups
$database->query("SELECT gid, name FROM `:prefix_groups` WHERE type = 3 ORDER BY name ASC");
$grouplist = $database->resultset();

// Vars for server list
$theme->assign('permission_list', $userbank->HasAccess(ADMIN_OWNER | ADMIN_LIST_SERVERS));
$theme->assign('permission_editserver', $userbank->HasAccess(ADMIN_OWNER | ADMIN_EDIT_SERVERS));
$theme->assign('pemission_delserver', $userbank->HasAccess(ADMIN_OWNER | ADMIN_DELETE_SERVERS));
$theme->assign('server_count', $server_count['cnt']);
$theme->assign('server_list', $servers);

// Vars for add server
$theme->assign('permission_addserver', $userbank->HasAccess(ADMIN_OWNER | ADMIN_ADD_SERVER));
$theme->assign('modlist', $modlist);
$theme->assign('grouplist', $grouplist);
// set vars from edit form
$theme->assign('edit_server', false);
$theme->assign('ip', '');
$theme->assign('port', '');
$theme->assign('rcon', '');
$theme->assign('modid', '');

$theme->assign('submit_text', "Add Server");
?>
    <div id="0" style="display:none;">
<?php
$theme->display('page_admin_servers_list.tpl');
?>
    </div>
    <div id="1" style="display:none;">
<?php
$theme->display('page_admin_servers_add.tpl');
?>
    </div>
</div>
