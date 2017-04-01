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

class CUpdater
{
    private $store = 0;
    private $database = null;
    private $currentVersion = 0;

    public function __construct(Database $database, $version = 0)
    {
        $this->database = $database;
        $this->currentVersion = $version;

        if (!is_numeric($this->getCurrentRevision())) {
            $this->updateVersionNumber(0); // Set at 0 initially, this will cause all database updates to be run
        } elseif ($this->getCurrentRevision() == -1) { // They have some fubar version fix it for them :|
            $this->database->query("INSERT INTO `:prefix_settings` (setting, value) VALUES ('config.version', '0')");
            $this->database->execute();
        }
    }

    public function getLatestPackageVersion()
    {
        $values = array_keys($this->getStore());
        return max($values);
    }

    public function doUpdates()
    {
        if ($this->getCurrentRevision >= $this->getLatestPackageVersion()) {
            return "Nothing to update...";
        }

        $log = "";
        foreach ($this->getStore() as $version => $file) {
            if ($version > $this->getCurrentRevision()) {
                $log .= "Running Update: <b>v".$version."</b>... ";
                if (!include(ROOT."updater/data/".$file)) {
                    $log .= "<b>Error executing: /updater/data/".$file.". Stopping Update!</b>";
                    $log .= "<br />Update Failed.";
                    return $log;
                }
                $log .= "Done.<br /><br />";
                $this->updateVersionNumber($version);
            }

            if ($version == $this->getLatestPackageVersion()) {
                $log .= "<br />Updated Sucessfully. Please delete the /updater folder.";
                return $log;
            }
        }
    }

    public function getCurrentRevision()
    {
        return ($this->currentVersion > 0) ? $this->currentVersion : -1;
    }

    public function needsUpdate()
    {
        return($this->getLatestPackageVersion() > $this->getCurrentRevision());
    }

    private function getStore()
    {
        if ($this->store == 0) {
            return include ROOT . "/updater/store.php";
        }
        return $this->store;
    }

    private function updateVersionNumber($rev)
    {
        $this->database->query("UPDATE `:prefix_settings` SET value = :value WHERE setting = 'config.version'");
        $this->database->bind(':value', $rev, \PDO::PARAM_INT);
        return $this->database->execute();
    }
}
