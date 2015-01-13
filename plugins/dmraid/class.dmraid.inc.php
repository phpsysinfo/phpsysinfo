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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
            CommonFunctions::executeProgram("dmraid", "-s -vv 2>&1", $buffer);
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/dmraid.txt", $buffer);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_DMRAID_ACCESS");
            break;
        }
        if (trim($buffer) != "") {
            $this->_filecontent = preg_split("/(\r?\n\*\*\* )|(\r?\n--> )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
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
        if ( empty($this->_filecontent)) {
            return;
        }
        $group = "";
        foreach ($this->_filecontent as $block) {
            if (preg_match('/^(NOTICE: )|(ERROR: )/m', $block)) {
                $group = "";
                $lines = preg_split("/\r?\n/", $block, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    if (preg_match('/^NOTICE: added\s+\/dev\/(.+)\s+to RAID set\s+\"(.+)\"/', $line, $partition)) {
                        $this->_result['devices'][$partition[2]]['partitions'][$partition[1]]['status'] = "";
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

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLObject entire XML content for the plugin
     */
    public function xml()
    {
        if ( empty($this->_result)) {
            return $this->xml->getSimpleXmlElement();
        }
        $hideRaids = array();
        if ( defined('PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES') && is_string(PSI_PLUGIN_DMRAID_HIDE_RAID_DEVICES) ) {
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
                $dev->addAttribute("Stride", $device["stride"]);
                $dev->addAttribute("Subsets", $device["subsets"]);
                $dev->addAttribute("Devs", $device["devs"]);
                $dev->addAttribute("Spares", $device["spares"]);
                $disks = $dev->addChild("Disks");
                if (isset($device['partitions']) && sizeof($device['partitions']>0)) foreach ($device['partitions'] as $diskkey=>$disk) {
                    $disktemp = $disks->addChild("Disk");
                    $disktemp->addAttribute("Name", $diskkey);
                    if ($device["status"]=='ok') {
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
