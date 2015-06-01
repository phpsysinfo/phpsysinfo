<?php
/**
 * MDStatus Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_MDStatus
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.mdstatus.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * mdstatus Plugin, which displays a snapshot of the kernel's RAID/md state
 * a simple view which shows supported types and RAID-Devices which are determined by
 * parsing the "/proc/mdstat" file, another way is to provide
 * a file with the output of the /proc/mdstat file, so there is no need to run a execute by the
 * webserver, the format of the command is written down in the phpsysinfo.ini file, where also
 * the method of getting the information is configured
 *
 * @category  PHP
 * @package   PSI_Plugin_MDStatus
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class MDStatus extends PSI_Plugin
{
    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = "";

    /**
     * variable, which holds the result before the xml is generated out of this array
     * @var array
     */
    private $_result = array();

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        $buffer = "";
        parent::__construct(__CLASS__, $enc);
        switch (strtolower(PSI_PLUGIN_MDSTATUS_ACCESS)) {
        case 'file':
            CommonFunctions::rfts("/proc/mdstat", $buffer);
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/mdstat.txt", $buffer);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_MDSTATUS_ACCESS");
            break;
        }
        if (trim($buffer) != "") {
            $this->_filecontent = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $this->_filecontent = array();
        }
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array<br>the array is build like a tree,
     * so that it is possible to get only a specific process with the childs
     *
     * @return void
     */
    public function execute()
    {
        if (empty($this->_filecontent)) {
            return;
        }
        // get the supported types
        if (preg_match('/[a-zA-Z]* : (\[([a-z0-9])*\]([ \n]))+/', $this->_filecontent[0], $res)) {
            $parts = preg_split("/ : /", $res[0]);
            $parts = preg_split("/ /", $parts[1]);
            $count = 0;
            foreach ($parts as $types) {
                if (trim($types) != "") {
                    $this->_result['supported_types'][$count++] = substr(trim($types), 1, -1);
                }
            }
        }
        // get disks
        if (preg_match("/^read_ahead/", $this->_filecontent[1])) {
            $count = 2;
        } else {
            $count = 1;
        }
        $cnt_filecontent = count($this->_filecontent);
        do {
            $parts = preg_split("/ : /", $this->_filecontent[$count]);
            $dev = trim($parts[0]);
            if (count($parts) == 2) {
                $details = preg_split('/ /', $parts[1]);
                if (!strstr($details[0], 'inactive')) {
                    $this->_result['devices'][$dev]['level'] = $details[1];
                } else {
                    $this->_result['devices'][$dev]['level'] = "none";
                }
                $this->_result['devices'][$dev]['status'] = $details[0];
                for ($i = 2, $cnt_details = count($details); $i < $cnt_details; $i++) {
                    preg_match('/(([a-z0-9])+)(\[([0-9]+)\])(\([SF ]\))?/', trim($details[$i]), $partition);
                    if (count($partition) == 5 || count($partition) == 6) {
                        $this->_result['devices'][$dev]['partitions'][$partition[1]]['raid_index'] = substr(trim($partition[3]), 1, -1);
                        if (isset($partition[5])) {
                            $search = array("(", ")");
                            $replace = array("", "");
                            $this->_result['devices'][$dev]['partitions'][$partition[1]]['status'] = str_replace($search, $replace, trim($partition[5]));
                        } else {
                            $this->_result['devices'][$dev]['partitions'][$partition[1]]['status'] = " ";
                        }
                    }
                }
                $count++;
                $optionline = $this->_filecontent[$count - 1].$this->_filecontent[$count];
                if (preg_match('/([^\sk]*)k chunk/', $optionline, $chunksize)) {
                    $this->_result['devices'][$dev]['chunk_size'] = $chunksize[1];
                } else {
                    $this->_result['devices'][$dev]['chunk_size'] = -1;
                }
                if ($pos = strpos($optionline, "super non-persistent")) {
                    $this->_result['devices'][$dev]['pers_superblock'] = 0;
                } else {
                    $this->_result['devices'][$dev]['pers_superblock'] = 1;
                }
                if ($pos = strpos($optionline, "algorithm")) {
                    $this->_result['devices'][$dev]['algorithm'] = trim(substr($optionline, $pos + 9, 2));
                } else {
                    $this->_result['devices'][$dev]['algorithm'] = -1;
                }
                if (preg_match('/(\[[0-9]?\/[0-9]\])/', $optionline, $res)) {
                    $slashpos = strpos($res[0], '/');
                    $this->_result['devices'][$dev]['registered'] = substr($res[0], 1, $slashpos - 1);
                    $this->_result['devices'][$dev]['active'] = substr($res[0], $slashpos + 1, strlen($res[0]) - $slashpos - 2);
                } else {
                    $this->_result['devices'][$dev]['registered'] = -1;
                    $this->_result['devices'][$dev]['active'] = -1;
                }
                if (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)%/'), $this->_filecontent[$count + 1], $res) || (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)/'), $optionline, $res))) {
                    list($this->_result['devices'][$dev]['action']['name'], $this->_result['devices'][$dev]['action']['percent']) = preg_split("/=/", str_replace("%", "", $res[0]));
                    if (preg_match(('/([a-z]*=[0-9\.]+[a-z]+)/'), $this->_filecontent[$count + 1], $res)) {
                        $time = preg_split("/=/", $res[0]);
                        list($this->_result['devices'][$dev]['action']['finish_time'], $this->_result['devices'][$dev]['action']['finish_unit']) = sscanf($time[1], '%f%s');
                    } else {
                        $this->_result['devices'][$dev]['action']['finish_time'] = -1;
                        $this->_result['devices'][$dev]['action']['finish_unit'] = -1;
                    }
                } else {
                    $this->_result['devices'][$dev]['action']['name'] = -1;
                    $this->_result['devices'][$dev]['action']['percent'] = -1;
                    $this->_result['devices'][$dev]['action']['finish_time'] = -1;
                    $this->_result['devices'][$dev]['action']['finish_unit'] = -1;
                }
            } else {
                $count++;
            }
        } while ($cnt_filecontent > $count);
        $lastline = $this->_filecontent[$cnt_filecontent - 2];
        if (strpos($lastline, "unused devices") !== false) {
            $parts = preg_split("/:/", $lastline);
            $search = array("<", ">");
            $replace = array("", "");
            $this->_result['unused_devs'] = trim(str_replace($search, $replace, $parts[1]));
        } else {
            $this->_result['unused_devs'] = -1;
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLObject entire XML content for the plugin
     */
    public function xml()
    {
        if (empty($this->_result)) {
            return $this->xml->getSimpleXmlElement();
        }
        $hideRaids = array();
        if (defined('PSI_PLUGIN_MDSTATUS_HIDE_RAID_DEVICES') && is_string(PSI_PLUGIN_MDSTATUS_HIDE_RAID_DEVICES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_MDSTATUS_HIDE_RAID_DEVICES)) {
                $hideRaids = eval(PSI_PLUGIN_MDSTATUS_HIDE_RAID_DEVICES);
            } else {
                $hideRaids = array(PSI_PLUGIN_MDSTATUS_HIDE_RAID_DEVICES);
            }
        }
        $sup = $this->xml->addChild("Supported_Types");
        foreach ($this->_result['supported_types'] as $type) {
            $typ = $sup->addChild("Type");
            $typ->addAttribute("Name", $type);
        }
        if (isset($this->_result['devices'])) foreach ($this->_result['devices'] as $key=>$device) {
            if (!in_array($key, $hideRaids, true)) {
                $dev = $this->xml->addChild("Raid");
                $dev->addAttribute("Device_Name", $key);
                $dev->addAttribute("Level", $device["level"]);
                $dev->addAttribute("Disk_Status", $device["status"]);
                $dev->addAttribute("Chunk_Size", $device["chunk_size"]);
                $dev->addAttribute("Persistend_Superblock", $device["pers_superblock"]);
                $dev->addAttribute("Algorithm", $device["algorithm"]);
                $dev->addAttribute("Disks_Registered", $device["registered"]);
                $dev->addAttribute("Disks_Active", $device["active"]);
                $action = $dev->addChild("Action");
                $action->addAttribute("Percent", $device['action']['percent']);
                $action->addAttribute("Name", $device['action']['name']);
                $action->addAttribute("Time_To_Finish", $device['action']['finish_time']);
                $action->addAttribute("Time_Unit", $device['action']['finish_unit']);
                $disks = $dev->addChild("Disks");
                foreach ($device['partitions'] as $diskkey=>$disk) {
                    $disktemp = $disks->addChild("Disk");
                    $disktemp->addAttribute("Name", $diskkey);
                    $disktemp->addAttribute("Status", $disk['status']);
                    $disktemp->addAttribute("Index", $disk['raid_index']);
                }
            }
        }
        if ($this->_result['unused_devs'] !== - 1) {
            $unDev = $this->xml->addChild("Unused_Devices");
            $unDev->addAttribute("Devices", $this->_result['unused_devs']);
        }

        return $this->xml->getSimpleXmlElement();
    }
}
