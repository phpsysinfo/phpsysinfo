<?php
/**
 * Raid Plugin, which displays RAID status
 *
 * @category  PHP
 * @package   PSI_Plugin_Raid
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Raid extends PSI_Plugin
{
    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = array();

    /**
     * variable, which holds the result before the xml is generated out of this array
     * @var array
     */
    private $_result = array();

    private $prog_items = array('mdstat','dmraid','megactl','megasasctl','graid','zpool');

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $RaidProgs = array();
        if (defined('PSI_PLUGIN_RAID_PROGRAM') && is_string(PSI_PLUGIN_RAID_PROGRAM)) {
            if (is_string(PSI_PLUGIN_RAID_PROGRAM)) {
                if (preg_match(ARRAY_EXP, PSI_PLUGIN_RAID_PROGRAM)) {
                    $RaidProgs = eval(strtolower(PSI_PLUGIN_RAID_PROGRAM));
                } else {
                    $RaidProgs = array(strtolower(PSI_PLUGIN_RAID_PROGRAM));
                }
            } else {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");
                exit;
            }
        } else {
            $RaidProgs = $this->prog_items;
        }

        $notwas = true;
        switch (strtolower(PSI_PLUGIN_RAID_ACCESS)) {
        case 'command':
            if ((PSI_OS == 'Linux') && in_array('mdstat', $RaidProgs)) {
                CommonFunctions::rfts("/proc/mdstat", $this->_filecontent['mdstat'], 0, 4096, PSI_DEBUG);
                $notwas = false;
            }
            if ((PSI_OS == 'Linux') && in_array('dmraid', $RaidProgs)) {
                CommonFunctions::executeProgram("dmraid", "-s -vv 2>&1", $this->_filecontent['dmraid'], PSI_DEBUG);
                $notwas = false;
            }
            if ((PSI_OS == 'Linux') && in_array('megactl', $RaidProgs)) {
                CommonFunctions::executeProgram("megactl", "", $this->_filecontent['megactl'], PSI_DEBUG);
                $notwas = false;
            }
            if ((PSI_OS == 'Linux') && in_array('megasasctl', $RaidProgs)) {
                CommonFunctions::executeProgram("megasasctl", "", $this->_filecontent['megasasctl'], PSI_DEBUG);
                $notwas = false;
            }
            if ((PSI_OS == 'FreeBSD') && in_array('graid', $RaidProgs)) {
                CommonFunctions::executeProgram("graid", "list", $this->_filecontent['graid'], PSI_DEBUG);
                $notwas = false;
            }
            if (in_array('zpool', $RaidProgs)) {
                CommonFunctions::executeProgram("zpool", "status", $this->_filecontent['zpool'], PSI_DEBUG);
                $notwas = false;
            }
            if ($notwas) {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");
            }
            break;
        case 'data':
            foreach ($this->prog_items as $item) {
                if (in_array($item, $RaidProgs)) {
                    CommonFunctions::rfts(APP_ROOT."/data/raid".$item.".txt", $this->_filecontent[$item], 0, 4096, false);
                    $notwas = false;
                }
            }
            if ($notwas) {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");
            }
            break;
        default:
            $this->global_error->addConfigError("__construct()", "[raid] ACCESS");
            break;
        }
    }

    private function execute_mdstat($buffer)
    {
        $raiddata = preg_split("/\r?\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) {
            // get the supported types
            $supported = '';
            if (preg_match('/^[a-zA-Z]+ :( \[[a-z0-9]+\])+/', $raiddata[0], $res)) {
                $parts = preg_split("/ : /", $res[0]);
                if (isset($parts[1]) && (trim($parts[1]) !== '')) {
                    $supported = preg_replace('/[\[\]]/', '', trim($parts[1]));
                }
            }
            // get disks
            if (preg_match("/^read_ahead/", $raiddata[1])) {
                $count = 2;
            } else {
                $count = 1;
            }
            $cnt_filecontent = count($raiddata);
            do {
                $parts = preg_split("/ : /", $raiddata[$count]);
                $dev = trim($parts[0]);
                if (count($parts) == 2) {
                    $this->_result['devices'][$dev]['prog'] = "mdstat";
                    if ($supported !== '') $this->_result['devices'][$dev]['supported'] = $supported;
                    $this->_result['devices'][$dev]['items'][0]['raid_index'] = -1; //must by first
                    $details = preg_split('/ /', $parts[1]);
                    if (!strstr($details[0], 'inactive')) {
                        if (isset($details[2]) && strstr($details[1], '(auto-read-only)')) {
                            $this->_result['devices'][$dev]['level'] = $details[2];
                            $this->_result['devices'][$dev]['status'] = $details[0]." ".$details[1];
                            //$this->_result['devices'][$dev]['items'][0]['name'] = $dev." ".$details[2];
                            $this->_result['devices'][$dev]['items'][0]['name'] = $details[2];
                            $this->_result['devices'][$dev]['items'][0]['status'] = "W";
                            $i = 3;
                        } else {
                            $this->_result['devices'][$dev]['level'] = $details[1];
                            $this->_result['devices'][$dev]['status'] = $details[0];
                            //$this->_result['devices'][$dev]['items'][0]['name'] = $dev." ".$details[1];
                            $this->_result['devices'][$dev]['items'][0]['name'] = $details[1];
                            $this->_result['devices'][$dev]['items'][0]['status'] = "ok";
                            $i = 2;
                        }
                    } else {
                        $this->_result['devices'][$dev]['level'] = "none";
                        $this->_result['devices'][$dev]['status'] = $details[0];
                        $this->_result['devices'][$dev]['items'][0]['name'] = $dev;
                        $this->_result['devices'][$dev]['items'][0]['status'] = "F";
                        $i = 1;
                    }
                    $this->_result['devices'][$dev]['items'][0]['parentid'] = 0;

                    for ($cnt_details = count($details); $i < $cnt_details; $i++) {
                        preg_match('/(([a-z0-9])+)(\[([0-9]+)\])(\([SF ]\))?/', trim($details[$i]), $partition);
                        if (count($partition) == 5 || count($partition) == 6) {
                            $this->_result['devices'][$dev]['items'][$partition[1]]['raid_index'] = 0+substr(trim($partition[3]), 1, -1);
                            if (isset($partition[5])) {
                                $search = array("(", ")");
                                $replace = array("", "");
                                $this->_result['devices'][$dev]['items'][$partition[1]]['status'] = str_replace($search, $replace, trim($partition[5]));
                            } else {
                                $this->_result['devices'][$dev]['items'][$partition[1]]['status'] = "ok";
                            }
                            $this->_result['devices'][$dev]['items'][$partition[1]]['name'] = $partition[1];
                            $this->_result['devices'][$dev]['items'][$partition[1]]['parentid'] = 1;
                            $this->_result['devices'][$dev]['items'][$partition[1]]['type'] = "disk";
                        }
                    }
                    $optionline = $raiddata[$count].$raiddata[$count+1];
                    $count++;
                    if (preg_match('/([^\sk]*)k chunk/', $optionline, $chunksize)) {
                        $this->_result['devices'][$dev]['chunk_size'] = $chunksize[1];
                    }
                    if ($pos = strpos($optionline, "super non-persistent")) {
                        $this->_result['devices'][$dev]['pers_superblock'] = 0;
                    } else {
                        $this->_result['devices'][$dev]['pers_superblock'] = 1;
                    }
                    if ($pos = strpos($optionline, "algorithm")) {
                        $this->_result['devices'][$dev]['algorithm'] = trim(substr($optionline, $pos + 9, 2));
                    }
                    if (preg_match('/\[([0-9]+)\/([0-9]+)\]/', $optionline, $res)) {
                        $this->_result['devices'][$dev]['registered'] = $res[1];
                        $this->_result['devices'][$dev]['active'] = $res[2];
                    }

                    if (isset($this->_result['devices'][$dev]['items'])) {
                        asort($this->_result['devices'][$dev]['items']);
                    }
                    if ((!isset($this->_result['devices'][$dev]['registered']) || ($this->_result['devices'][$dev]['registered']<24)) && preg_match('/\[([_U]+)\]/', $optionline, $res) && (($reslen=strlen($res[1])) > 0)) {
                        $notsparecount = 0;
                        foreach ($this->_result['devices'][$dev]['items'] as $diskkey=>$disk) {
                            if (($diskkey!==0) && ($this->_result['devices'][$dev]['items'][$diskkey]['status']!=="S")) {
                                $notsparecount++;
                            }
                        }
                        if ($notsparecount == $reslen) {
                            $partnr = 0;
                            foreach ($this->_result['devices'][$dev]['items'] as $diskkey=>$disk) {
                                if (($diskkey!==0) && ($this->_result['devices'][$dev]['items'][$diskkey]['status']!=="S")) {
                                    if (($res[1][$partnr]=='_') && ($this->_result['devices'][$dev]['items'][$diskkey]['status']=="ok")) {
                                        $this->_result['devices'][$dev]['items'][$diskkey]['status']="W";
                                    }
                                    $partnr++;
                                }
                            }
                        } elseif ($reslen-$notsparecount == 1) {
                            $partnr = 0;
                            foreach ($this->_result['devices'][$dev]['items'] as $diskkey=>$disk) {
                                if (($diskkey!==0) && ($this->_result['devices'][$dev]['items'][$diskkey]['status']!=="S")) {
                                    if ($res[1][$partnr]=='_') {
                                        $this->_result['devices'][$dev]['items']['none']['raid_index']=$this->_result['devices'][$dev]['items'][$diskkey]['raid_index']-1;
                                        $this->_result['devices'][$dev]['items']['none']['status']="E";
                                        $this->_result['devices'][$dev]['items']['none']['name']="none";
                                        $this->_result['devices'][$dev]['items']['none']['parentid'] = 1;
                                        $this->_result['devices'][$dev]['items']['none']['type'] = "disk";
                                    }
                                    $partnr++;
                                }
                            }
                            if ($res[1][$partnr]=='_') {
                                $this->_result['devices'][$dev]['items']['none']['raid_index']=$this->_result['devices'][$dev]['items'][$diskkey]['raid_index']+1;
                                $this->_result['devices'][$dev]['items']['none']['status']="E";
                                $this->_result['devices'][$dev]['items']['none']['name']="none";
                                $this->_result['devices'][$dev]['items']['none']['parentid'] = 1;
                                $this->_result['devices'][$dev]['items']['none']['type']="disk";
                            }
                            asort($this->_result['devices'][$dev]['items']);
                            foreach ($this->_result['devices'][$dev]['items'] as $diskkey=>$disk) {
                                if ($diskkey=="none") {
                                    $this->_result['devices'][$dev]['items'][$diskkey]['raid_index']="unknown";
                                }
                            }
                        } else {
                            foreach ($this->_result['devices'][$dev]['items'] as $diskkey=>$disk) {
                                if ($this->_result['devices'][$dev]['items'][$diskkey]['status']=="ok") {
                                    $this->_result['devices'][$dev]['items'][$diskkey]['status']="W";
                                }
                            }
                            for ($partnr=0; $partnr<$reslen-$notsparecount; $partnr++) {
                                    $this->_result['devices'][$dev]['items']['none'.$partnr]['raid_index']="unknown";
                                    $this->_result['devices'][$dev]['items']['none'.$partnr]['status']="E";
                                    $this->_result['devices'][$dev]['items']['none'.$partnr]['name'] = "none".$partnr;
                                    $this->_result['devices'][$dev]['items']['none'.$partnr]['parentid'] = 1;
                                    $this->_result['devices'][$dev]['items']['none'.$partnr]['type'] = "disk";
                            }
                        }
                    }
                    if (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)%/'), $raiddata[$count + 1], $res) || (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)/'), $optionline, $res))) {
                        list($this->_result['devices'][$dev]['action']['name'], $this->_result['devices'][$dev]['action']['percent']) = preg_split("/=/", str_replace("%", "", $res[0]));
                        if (preg_match(('/([a-z]*=[0-9\.]+[a-z]+)/'), $raiddata[$count + 1], $res)) {
                            $time = preg_split("/=/", $res[0]);
                            list($this->_result['devices'][$dev]['action']['finish_time'], $this->_result['devices'][$dev]['action']['finish_unit']) = sscanf($time[1], '%f%s');
                        }
                    } elseif (preg_match(('/^( *)([a-z]+)( *)=( *)([A-Z]+)$/'), $raiddata[$count + 1], $res)) {
                       $this->_result['devices'][$dev]['status'] .= " ".trim($raiddata[$count + 1]);
                    }
                } else {
                    $count++;
                }
            } while ($cnt_filecontent > $count);
            $lastline = $raiddata[$cnt_filecontent - 1];
            if (strpos($lastline, "unused devices") !== false) {
                $parts = preg_split("/:/", $lastline);
                $unused = trim($parts[1]);
                if ($unused !== "<none>") {
                    $details = preg_split('/ /', $parts[1], -1, PREG_SPLIT_NO_EMPTY);
                    $this->_result['devices']['spare']['prog'] = "mdstat";
                    $this->_result['devices']['spare']['status'] = "spare";
                    $this->_result['devices']['spare']['items'][0]['name'] = "spare";
                    $this->_result['devices']['spare']['items'][0]['parentid'] = 0;
                    $this->_result['devices']['spare']['items'][0]['status'] = "S";
                    foreach ($details as $id=>$disk) {
                        $this->_result['devices']['spare']['items'][$id+1]['name'] = $disk;
                        $this->_result['devices']['spare']['items'][$id+1]['parentid'] = 1;
                        $this->_result['devices']['spare']['items'][$id+1]['status'] = "S";
                        $this->_result['devices']['spare']['items'][$id+1]['type'] = "disk";
                    }
                }
            }
        }
    }

    private function execute_dmraid($buffer)
    {
        $raiddata = preg_split("/(\r?\n\*\*\* )|(\r?\n--> )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) {
            $group = "";
            foreach ($raiddata as $block) {
                if (preg_match('/^(NOTICE: )|(ERROR: )/m', $block)) {
                    $group = "";
                    $lines = preg_split("/\r?\n/", $block, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) {
                        if (preg_match('/^NOTICE: added\s+\/dev\/(.+)\s+to RAID set\s+\"(.+)\"/', $line, $partition)) {
                            if (!isset($this->_result['devices'][$partition[2]]['items'][0]['parentid'])) {
                                $this->_result['devices'][$partition[2]]['items'][0]['parentid'] = 0;
                                $this->_result['devices'][$partition[2]]['items'][0]['name'] = $partition[2];
                            }
                            $this->_result['devices'][$partition[2]]['items'][$partition[1]]['status'] = "ok";
                            $this->_result['devices'][$partition[2]]['items'][$partition[1]]['type'] = "disk";
                            $this->_result['devices'][$partition[2]]['items'][$partition[1]]['parentid'] = 1;
                            $this->_result['devices'][$partition[2]]['items'][$partition[1]]['name'] = $partition[1];
                            $this->_result['devices'][$partition[2]]['prog'] = "dmraid";
                            $this->_result['devices'][$partition[2]]['status'] = "ok";
                            $this->_result['devices'][$partition[2]]['level'] = "unknown";
                        } elseif (preg_match('/^ERROR: .* device\s+\/dev\/(.+)\s+(.+)\s+in RAID set\s+\"(.+)\"/', $line, $partition)) {
                            if (!isset($this->_result['devices'][$partition[3]]['items'][0]['parentid'])) {
                                $this->_result['devices'][$partition[3]]['items'][0]['parentid'] = 0;
                                $this->_result['devices'][$partition[3]]['items'][0]['name'] = $partition[3];
                            }
                            $this->_result['devices'][$partition[3]]['prog'] = "dmraid";
                            $this->_result['devices'][$partition[3]]['level'] = "unknown";
                            $this->_result['devices'][$partition[3]]['items'][$partition[1]]['type'] = "disk";
                            $this->_result['devices'][$partition[3]]['items'][$partition[1]]['parentid'] = 1;
                            if ($partition[2]=="broken") {
                                $this->_result['devices'][$partition[3]]['items'][$partition[1]]['status'] = 'F';
                                $this->_result['devices'][$partition[3]]['status'] = "F";
                            } else {
                                $this->_result['devices'][$partition[3]]['items'][$partition[1]]['status'] = 'W';
                                $this->_result['devices'][$partition[3]]['status'] = "W";
                            }
                            $this->_result['devices'][$partition[3]]['items'][$partition[1]]['name'] = $partition[1];
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
                        $this->_result['devices'][$group]['prog'] = "dmraid";
                        $this->_result['devices'][$group]['name'] = trim($arrname[1]);

                        $this->_result['devices'][$group]['items'][0]['name'] = trim($arrname[1]);

                        if (preg_match('/^size\s*:\s*(.*)/m', $block, $size)) {
                            $this->_result['devices'][$group]['size'] = trim($size[1]);
                        }
                        if (preg_match('/^stride\s*:\s*(.*)/m', $block, $stride)) {
                                $this->_result['devices'][$group]['stride'] = trim($stride[1]);
                        }
                        if (preg_match('/^type\s*:\s*(.*)/m', $block, $type)) {
                            $this->_result['devices'][$group]['level'] = trim($type[1]);
                            //$this->_result['devices'][$group]['items'][0]['name'] .= " ".trim($type[1]);
                            $this->_result['devices'][$group]['items'][0]['name'] = trim($type[1]);
                        }
                        if (preg_match('/^status\s*:\s*(.*)/m', $block, $status)) {
                            $this->_result['devices'][$group]['status'] = trim($status[1]);
                            switch (trim($status[1])) {
                                case "broken":
                                    $this->_result['devices'][$group]['items'][0]['status'] = "F";
                                    break;
                                case "inconsistent":
                                    $this->_result['devices'][$group]['items'][0]['status'] = "W";
                                    break;
                                default:
                                    $this->_result['devices'][$group]['items'][0]['status'] = trim($status[1]);
                            }
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

                        if (!isset($this->_result['devices'][$group]['items'][0]['parentid'])) {
                            $this->_result['devices'][$group]['items'][0]['parentid'] = 0;
                        }

                        $group = "";
                    }
                }
            }
            if (isset($this->_result['devices'])) {
                foreach ($this->_result['devices'] as $gid=>$group) if ($group['prog'] === "dmraid") {
                    $id = 1;
                    if (isset($group['devs']) && ($group['devs']>0) &&
                       (!isset($group['items']) || (count($group['items'])<$group['devs'])) &&
                       isset($group['subsets']) && ($group['subsets']>0)) for ($i = 0; $i < $group['subsets']; $i++) {
                        if (isset($this->_result['devices'][$gid."-".$i]['items'][0]['parentid'])) {
                            foreach ($this->_result['devices'][$gid."-".$i]['items'] as $fid=>$from) {
                                if ($fid===0) {
                                    $this->_result['devices'][$gid]['items'][$gid."-".$i]['parentid'] = 1;
                                    $this->_result['devices'][$gid]['items'][$gid."-".$i]['status'] = $from['status'];
                                    $this->_result['devices'][$gid]['items'][$gid."-".$i]['name'] = $gid."-".$i." ".$from['name'];
                                    if (isset($from['type'])) $this->_result['devices'][$gid]['items'][$gid."-".$i]['type'] = $from['type'];
                                } else {
                                    $this->_result['devices'][$gid]['items'][$from['name']]['parentid'] = 1+$id;
                                    $this->_result['devices'][$gid]['items'][$from['name']]['status'] = $from['status'];
                                    $this->_result['devices'][$gid]['items'][$from['name']]['name'] = $from['name'];
                                    if (isset($from['type'])) $this->_result['devices'][$gid]['items'][$from['name']]['type'] = $from['type'];
                                }
                            }
                            $id+=count($this->_result['devices'][$gid."-".$i]['items']);
                            unset($this->_result['devices'][$gid."-".$i]);
                        } else {
                            $this->_result['devices'][$gid]['items'][$gid."-".$i]['parentid'] = 1;
                            $this->_result['devices'][$gid]['items'][$gid."-".$i]['status'] = 'unknown';
                            $this->_result['devices'][$gid]['items'][$gid."-".$i]['name'] = $gid."-".$i;
                            $id++;
                        }
                    }
                }
                foreach ($this->_result['devices'] as $gid=>$group) if ($group['prog'] === "dmraid") {
                    if (($group['name'] !== $gid) && isset($group['items'][0]['parentid'])) {
                        $this->_result['devices'][$gid]['items'][0]['name'] = $group['name']." ".$group['items'][0]['name'];
                    }
                }
            }
        }
    }

    private function execute_megactl($buffer, $sas = false)
    {
        $raiddata = preg_split("/(\r?\n)+(?=[a-z]\d+ )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) foreach ($raiddata as $raidgroup) {
            if (preg_match("/^([a-z]\d+) /", $raidgroup, $buff)) {
                if (preg_match("/^[a-z]\d+ ([^:\r\n]+) [^:\r\n]+:/", $raidgroup, $geom) || preg_match("/^[a-z]\d+ ([^:\r\n]+)/", $raidgroup, $geom)) {
                    $controller = trim($geom[1]);
                } else {
                    $controller = '';
                }
                if (preg_match("/^[a-z]\d+ [^:\r\n]+ [^\r\n]* batt:([^:\r\n,]+)/", $raidgroup, $batt)) {
                    $battery = trim($batt[1]);
                } else {
                    $battery = '';
                }
                $group = $buff[1];
                $lines = preg_split("/\r?\n/", $raidgroup, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($lines)) {
                    if ($sas === true) {
                        $prog = "megactl";
                        $prefix = "";
                    } else {
                        $prog = "megasasctl";
                        $prefix = "/"; //for megactl and megasasctl conflicts
                    }
                    unset($lines[0]);
                    foreach ($lines as $line) {
                        $details = preg_split('/ /', preg_replace('/^hot spares +:/', 'hotspare:', $line), -1, PREG_SPLIT_NO_EMPTY);
                        if ((count($details) == 6) && ($details[2] === "RAID")) {
                            $this->_result['devices'][$prefix.$details[0]]['prog'] = $prog;
                            $unit = preg_replace("/^\d+/", "", $details[1]);
                            $value = preg_replace("/\D+$/", "", $details[1]);
                            switch ($unit) {
                                case 'B':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = $value;
                                    break;
                                case 'KiB':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = 1024*$value;
                                    break;
                                case 'MiB':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = 1024*1024*$value;
                                    break;
                                case 'GiB':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = 1024*1024*1024*$value;
                                    break;
                                case 'TiB':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = 1024*1024*1024*1024*$value;
                                    break;
                                case 'PiB':
                                    $this->_result['devices'][$prefix.$details[0]]['size'] = 1024*1024*1024*1024*1024*$value;
                                    break;
                            }
                            $this->_result['devices'][$prefix.$details[0]]['level'] = "RAID".$details[3]." ".$details[4];
                            $this->_result['devices'][$prefix.$details[0]]['status'] = $details[5];
                            if ($controller !== '') $this->_result['devices'][$prefix.$details[0]]['controller'] = $controller;
                            if ($battery !== '') $this->_result['devices'][$prefix.$details[0]]['battery'] = $battery;
                            $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['parentid'] = 0;
                            $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['name'] = "RAID".$details[3]." ".$details[4];
                            if ($details[5] !== 'optimal') {
                                $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['info'] = $details[5];
                            }
                            switch ($details[5]) {
                                case 'optimal':
                                    $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['status'] = "ok";
                                    break;
                                case 'OFFLINE':
                                    $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['status'] = "F";
                                    break;
                                default:
                                    $this->_result['devices'][$prefix.$details[0]]['items'][$details[0]]['status'] = "W";
                            }
                        } elseif (count($details) == 4) {
                            if (isset($this->_result['devices'][$prefix.$details[2]])) {
                                $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['parentid'] = 1;
                                $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['type'] = 'disk';
                                $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['name'] = $details[0];
                                if ($details[3] !== 'online') {
                                    $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['info'] = $details[3];
                                }
                                switch ($details[3]) {
                                    case 'online':
                                        $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['status'] = "ok";
                                        break;
                                    case 'hotspare':
                                        $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['status'] = "S";
                                        break;
                                    case 'rdy/fail':
                                        $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['status'] = "F";
                                        break;
                                    default:
                                        $this->_result['devices'][$prefix.$details[2]]['items'][$details[0]]['status'] = "W";
                                }
                            }
                        } elseif ((count($details) == 2) && (($details[0]==='unconfigured:') || ($details[0]==='hotspare:'))) {
                            $itemn0 = rtrim($details[0], ':');
                            $itemn = $group .'-'.$itemn0;
                            $this->_result['devices'][$prefix.$itemn]['status'] = $itemn0;
                            $this->_result['devices'][$prefix.$itemn]['prog'] = $prog;
                            if ($controller !== '') $this->_result['devices'][$prefix.$itemn]['controller'] = $controller;
                            if ($battery !== '') $this->_result['devices'][$prefix.$itemn]['battery'] = $battery;
                            $this->_result['devices'][$prefix.$itemn]['items'][$itemn]['parentid'] = 0;
                            $this->_result['devices'][$prefix.$itemn]['items'][$itemn]['name'] = $itemn0;
                            if ($details[0]==='unconfigured:') {
                                $this->_result['devices'][$prefix.$itemn]['items'][$itemn]['status'] = 'U';
                            } else {
                                $this->_result['devices'][$prefix.$itemn]['items'][$itemn]['status'] = 'S';
                            }
                        } elseif (count($details) == 3) {
                            $itemn = '';
                            switch ($details[2]) {
                                case 'BAD':
                                case 'ready':
                                    $itemn = $group .'-'.'unconfigured';
                                    break;
                                case 'hotspare':
                                    $itemn = $group .'-'.'hotspare';
                            }
                            if (($itemn !== '') && isset($this->_result['devices'][$prefix.$itemn])) {
                                $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['parentid'] = 1;
                                $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['type'] = 'disk';
                                $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['name'] = $details[0];
                                $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['info'] = $details[2];
                                switch ($details[2]) {
                                    case 'ready':
                                        $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['status'] = "U";
                                        break;
                                    case 'hotspare':
                                        $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['status'] = "S";
                                        break;
                                    default:
                                        $this->_result['devices'][$prefix.$itemn]['items'][$details[0]]['status'] = "F";
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function execute_graid($buffer)
    {
        if (preg_match('/^Geom name: +([^\n\r]+)/', $buffer, $geom)) {
            $controller = trim($geom[1]);
        } else {
            $controller = '';
        }
        $raiddata = preg_split("/Consumers:\r?\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) {
            $disksinfo = array();
            if (isset($raiddata[1]) && (trim($raiddata[1])!=="")) {
                $lines = preg_split("/\r?\n/", trim($raiddata[1]), -1, PREG_SPLIT_NO_EMPTY);
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
            $lines = preg_split("/\r?\n/", trim($raiddata[0]), -1, PREG_SPLIT_NO_EMPTY);
            $group = "";
            foreach ($lines as $line) {
                if (preg_match("/^\d+\.\s+Name:\s+(.+)/", $line, $data)) {
                    $group = $data[1];
                    $this->_result['devices'][$group]['prog'] = "graid";
                    if ($controller !== '') $this->_result['devices'][$group]['controller'] = $controller;
                } elseif ($group!=="") {
                    if (preg_match('/^\s+Mediasize:\s+(\d+)/', $line, $data)) {
                        $this->_result['devices'][$group]['size'] = trim($data[1]);
                    } elseif (preg_match('/^\s+State:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['status'] = trim($data[1]);
                    } elseif (preg_match('/^\s+RAIDLevel:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['level'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Components:\s+(\d+)/', $line, $data)) {
                        $this->_result['devices'][$group]['devs'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Label:\s+(.+)/', $line, $data)) {
                        $this->_result['devices'][$group]['name'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Subdisks:\s+(.+)/', $line, $data)) {
                        $disks = preg_split('/\s*,\s*/', trim($data[1]), -1, PREG_SPLIT_NO_EMPTY);
                        $nones = 0;
                        $this->_result['devices'][$group]['items'][0]['parentid'] = 0;
                        foreach ($disks as $disk) {
                            if (preg_match("/^(\S+)\s+\(([^\)]+)\)/", $disk, $partition)) {
                                $this->_result['devices'][$group]['items'][$partition[1]]['parentid'] = 1;
                                $this->_result['devices'][$group]['items'][$partition[1]]['type'] = "disk";
                                if ($partition[2]=="ACTIVE") {
                                    if (isset($disksinfo[$partition[1]]["status"])) {
                                        if ($disksinfo[$partition[1]]["status"]!=="ACTIVE") {
                                            $this->_result['devices'][$group]['items'][$partition[1]]['status'] = 'W';
                                        } elseif ($disksinfo[$partition[1]]["substatus"]=="ACTIVE") {
                                            $this->_result['devices'][$group]['items'][$partition[1]]['status'] = 'ok';
                                        } else {
                                            $this->_result['devices'][$group]['items'][$partition[1]]['status'] = 'W';
                                            if (isset($disksinfo[$partition[1]]["percent"])) {
                                                $this->_result['devices'][$group]['action']['name'] = $disksinfo[$partition[1]]["substatus"];
                                                $this->_result['devices'][$group]['action']['percent'] = $disksinfo[$partition[1]]["percent"];
                                            }
                                        }
                                    } else {
                                        $this->_result['devices'][$group]['items'][$partition[1]]['status'] = 'ok';
                                        $this->_result['devices'][$group]['items'][$partition[1]]['name'] = $partition[1];
                                    }
                                    $this->_result['devices'][$group]['items'][$partition[1]]['name'] = $partition[1];
                                } elseif ($partition[2]=="NONE") {
                                    $this->_result['devices'][$group]['items']["none".$nones]['status'] = 'E';
                                    $this->_result['devices'][$group]['items']["none".$nones]['name'] = "none".$nones;
                                    $nones++;
                                }
                            }
                        }
                    }
                }
            }
            if (isset($this->_result['devices'][$group]['items'][0]['parentid'])) {
                $name = "";
                if (isset($this->_result['devices'][$group]['name'])) {
                    $name = $this->_result['devices'][$group]['name'];
                }
                if (isset($this->_result['devices'][$group]['level'])) {
                    $name .= " " .$this->_result['devices'][$group]['level'];
                }
                $this->_result['devices'][$group]['items'][0]['name'] = trim($name);
                if (isset($this->_result['devices'][$group]['status'])) {
                      if ($this->_result['devices'][$group]['status']==="OPTIMAL") {
                          $this->_result['devices'][$group]['items'][0]['status'] = "ok";
                      } else {
                          $this->_result['devices'][$group]['items'][0]['status'] = "W";
                          $this->_result['devices'][$group]['items'][0]['info'] = $this->_result['devices'][$group]['status'];
                      }
                } else {
                    $this->_result['devices'][$group]['items'][0]['status'] = "ok";
                }
            }
        }
    }

    private function execute_zpool($buffer)
    {
        $raiddata = preg_split("/(\r?\n)+ +(?=pool: )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) foreach ($raiddata as $raid) {
            if (preg_match("/^pool: (\S+)/", $raid, $buff)) {
                $group = $buff[1];
                $this->_result['devices'][$group]['prog'] = "zpool";
                if (preg_match("/^ +state: (\S+)/m", $raid, $buff)) {
                    $this->_result['devices'][$group]['status'] = $buff[1];
                }
                $databegin = preg_split("/\n[ \t]+NAME +STATE +READ +WRITE +CKSUM\r?\n/", $raid, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($databegin) && (count($databegin)==2)) {
                    $datas = preg_split("/\r?\n[ \t]*\r?\n/", $databegin[1], -1, PREG_SPLIT_NO_EMPTY);
                    $datalines = preg_split("/\r?\n/", $datas[0], -1, PREG_SPLIT_NO_EMPTY);
                    $rootoffset = false;
                    $lastparentids = array(0=>-1);
                    $lastindent = 0;
                    $lastid = 0;
                    foreach ($datalines as $id=>$data) {
                        if (preg_match("/^([ \t]+)\S/", $data, $buff)) {;
                            $fullbuff = preg_split("/[ \t]+/", $data, 6, PREG_SPLIT_NO_EMPTY);
                            $offset=strlen($buff[1]);
                            if ($rootoffset === false) { // first line means root
                                $rootoffset = $offset;
                                $this->_result['devices'][$group]['items'][$id]['name'] = "";//$fullbuff[0];
                                if (count($fullbuff) > 1) {
                                    $this->_result['devices'][$group]['items'][$id]['status'] = $fullbuff[1];
                                }
                                $this->_result['devices'][$group]['items'][$id]['parentid'] = -2;
                                continue;
                            }
                            if ($offset < $rootoffset) { // some errors
                                continue;
                            }

                            $this->_result['devices'][$group]['items'][$id]['name'] = $fullbuff[0];

                            if (count($fullbuff) > 1) {
                                $this->_result['devices'][$group]['items'][$id]['status'] = $fullbuff[1];
                            }
                            if (count($fullbuff) > 5) {
                                $this->_result['devices'][$group]['items'][$id]['info'] = $fullbuff[5];
                            }

                            $indent = ($offset - $rootoffset)/2;
                            if ($indent > $lastindent) {
                                $lastparentids[$indent] = $lastid;
                            }
                            $this->_result['devices'][$group]['items'][$id]['parentid'] = $lastparentids[$indent];

                            if ($lastparentids[$indent] >= 0) {
                                if (isset($this->_result['devices'][$group]['items'][$lastparentids[$indent]]['childs'])) {
                                    $this->_result['devices'][$group]['items'][$lastparentids[$indent]]['childs']++;
                                } else {
                                    $this->_result['devices'][$group]['items'][$lastparentids[$indent]]['childs'] = 1;
                                }
                            }

                            $lastindent = $indent;
                            $lastid = $id;
                        }
                    }
                    foreach ($this->_result['devices'][$group]['items'] as $id=>$data) { // type analize
                        if ((!isset($data['childs']) || ($data['childs']<1)) && ($data['parentid']>=0) && !preg_match("/^mirror$|^mirror-|^spare$|^spare-|^replacing$|^replacing-|^raidz[123]$|^raidz[123]-/", $data['name'])) {
                            $this->_result['devices'][$group]['items'][$id]['type'] = "disk";
                        } elseif (isset($data['childs']) && ($data['childs']>1) && !preg_match("/^spares$|^mirror$|^mirror-|^spare$|^spare-|^replacing$|^replacing-|^raidz[123]$|^raidz[123]-/", $data['name'])) {
                            $this->_result['devices'][$group]['items'][$id]['name2'] = "stripe";
                       }
                    }

                    foreach ($this->_result['devices'][$group]['items'] as $id=>$data) { // size optimize
                        if (($data['parentid']<0) && isset($data['childs']) && ($data['childs']==1)) {
                            if ($data['parentid']==-2) {
                                unset($this->_result['devices'][$group]['items'][$id]);
                            } elseif (($data['parentid'] == -1) && !isset($this->_result['devices'][$group]['items'][$id+1]['type'])) {
                                $this->_result['devices'][$group]['items'][$id+1]['name2'] = $data['name'];
                                $this->_result['devices'][$group]['items'][$id+1]['parentid'] = $data['parentid'];
                                unset($this->_result['devices'][$group]['items'][$id]);
                                foreach ($this->_result['devices'][$group]['items'] as $id2=>$data2) {
                                    if ($data2['parentid']>$id) {
                                        $this->_result['devices'][$group]['items'][$id2]['parentid'] = $data2['parentid'] - 1;
                                    }
                                }
                            }
                        }
                    }

                    if (isset($this->_result['devices'][$group]['items'][0])) {
                        $shift = true;
                    } else {
                        $shift = false;
                    }
                    foreach ($this->_result['devices'][$group]['items'] as $id=>$data) {
                        // reindex
                        if ($shift) {
                            $this->_result['devices'][$group]['items'][$id]['parentid']++;
                        }
                        if ($data['parentid']<0) {
                            $this->_result['devices'][$group]['items'][$id]['parentid'] = 0;
                        }

                         // name append
                        if (isset($data['name2'])) {
                            if (($data['name2']==="cache") || ($data['name2']==="logs")) {
                                $this->_result['devices'][$group]['items'][$id]['name'] = trim($data['name2']." ".$data['name']);
                            } else {
                                $this->_result['devices'][$group]['items'][$id]['name'] = trim($data['name']." ".$data['name2']);
                            }
                            unset($this->_result['devices'][$group]['items'][$id]['name2']);
                        }

                        // status and info normalize
                        if (isset($data['status'])) {
                                switch ($data['status']) {
                                    case 'AVAIL':
                                        if (isset($data['info'])) {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                        } else {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status'];
                                        }
                                        $this->_result['devices'][$group]['items'][$id]['status'] = "S";
                                        break;
                                    case 'INUSE':
                                    case 'DEGRADED':
                                        if (isset($data['info'])) {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                        } else {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status'];
                                        }
                                        $this->_result['devices'][$group]['items'][$id]['status'] = "W";
                                        break;
                                    case 'UNAVAIL':
                                    case 'FAULTED':
                                        if (isset($data['info'])) {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                        } else {
                                            $this->_result['devices'][$group]['items'][$id]['info'] = $data['status'];
                                        }
                                        $this->_result['devices'][$group]['items'][$id]['status'] = "F";
                                        break;
                                    default:
                                        $this->_result['devices'][$group]['items'][$id]['status'] = "ok";
                                }
                        } else {
                            if ($this->_result['devices'][$group]['items'][$id]['name'] == "spares") {
                                $this->_result['devices'][$group]['items'][$id]['status'] = "S";
                            } else {
                                $this->_result['devices'][$group]['items'][$id]['status'] = "ok";
                            }
                        }
                    }
                }
            }
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
        if (count($this->_filecontent)>0) {
            foreach ($this->prog_items as $item) {
                if (isset($this->_filecontent[$item]) && !is_null($buffer = $this->_filecontent[$item]) && (($buffer = trim($buffer)) != "")) {
                    switch ($item) {
                        case 'mdstat':
                            $this->execute_mdstat($buffer);
                            break;
                        case 'dmraid':
                            $this->execute_dmraid($buffer);
                            break;
                        case 'megactl':
                            $this->execute_megactl($buffer, false);
                            break;
                        case 'megasasctl':
                            $this->execute_megactl($buffer, true);
                            break;
                        case 'graid':
                            $this->execute_graid($buffer);
                            break;
                        case 'zpool':
                            $this->execute_zpool($buffer);
                            break;
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
        if (defined('PSI_PLUGIN_RAID_HIDE_DEVICES') && is_string(PSI_PLUGIN_RAID_HIDE_DEVICES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_RAID_HIDE_DEVICES)) {
                $hideRaids = eval(PSI_PLUGIN_RAID_HIDE_DEVICES);
            } else {
                $hideRaids = array(PSI_PLUGIN_RAID_HIDE_DEVICES);
            }
        }
        foreach ($this->_result['devices'] as $key=>$device) {
            if (!in_array(ltrim($key, "/"), $hideRaids, true)) {
                $dev = $this->xml->addChild("Raid");
                $dev->addAttribute("Device_Name", ltrim($key, "/")); //for megactl and megasasctl conflicts
                $dev->addAttribute("Program", $device["prog"]);
                if (isset($device['level'])) $dev->addAttribute("Level", strtolower($device["level"]));
                $dev->addAttribute("Status", strtolower($device["status"]));
                if (isset($device['name'])) $dev->addAttribute("Name", $device["name"]);
                if (isset($device['size'])) $dev->addAttribute("Size", $device["size"]);
                if (isset($device['stride'])) $dev->addAttribute("Stride", $device["stride"]);
                if (isset($device['subsets'])) $dev->addAttribute("Subsets", $device["subsets"]);
                if (isset($device['devs'])) $dev->addAttribute("Devs", $device["devs"]);
                if (isset($device['spares'])) $dev->addAttribute("Spares", $device["spares"]);

                if (isset($device['chunk_size'])) $dev->addAttribute("Chunk_Size", $device["chunk_size"]);
                if (isset($device['pers_superblock'])) $dev->addAttribute("Persistend_Superblock", $device["pers_superblock"]);
                if (isset($device['algorithm'])) $dev->addAttribute("Algorithm", $device["algorithm"]);
                if (isset($device['registered'])) $dev->addAttribute("Disks_Registered", $device["registered"]);
                if (isset($device['active'])) $dev->addAttribute("Disks_Active", $device["active"]);
                if (isset($device['controller'])) $dev->addAttribute("Controller", $device["controller"]);
                if (isset($device['battery'])) $dev->addAttribute("Battery", $device["battery"]);
                if (isset($device['supported'])) $dev->addAttribute("Supported", $device["supported"]);

                if (isset($device['action'])) {
                    $action = $dev->addChild("Action");
                    $action->addAttribute("Name", $device['action']['name']);
                    if (isset($device['action']['percent'])) $action->addAttribute("Percent", $device['action']['percent']);

                    if (isset($device['action']['finish_time'])) $action->addAttribute("Time_To_Finish", $device['action']['finish_time']);
                    if (isset($device['action']['finish_unit'])) $action->addAttribute("Time_Unit", $device['action']['finish_unit']);

                }
                $disks = $dev->addChild("RaidItems");
                if (isset($device['items']) && (sizeof($device['items'])>0)) foreach ($device['items'] as $disk) {
                    if (isset($disk['name'])) {
                        $disktemp = $disks->addChild("Item");
                        $disktemp->addAttribute("Name", $disk['name']);
                        // if (isset($disk['raid_index'])) $disktemp->addAttribute("Index", $disk['raid_index']);
                        if (isset($disk['parentid'])) $disktemp->addAttribute("ParentID", $disk['parentid']);
                        if (isset($disk['type'])) $disktemp->addAttribute("Type", $disk['type']);
                        // if (in_array(strtolower($device["status"]), array('ok', 'optimal', 'active', 'online', 'degraded'))) {
                            $disktemp->addAttribute("Status", $disk['status']);
                        //} else {
                        //    $disktemp->addAttribute("Status", 'W');
                        //}
                        if (isset($disk['info'])) $disktemp->addAttribute("Info", $disk['info']);
                    }
                }
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
