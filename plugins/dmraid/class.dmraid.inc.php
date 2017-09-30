<?php
/**
 * DMRaid Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_DMRaid
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.dmraid.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * dmraid Plugin, which displays software RAID status
 *
 * @category  PHP
 * @package   PSI_Plugin_DMRaid
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class DMRaid extends PSI_Plugin
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
        switch (strtolower(PSI_PLUGIN_DMRAID_ACCESS)) {
        case 'command':
            if (PSI_OS == 'FreeBSD') {
                CommonFunctions::executeProgram("graid", "list", $buffer);
            } else {
                CommonFunctions::executeProgram("dmraid", "-s -vv 2>&1", $buffer);
            }
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/dmraid.txt", $buffer);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_DMRAID_ACCESS");
            break;
        }
        if (trim($buffer) != "") {
            if (PSI_OS == 'FreeBSD') {
                $this->_filecontent = preg_split("/Consumers:\r?\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $this->_filecontent = preg_split("/(\r?\n\*\*\* )|(\r?\n--> )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            }
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
        if (PSI_OS == 'FreeBSD') {
            $disksinfo = array();
            if (isset($this->_filecontent[1]) && (trim($this->_filecontent[1])!=="")) {
                $lines = preg_split("/\r?\n/", trim($this->_filecontent[1]), -1, PREG_SPLIT_NO_EMPTY);
                $disk = "";
                foreach ($lines as $line) {
                    if (preg_match("/^\d+\.\s+Name:\s+(.+)/", $line, $data)) {
                        $disk = $data[1];
                    } elseif (($disk!=="") && preg_match('/^\s+State:\s+(\S+)\s+\(([^\)\s]+)\s*([\d]*)(%*)([^\)]*)\)/', $line, $data)) {
                        $disksinfo[$disk]['status'] = trim($data[1]);
                        $disksinfo[$disk]['substatus'] = trim($data[2]);
                        if (trim($data[4])=="%") {
                            $disksinfo[$disk]['percent'] = trim($data[3]);
                        }
                    }
                }
            }
            $lines = preg_split("/\r?\n/", trim($this->_filecontent[0]), -1, PREG_SPLIT_NO_EMPTY);
            $group = "";
            foreach ($lines as $line) {
                if (preg_match("/^\d+\.\s+Name:\s+(.+)/", $line, $data)) {
                    $group = $data[1];
                } elseif ($group!=="") {
                    if (preg_match('/^\s+Mediasize:\s+(\d+)/', $line, $data)) {
                        $this->_result['devices'][$group]['size'] = trim($data[1]);
                    } elseif (preg_match('/^\s+State:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['status'] = trim($data[1]);
                    } elseif (preg_match('/^\s+RAIDLevel:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['type'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Components:\s+(\d+)/', $line, $data)) {
                        $this->_result['devices'][$group]['devs'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Label:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['name'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Subdisks:\s+(.+)/', $line, $data)) {
                        $disks = preg_split('/\s*,\s*/', trim($data[1]), -1, PREG_SPLIT_NO_EMPTY);
                        $nones = 0;
                        foreach ($disks as $disk) {
                            if (preg_match("/^(\S+)\s+\(([^\)]+)\)/", $disk, $partition)) {
                                if ($partition[2]=="ACTIVE") {
                                    if (isset($disksinfo[$partition[1]]["status"])) {
                                        if ($disksinfo[$partition[1]]["status"]!=="ACTIVE") {
                                            $this->_result['devices'][$group]['partitions'][$partition[1]]['status'] = 'W';
                                        } elseif ($disksinfo[$partition[1]]["substatus"]=="ACTIVE") {
                                            $this->_result['devices'][$group]['partitions'][$partition[1]]['status'] = 'ok';
                                        } else {
                                            $this->_result['devices'][$group]['partitions'][$partition[1]]['status'] = 'W';
                                            if (isset($disksinfo[$partition[1]]["percent"])) {
                                                $this->_result['devices'][$group]['action']['name'] = $disksinfo[$partition[1]]["substatus"];
                                                $this->_result['devices'][$group]['action']['percent'] = $disksinfo[$partition[1]]["percent"];
                                            }
                                        }
                                    } else {
                                        $this->_result['devices'][$group]['partitions'][$partition[1]]['status'] = 'ok';
                                    }
                                } elseif ($partition[2]=="NONE") {
                                    $this->_result['devices'][$group]['partitions']["none".$nones]['status'] = 'E';
                                    $nones++;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $group = "";
            foreach ($this->_filecontent as $block) {
                if (preg_match('/^(NOTICE: )|(ERROR: )/m', $block)) {
                    $group = "";
                    $lines = preg_split("/\r?\n/", $block, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) {
                        if (preg_match('/^NOTICE: added\s+\/dev\/(.+)\s+to RAID set\s+\"(.+)\"/', $line, $partition)) {
                            $this->_result['devices'][$partition[2]]['partitions'][$partition[1]]['status'] = "ok";
                        } elseif (preg_match('/^ERROR: .* device\s+\/dev\/(.+)\s+(.+)\s+in RAID set\s+\"(.+)\"/', $line, $partition)) {
                            if ($partition[2]=="broken") {
                                $this->_result['devices'][$partition[3]]['partitions'][$partition[1]]['status'] = 'F';
                            } else {
                                $this->_result['devices'][$partition[3]]['partitions'][$partition[1]]['status'] = 'W';
                            }
                        }
                    }
                } else {
                    if (preg_match('/^Group superset\s+(.+)/m', $block, $arrname)) {
                        $group = trim($arrname[1]);
                    }
                    if (preg_match('/^name\s*:\s*(.*)/m', $block, $arrname)) {
                        if ($group=="") {
                            $group = trim($arrname[1]);
                        }
                        $this->_result['devices'][$group]['name'] = $arrname[1];
                        if (preg_match('/^size\s*:\s*(.*)/m', $block, $size)) {
                            $this->_result['devices'][$group]['size'] = trim($size[1]);
                        }
                        if (preg_match('/^stride\s*:\s*(.*)/m', $block, $stride)) {
                                $this->_result['devices'][$group]['stride'] = trim($stride[1]);
                        }
                        if (preg_match('/^type\s*:\s*(.*)/m', $block, $type)) {
                            $this->_result['devices'][$group]['type'] = trim($type[1]);
                        }
                        if (preg_match('/^status\s*:\s*(.*)/m', $block, $status)) {
                            $this->_result['devices'][$group]['status'] = trim($status[1]);
                        }
                        if (preg_match('/^subsets\s*:\s*(.*)/m', $block, $subsets)) {
                            $this->_result['devices'][$group]['subsets'] = trim($subsets[1]);
                        }
                        if (preg_match('/^devs\s*:\s*(.*)/m', $block, $devs)) {
                            $this->_result['devices'][$group]['devs'] = trim($devs[1]);
                        }
                        if (preg_match('/^spares\s*:\s*(.*)/m', $block, $spares)) {
                                $this->_result['devices'][$group]['spares'] = trim($spares[1]);
                        }
                        $group = "";
                    }
                }
            }
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        if (empty($this->_result)) {
            return $this->xml->getSimpleXmlElement();
        }
        $hideRaids = array();
        if (defined('PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES') && is_string(PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES)) {
                $hideRaids = eval(PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES);
            } else {
                $hideRaids = array(PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES);
            }
        }
        foreach ($this->_result['devices'] as $key=>$device) {
            if (!in_array($key, $hideRaids, true)) {
                $dev = $this->xml->addChild("Raid");
                $dev->addAttribute("Device_Name", $key);
                $dev->addAttribute("Type", $device["type"]);
                $dev->addAttribute("Disk_Status", $device["status"]);
                $dev->addAttribute("Name", $device["name"]);
                $dev->addAttribute("Size", $device["size"]);
                if (isset($device['stride'])) $dev->addAttribute("Stride", $device["stride"]);
                if (isset($device['subsets'])) $dev->addAttribute("Subsets", $device["subsets"]);
                $dev->addAttribute("Devs", $device["devs"]);
                if (isset($device['spares'])) $dev->addAttribute("Spares", $device["spares"]);
                if (isset($device['action'])) {
                    $action = $dev->addChild("Action");
                    $action->addAttribute("Percent", $device['action']['percent']);
                    $action->addAttribute("Name", $device['action']['name']);
                }
                $disks = $dev->addChild("Disks");
                if (isset($device['partitions']) && sizeof($device['partitions']>0)) foreach ($device['partitions'] as $diskkey=>$disk) {
                    $disktemp = $disks->addChild("Disk");
                    $disktemp->addAttribute("Name", $diskkey);
                    if (($device["status"]=='ok') || ($device["status"]=='OPTIMAL')) {
                        $disktemp->addAttribute("Status", $disk['status']);
                    } else {
                        $disktemp->addAttribute("Status", 'W');
                    }
                }
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
