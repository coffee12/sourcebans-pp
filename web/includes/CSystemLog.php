<?php
/*************************************************************************
	This file is part of SourceBans++

	Copyright © 2014-2016 SourceBans++ Dev Team <https://github.com/sbpp>

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
		Copyright © 2007-2014 SourceBans Team - Part of GameConnect
		Licensed under CC BY-NC-SA 3.0
		Page: <http://www.sourcebans.net/> - <http://www.gameconnect.net/>
*************************************************************************/
namespace SourceBans;

class CSystemLog
{
    private $logList = array();
    private $type = "";
    private $title = "";
    private $msg = "";
    private $aid = 0;
    private $host = "";
    private $created = 0;
    private $parentFunction = "";
    private $query = "";

    private $database = null;

    public function __construct(
        Database $database = null,
        $type = "",
        $ttl = "",
        $msg = "",
        $host = "",
        $done = null,
        $query = null
    ) {
        $this->database = $database;
        
        global $userbank;
        if (!empty($type) && !empty($ttl) && !empty($msg)) {
            $this->type = $type;
            $this->title = $ttl;
            $this->msg = $msg;

            if (!$userbank) {
                return false;
            }

            $this->aid = $userbank->GetAid() ? $userbank->GetAid() : "-1";
            $this->host = filter_input(INPUT_SERVER, $host); //$_SERVER['REMOTE_ADDR']
            $this->created = time();
            $this->parentFunction = $this->getCaller();
            $this->query = is_null($query) ? '' : filter_input(INPUT_SERVER, $query); //$_SERVER['QUERY_STRING']
            if (!is_null($done)) {
                $this->WriteLog();
            }
        }
    }

    public function addLogItem($tpe, $ttl, $msg, $host, $query)
    {
        $item = array();
        $item['type'] = $tpe;
        $item['title'] = $ttl;
        $item['msg'] = $msg;
        $item['aid'] =  SB_AID;
        $item['host'] = filter_input(INPUT_SERVER, $host);//$_SERVER['REMOTE_ADDR']
        $item['created'] = time();
        $item['parentFunction'] = $this->getCaller();
        $item['query'] = filter_input(INPUT_SERVER, $query);//$_SERVER['QUERY_STRING']

        array_push($this->logList, $item);
    }

    public function writeLogEntries()
    {
        $this->logList = array_unique($this->logList);
        foreach ($this->logList as $logentry) {
            if (!$logentry['query']) {
                $logentry['query'] = "N/A";
            }

            $data = array(
                ":type" => $logentry['type'],
                ":title" => $logentry['title'],
                ":message" => $logentry['msg'],
                ":function" => $logentry['parentFunction'],
                ":query" => $logentry['query'],
                ":aid" => $logentry['aid'],
                ":host" => $logentry['host'],
                ":created" => $logentry['created']
            );

            $this->database->query(
                "INSERT INTO `:prefix_log` (type, title, message, function, query, aid, host, created)
                VALUES (:type, :title, :message, :function, :query, :aid, :host, :created)"
            );

            $this->database->bindMultiple($data);
            $this->database->execute();
        }
        unset($this->logList);
    }

    public function writeLog()
    {
        if (!$this->query) {
            $this->query = "N/A";
        }

        $data = array(
            ":type" => $this->type,
            ":title" => $this->title,
            ":message" => $this->msg,
            ":function" => $this->parentFunction,
            ":query" => $this->query,
            ":aid" => $this->aid,
            ":host" => $this->host,
            ":created" => $this->created
        );

        $this->database->query(
            "INSERT INTO `:prefix_log` (type, title, message, function, query, aid, host, created)
            VALUES (:type, :title, :message, :function, :query, :aid, :host, :created)"
        );

        $this->database->bindMultiple($data);
        $this->database->execute();
    }

    private function getCaller()
    {
        $dbt = debug_backtrace();

        $functions = isset($dbt[2]['file']) ? $dbt[2]['file'] . " - " . $dbt[2]['line'] . "<br />" : '';
        $functions .= isset($dbt[3]['file']) ? $dbt[3]['file'] . " - " . $dbt[3]['line'] . "<br />" : '';
        $functions .= isset($dbt[4]['file']) ? $dbt[4]['file'] . " - " . $dbt[4]['line'] . "<br />" : '';
        $functions .= isset($dbt[5]['file']) ? $dbt[5]['file'] . " - " . $dbt[5]['line'] . "<br />" : '';
        $functions .= isset($dbt[6]['file']) ? $dbt[6]['file'] . " - " . $dbt[6]['line'] . "<br />" : '';
        return $functions;
    }

    public function getAll($start, $limit, $searchstring = "")
    {
        $this->database->query(
            "SELECT ad.user, l.type, l.title, l.message, l.function, l.query, l.host, l.created, l.aid
            FROM `:prefix_log` AS l LEFT JOIN `:prefix_admins` AS ad ON l.aid = ad.aid :search
            ORDER BY l.created DESC LIMIT :start, :lim"
        );
        $this->database->bind(':start', $start, \PDO::PARAM_INT);
        $this->database->bind(':lim', $limit, \PDO::PARAM_INT);
        $this->database->bind(':search', $searchstring);

        $smLogs = $this->database->resultset();
        return $smLogs;
    }

    public function logCount($searchstring = "")
    {
        $this->database->query("SELECT count(l.lid) AS count FROM `:prefix_log` AS :search");
        $this->database->bind(':search', "l".$searchstring);
        $smLogs = $this->database->single();
        return $smLogs['count'];
    }

    public function countLogList()
    {
        return count($this->logList);
    }
}
