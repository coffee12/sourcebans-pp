<?php
/*************************************************************************
This file is part of SourceBans++

Copyright � 2014-2016 SourceBans++ Dev Team <https://github.com/sbpp>

SourceBans++ is licensed under a
Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-nc-sa/3.0/>.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

This program is based off work covered by the following copyright(s):
SourceBans 1.4.11
Copyright � 2007-2014 SourceBans Team - Part of GameConnect
Licensed under CC BY-NC-SA 3.0
Page: <http://www.sourcebans.net/> - <http://www.gameconnect.net/>
*************************************************************************/

global $userbank, $theme;
$database->query("SELECT * FROM `:prefix_admins` ORDER BY user ASC");
$admin_list   = $database->resultset();
$database->query("SELECT sid, ip, port FROM `:prefix_servers` WHERE enabled = '0'");
$server_list  = $database->resultset();
$servers      = array();
$serverscript = "<script type=\"text/javascript\">";
foreach ($server_list as $server) {
    $info = array();
    $serverscript .= "xajax_ServerHostPlayers('".$server['sid']."', 'id', 'ss".$server['sid']."', '', '', false, 200);";
    $info['sid']  = $server['sid'];
    $info['ip']   = $server['ip'];
    $info['port'] = $server['port'];
    array_push($servers, $info);
}
$serverscript .= "</script>";
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$theme->assign(
    'hideplayerips',
    (isset($GLOBALS['config']['banlist.hideplayerips'])
    && $GLOBALS['config']['banlist.hideplayerips'] == "1" && !$userbank->is_admin())
);
$theme->assign('is_admin', $userbank->is_admin());
$theme->assign('admin_list', $admin_list);
$theme->assign('server_list', $servers);
$theme->assign('server_script', $serverscript);

$theme->display('box_admin_bans_search.tpl');
?>
<script type="text/javascript">
function switch_length(opt)
{
    if (opt.options[opt.selectedIndex].value=='other') {
        $('other_length').setStyle('display', 'block');
        $('other_length').focus();
        $('length').setStyle('width', '20px');
    } else {
        $('other_length').setStyle('display', 'none');
        $('length').setStyle('width', '210px');
    }
}
</script>
