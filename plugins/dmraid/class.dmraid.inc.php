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
 * mdstat Plugin, which displays a snapshot of the kernel's RAID/md state
 * a simple view which shows supported types and RAID-Devices which are determined by
 * parsing the "/proc/mdstat" file, another way is to provide
 * a file with the output of the /proc/mdstat file, so there is no need to run a execute by the
 * webserver, the format of the command is written down in the mdstat.config.php file, where also
 * the method of getting the information is configured
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
//            $this->_filecontent = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            $this->_filecontent = preg_split("/(\n\*\*\* )|(\n\-\-\> )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
//            $this->_filecontent = preg_split("/\n\*\*\* /", $buffer, -1, PREG_SPLIT_NO_EMPTY);
//            $this->_filecontent = preg_split("/\n--> /", $buffer, -1, PREG_SPLIT_NO_EMPTY);
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
                $lines = preg_split("/\n/", $block, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    if (preg_match('/^NOTICE: added\s+\/dev\/(.+)\s+to RAID set\s+\"(.+)\"/', $line, $partition)) {
                        $this->_result['devices'][$partition[2]]['partitions'][$partition[1]]['status'] = "";

//                        $this->_result['devices'][$partition[2]]['action']['name'] = -1;
//                        $this->_result['devices'][$partition[2]]['action']['percent'] = -1;
//                        $this->_result['devices'][$partition[2]]['action']['finish_time'] = -1;
//                        $this->_result['devices'][$partition[2]]['action']['finish_unit'] = -1;
//                echo "jest";
                    } elseif (preg_match('/^ERROR: .* device\s+\/dev\/(.+)\s+(.+)\s+in RAID set\s+\"(.+)\"/', $line, $partition)) {
                        if ($partition[2]=="broken") {
                            $this->_result['devices'][$partition[3]]['partitions'][$partition[1]]['status'] = 'F';
                        }
//                        $this->_result['devices'][$partition[3]]['action']['name'] = -1;
//                        $this->_result['devices'][$partition[3]]['action']['percent'] = -1;
//                        $this->_result['devices'][$partition[3]]['action']['finish_time'] = -1;
//                        $this->_result['devices'][$partition[3]]['action']['finish_unit'] = -1;

                    }
                }
            } else {
                if (preg_match('/^Group superset\s+(.+)/m', $block, $arrname)) {
                    $group = $arrname[1];
                }
                if (preg_match('/^name\s*:\s*(.*)/m', $block, $arrname)) {
                    if ($group=="") {
                        $group = $arrname[1];
                    }
                    if (preg_match('/^type\s*:\s*(.*)/m', $block, $level)) {
                        $this->_result['devices'][$group]['level'] = $level[1];
                    }
                    if (preg_match('/^devs\s*:\s*(.*)/m', $block, $devs)) {
                        $this->_result['devices'][$group]['registered'] = $devs[1];
                    }
                    if (preg_match('/^spares\s*:\s*(.*)/m', $block, $spares)
                       && isset($this->_result['devices'][$group]['registered'])) {
                            $this->_result['devices'][$group]['active']=$this->_result['devices'][$group]['registered']-$spares[1];
                    }
                    if (preg_match('/^status\s*:\s*(.*)/m', $block, $status)) {
                        $this->_result['devices'][$group]['status'] = $status[1];
                    }
                    $group = "";
                }
            }
        }
//        $this->_result['unused_devs'] = -1;
//var_dump($this->_result);
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
/*        $sup = $this->xml->addChild("Supported_Types");
        foreach ($this->_result['supported_types'] as $type) {
            $typ = $sup->addChild("Type");
            $typ->addAttribute("Name", $type);
        }*/
        foreach ($this->_result['devices'] as $key=>$device) {
            $dev = $this->xml->addChild("Raid");
            $dev->addAttribute("Device_Name", $key);
            $dev->addAttribute("Level", $device["level"]);
            $dev->addAttribute("Disk_Status", $device["status"]);
//            $dev->addAttribute("Chunk_Size", $device["chunk_size"]);
//            $dev->addAttribute("Persistend_Superblock", $device["pers_superblock"]);
//            $dev->addAttribute("Algorithm", $device["algorithm"]);
            $dev->addAttribute("Disks_Registered", $device["registered"]);
            $dev->addAttribute("Disks_Active", $device["active"]);
//            $action = $dev->addChild("Action");
//            $action->addAttribute("Percent", $device['action']['percent']);
//            $action->addAttribute("Name", $device['action']['name']);
//            $action->addAttribute("Time_To_Finish", $device['action']['finish_time']);
//            $action->addAttribute("Time_Unit", $device['action']['finish_unit']);
            $disks = $dev->addChild("Disks");
            if (isset($device['partitions']) && sizeof($device['partitions']>0)) foreach ($device['partitions'] as $diskkey=>$disk) {
                $disktemp = $disks->addChild("Disk");
                $disktemp->addAttribute("Name", $diskkey);
                $disktemp->addAttribute("Status", $disk['status']);
              //  $disktemp->addAttribute("Index", $disk['raid_index']);
            }
        }
//        if ($this->_result['unused_devs'] !== - 1) {
//            $unDev = $this->xml->addChild("Unused_Devices");
//            $unDev->addAttribute("Devices", $this->_result['unused_devs']);
//        }

        return $this->xml->getSimpleXmlElement();
    }
}
