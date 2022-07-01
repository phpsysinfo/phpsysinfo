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

    private $prog_items = array('mdstat','dmraid','megactl','megasasctl','megaclisas-status','3ware-status','graid','zpool','storcli','perccli','idrac');

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->prog_items = array();
        if (defined('PSI_PLUGIN_RAID_PROGRAM') && is_string(PSI_PLUGIN_RAID_PROGRAM)) {
            if (is_string(PSI_PLUGIN_RAID_PROGRAM)) {
                if (preg_match(ARRAY_EXP, PSI_PLUGIN_RAID_PROGRAM)) {
                    $this->prog_items = eval(strtolower(PSI_PLUGIN_RAID_PROGRAM));
                } else {
                    $this->prog_items = array(strtolower(PSI_PLUGIN_RAID_PROGRAM));
                }
            } else {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");

                return;
            }
        }

        $notwas = true;
        switch (strtolower(PSI_PLUGIN_RAID_ACCESS)) {
        case 'command':
        case 'php-snmp':
            if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) {
                if ((PSI_OS == 'Linux') && in_array('mdstat', $this->prog_items)) {
                    CommonFunctions::rfts("/proc/mdstat", $this->_filecontent['mdstat'], 0, 4096, PSI_DEBUG);
                    $notwas = false;
                }
                if ((PSI_OS == 'Linux') && in_array('dmraid', $this->prog_items)) {
                    CommonFunctions::executeProgram("dmraid", "-s -vv 2>&1", $this->_filecontent['dmraid'], PSI_DEBUG);
                    $notwas = false;
                }
                if ((PSI_OS == 'Linux') && in_array('megactl', $this->prog_items)) {
                    CommonFunctions::executeProgram("megactl", "-vv", $this->_filecontent['megactl'], PSI_DEBUG);
                    $notwas = false;
                }
                if ((PSI_OS == 'Linux') && in_array('megasasctl', $this->prog_items)) {
                    CommonFunctions::executeProgram("megasasctl", "-vv", $this->_filecontent['megasasctl'], PSI_DEBUG);
                    $notwas = false;
                }
                if (in_array('megaclisas-status', $this->prog_items)) {
                    if (PSI_OS == 'WINNT') {
                        if (!WINNT::isAdmin()) {
                             if (CommonFunctions::_findProgram("megaclisas-status.py")) {
                                 $this->global_error->addError("RAID megaclisas-status.py error", "Program allowed for users with administrator privileges (run as administrator)");
                             } elseif (PSI_DEBUG) {
                                 $this->global_error->addError('find_program("megaclisas-status.py")', "program not found on the machine");
                             }
                        } else {
                            CommonFunctions::executeProgram("megaclisas-status.py", "", $this->_filecontent['megaclisas-status'], PSI_DEBUG);
                        }
                    } else {
                        CommonFunctions::executeProgram("megaclisas-status", "", $this->_filecontent['megaclisas-status'], PSI_DEBUG);
                    }
                    $notwas = false;
                }
                if (in_array('3ware-status', $this->prog_items)) {
                    if (PSI_OS == 'WINNT') {
                        if (!WINNT::isAdmin()) {
                             if (CommonFunctions::_findProgram("3ware-status.py")) {
                                 $this->global_error->addError("RAID 3ware-status.py error", "Program allowed for users with administrator privileges (run as administrator)");
                             } elseif (PSI_DEBUG) {
                                 $this->global_error->addError('find_program("3ware-status.py")', "program not found on the machine");
                             }
                        } else {
                            CommonFunctions::executeProgram("3ware-status.py", "", $this->_filecontent['3ware-status'], PSI_DEBUG);
                        }
                    } else {
                        CommonFunctions::executeProgram("3ware-status", "", $this->_filecontent['3ware-status'], PSI_DEBUG);
                    }
                    $notwas = false;
                }
                if ((PSI_OS == 'FreeBSD') && in_array('graid', $this->prog_items)) {
                    CommonFunctions::executeProgram("graid", "list", $this->_filecontent['graid'], PSI_DEBUG);
                    $notwas = false;
                }
                if (in_array('zpool', $this->prog_items)) {
                    CommonFunctions::executeProgram("zpool", "status", $this->_filecontent['zpool'], PSI_DEBUG);
                    $notwas = false;
                }
                if (in_array('storcli', $this->prog_items)) {
                    if ((PSI_OS == 'WINNT') && !WINNT::isAdmin() && (CommonFunctions::_findProgram("storcli64") || CommonFunctions::_findProgram("storcli"))) {
                      $this->global_error->addError("RAID storcli error", "Program allowed for users with administrator privileges (run as administrator)");
                    }
                    if (!(CommonFunctions::_findProgram("storcli64") && CommonFunctions::executeProgram("storcli64", "/call show all", $this->_filecontent['storcli'], PSI_DEBUG))) {
                        CommonFunctions::executeProgram("storcli", "/call show all", $this->_filecontent['storcli'], PSI_DEBUG);
                    }
                    $notwas = false;
                }
                if (in_array('perccli', $this->prog_items)) {
                    if ((PSI_OS == 'WINNT') && !WINNT::isAdmin() && (CommonFunctions::_findProgram("perccli64") || CommonFunctions::_findProgram("perccli"))) {
                      $this->global_error->addError("RAID perccli error", "Program allowed for users with administrator privileges (run as administrator)");
                    }
                    if (!(CommonFunctions::_findProgram("perccli64") && CommonFunctions::executeProgram("perccli64", "/call show all", $this->_filecontent['perccli'], PSI_DEBUG))) {
                        CommonFunctions::executeProgram("perccli", "/call show all", $this->_filecontent['perccli'], PSI_DEBUG);
                    }
                    $notwas = false;
                }
            }
            if (in_array('idrac', $this->prog_items)) {
                if (defined('PSI_PLUGIN_RAID_IDRAC_DEVICES') && is_string(PSI_PLUGIN_RAID_IDRAC_DEVICES)) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_RAID_IDRAC_DEVICES)) {
                        $devices = eval(PSI_PLUGIN_RAID_IDRAC_DEVICES);
                    } else {
                        $devices = array(PSI_PLUGIN_RAID_IDRAC_DEVICES);
                    }
                    if (strtolower(PSI_PLUGIN_RAID_ACCESS)=="command") {
                        foreach ($devices as $device) {
                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." ".$device." .1.3.6.1.4.1.674.10892.5.5.1.20", $buffer, PSI_DEBUG);
                            if (strlen($buffer) > 0) {
                                $this->_filecontent['idrac'][$device] = $buffer;
                            }
                        }
                    } else {
                        snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
                        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
                        foreach ($devices as $device) {
                            if (! PSI_DEBUG) {
                                restore_error_handler(); /* default error handler */
                                $old_err_rep = error_reporting();
                                error_reporting(E_ERROR); /* fatal errors only */
                            }
                            $bufferarr=snmprealwalk($device, "public", ".1.3.6.1.4.1.674.10892.5.5.1.20", 1000000 * PSI_SNMP_TIMEOUT_INT, PSI_SNMP_RETRY_INT);
                            if (! PSI_DEBUG) {
                                error_reporting($old_err_rep); /* restore error level */
                                set_error_handler('errorHandlerPsi'); /* restore error handler */
                            }
                            if (! empty($bufferarr)) {
                                $buffer="";
                                foreach ($bufferarr as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                                if (strlen($buffer) > 0) {
                                    $this->_filecontent['idrac'][$device] = $buffer;
                                }
                            }
                        }
                    }
                }

                $notwas = false;
            }
            if ($notwas) {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");
            }
            break;
        case 'data':
            if (!defined('PSI_EMU_HOSTNAME')) foreach ($this->prog_items as $item) {
                if (in_array($item, $this->prog_items)) {
                    if ($item !== 'idrac') {
                        CommonFunctions::rftsdata("raid".$item.".tmp", $this->_filecontent[$item], 0, 4096, false);
                    } elseif (defined('PSI_PLUGIN_RAID_IDRAC_DEVICES') && is_string(PSI_PLUGIN_RAID_IDRAC_DEVICES)) {
                        if (preg_match(ARRAY_EXP, PSI_PLUGIN_RAID_IDRAC_DEVICES)) {
                            $devices = eval(PSI_PLUGIN_RAID_IDRAC_DEVICES);
                        } else {
                            $devices = array(PSI_PLUGIN_RAID_IDRAC_DEVICES);
                        }
                        $pn=0;
                        foreach ($devices as $device) {
                            $buffer="";
                            if (CommonFunctions::rftsdata("raid".$item.$pn.".tmp", $buffer) && !empty($buffer)) {
                                $this->_filecontent['idrac'][$device] = $buffer;
                            }
                            $pn++;
                        }
                    }
                    $notwas = false;
                }
            }
            if ($notwas) {
                $this->global_error->addConfigError("__construct()", "[raid] PROGRAM");
            }
            break;
        default:
            $this->global_error->addConfigError("__construct()", "[raid] ACCESS");
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
                    if ($supported !== '') $this->_result['mdstat'][$dev]['supported'] = $supported;
                    $this->_result['mdstat'][$dev]['items'][0]['raid_index'] = -1; //must by first
                    $details = preg_split('/ /', $parts[1]);
                    if (!strstr($details[0], 'inactive')) {
                        if (isset($details[2]) && strstr($details[1], '(auto-read-only)')) {
                            $this->_result['mdstat'][$dev]['level'] = $details[2];
                            $this->_result['mdstat'][$dev]['status'] = $details[0]." ".$details[1];
                            //$this->_result['mdstat'][$dev]['items'][0]['name'] = $dev." ".$details[2];
                            $this->_result['mdstat'][$dev]['items'][0]['name'] = $details[2];
                            $this->_result['mdstat'][$dev]['items'][0]['status'] = "W";
                            $i = 3;
                        } else {
                            $this->_result['mdstat'][$dev]['level'] = $details[1];
                            $this->_result['mdstat'][$dev]['status'] = $details[0];
                            //$this->_result['mdstat'][$dev]['items'][0]['name'] = $dev." ".$details[1];
                            $this->_result['mdstat'][$dev]['items'][0]['name'] = $details[1];
                            $this->_result['mdstat'][$dev]['items'][0]['status'] = "ok";
                            $i = 2;
                        }
                    } else {
                        $this->_result['mdstat'][$dev]['level'] = "none";
                        $this->_result['mdstat'][$dev]['status'] = $details[0];
                        $this->_result['mdstat'][$dev]['items'][0]['name'] = $dev;
                        $this->_result['mdstat'][$dev]['items'][0]['status'] = "F";
                        $i = 1;
                    }
                    $this->_result['mdstat'][$dev]['items'][0]['parentid'] = 0;

                    for ($cnt_details = count($details); $i < $cnt_details; $i++) {
                        preg_match('/(([a-z0-9])+)(\[([0-9]+)\])(\([SF ]\))?/', trim($details[$i]), $partition);
                        if (count($partition) == 5 || count($partition) == 6) {
                            $this->_result['mdstat'][$dev]['items'][$partition[1]]['raid_index'] = intval(substr(trim($partition[3]), 1, -1));
                            if (isset($partition[5])) {
                                $search = array("(", ")");
                                $replace = array("", "");
                                $dstat = str_replace($search, $replace, trim($partition[5]));
                                $this->_result['mdstat'][$dev]['items'][$partition[1]]['status'] = $dstat;
                                if (($dstat === "F") && ($this->_result['mdstat'][$dev]['items'][0]['status'] === "ok")) $this->_result['mdstat'][$dev]['items'][0]['status'] = "W";
                            } else {
                                $this->_result['mdstat'][$dev]['items'][$partition[1]]['status'] = "ok";
                            }
                            $this->_result['mdstat'][$dev]['items'][$partition[1]]['name'] = $partition[1];
                            $this->_result['mdstat'][$dev]['items'][$partition[1]]['parentid'] = 1;
                            $this->_result['mdstat'][$dev]['items'][$partition[1]]['type'] = "disk";
                        }
                    }
                    $optionline = $raiddata[$count].$raiddata[$count+1];
                    $count++;
                    if (preg_match('/(\d+)k chunk/', $optionline, $chunksize)) {
                        $this->_result['mdstat'][$dev]['chunk_size'] = $chunksize[1];
                    }
                    if ($pos = strpos($optionline, "super non-persistent")) {
                        $this->_result['mdstat'][$dev]['pers_superblock'] = 0;
                    } else {
                        $this->_result['mdstat'][$dev]['pers_superblock'] = 1;
                    }
                    if ($pos = strpos($optionline, "algorithm")) {
                        $this->_result['mdstat'][$dev]['algorithm'] = trim(substr($optionline, $pos + 9, 2));
                    }
                    if (preg_match('/\[([0-9]+)\/([0-9]+)\]/', $optionline, $res)) {
                        $this->_result['mdstat'][$dev]['registered'] = $res[1];
                        $this->_result['mdstat'][$dev]['active'] = $res[2];
                    }

                    if (isset($this->_result['mdstat'][$dev]['items'])) {
                        asort($this->_result['mdstat'][$dev]['items']);
                    }
                    if ((!isset($this->_result['mdstat'][$dev]['registered']) || ($this->_result['mdstat'][$dev]['registered']<24)) && preg_match('/\[([_U]+)\]/', $optionline, $res) && (($reslen=strlen($res[1])) > 0)) {
                        $notsparecount = 0;
                        foreach ($this->_result['mdstat'][$dev]['items'] as $diskkey=>$disk) {
                            if (($diskkey!==0) && ($this->_result['mdstat'][$dev]['items'][$diskkey]['status']!=="S")) {
                                $notsparecount++;
                            }
                        }
                        if ($notsparecount == $reslen) {
                            $partnr = 0;
                            foreach ($this->_result['mdstat'][$dev]['items'] as $diskkey=>$disk) {
                                if (($diskkey!==0) && ($this->_result['mdstat'][$dev]['items'][$diskkey]['status']!=="S")) {
                                    if (($res[1][$partnr]=='_') && ($this->_result['mdstat'][$dev]['items'][$diskkey]['status']=="ok")) {
                                        $this->_result['mdstat'][$dev]['items'][$diskkey]['status']="W";
                                        if ($this->_result['mdstat'][$dev]['items'][0]['status'] === "ok") $this->_result['mdstat'][$dev]['items'][0]['status'] = "W";
                                    }
                                    $partnr++;
                                }
                            }
                        } elseif ($reslen-$notsparecount == 1) {
                            $partnr = 0;
                            foreach ($this->_result['mdstat'][$dev]['items'] as $diskkey=>$disk) {
                                if (($diskkey!==0) && ($this->_result['mdstat'][$dev]['items'][$diskkey]['status']!=="S")) {
                                    if ($res[1][$partnr]=='_') {
                                        $this->_result['mdstat'][$dev]['items']['none']['raid_index']=$this->_result['mdstat'][$dev]['items'][$diskkey]['raid_index']-1;
                                        $this->_result['mdstat'][$dev]['items']['none']['status']="E";
                                        $this->_result['mdstat'][$dev]['items']['none']['name']="none";
                                        $this->_result['mdstat'][$dev]['items']['none']['parentid'] = 1;
                                        $this->_result['mdstat'][$dev]['items']['none']['type'] = "disk";
                                    }
                                    $partnr++;
                                }
                            }
                            if ($res[1][$partnr]=='_') {
                                $this->_result['mdstat'][$dev]['items']['none']['raid_index']=$this->_result['mdstat'][$dev]['items'][$diskkey]['raid_index']+1;
                                $this->_result['mdstat'][$dev]['items']['none']['status']="E";
                                $this->_result['mdstat'][$dev]['items']['none']['name']="none";
                                $this->_result['mdstat'][$dev]['items']['none']['parentid'] = 1;
                                $this->_result['mdstat'][$dev]['items']['none']['type']="disk";
                                if ($this->_result['mdstat'][$dev]['items'][0]['status'] === "ok") $this->_result['mdstat'][$dev]['items'][0]['status'] = "W";
                            }
                            asort($this->_result['mdstat'][$dev]['items']);
                            foreach ($this->_result['mdstat'][$dev]['items'] as $diskkey=>$disk) {
                                if ($diskkey=="none") {
                                    $this->_result['mdstat'][$dev]['items'][$diskkey]['raid_index']="unknown";
                                }
                            }
                        } else {
                            foreach ($this->_result['mdstat'][$dev]['items'] as $diskkey=>$disk) {
                                if ($this->_result['mdstat'][$dev]['items'][$diskkey]['status']=="ok") {
                                    $this->_result['mdstat'][$dev]['items'][$diskkey]['status']="W";
                                }
                            }
                            for ($partnr=0; $partnr<$reslen-$notsparecount; $partnr++) {
                                    $this->_result['mdstat'][$dev]['items']['none'.$partnr]['raid_index']="unknown";
                                    $this->_result['mdstat'][$dev]['items']['none'.$partnr]['status']="E";
                                    $this->_result['mdstat'][$dev]['items']['none'.$partnr]['name'] = "none".$partnr;
                                    $this->_result['mdstat'][$dev]['items']['none'.$partnr]['parentid'] = 1;
                                    $this->_result['mdstat'][$dev]['items']['none'.$partnr]['type'] = "disk";
                            }
                        }
                    }
                    if (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)%/'), $raiddata[$count + 1], $res) || (preg_match(('/([a-z]+)( *)=( *)([0-9\.]+)/'), $optionline, $res))) {
                        list($this->_result['mdstat'][$dev]['action']['name'], $this->_result['mdstat'][$dev]['action']['percent']) = preg_split("/=/", str_replace("%", "", $res[0]));
                        if (preg_match(('/([a-z]*=[0-9\.]+[a-z]+)/'), $raiddata[$count + 1], $res)) {
                            $time = preg_split("/=/", $res[0]);
                            list($this->_result['mdstat'][$dev]['action']['finish_time'], $this->_result['mdstat'][$dev]['action']['finish_unit']) = sscanf($time[1], '%f%s');
                        }
                    } elseif (preg_match(('/^( *)([a-z]+)( *)=( *)([A-Z]+)$/'), $raiddata[$count + 1], $res)) {
                       $this->_result['mdstat'][$dev]['status'] .= " ".trim($raiddata[$count + 1]);
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
                    $this->_result['mdstat']['spare']['status'] = "spare";
                    $this->_result['mdstat']['spare']['items'][0]['name'] = "spare";
                    $this->_result['mdstat']['spare']['items'][0]['parentid'] = 0;
                    $this->_result['mdstat']['spare']['items'][0]['status'] = "S";
                    foreach ($details as $id=>$disk) {
                        $this->_result['mdstat']['spare']['items'][$id+1]['name'] = $disk;
                        $this->_result['mdstat']['spare']['items'][$id+1]['parentid'] = 1;
                        $this->_result['mdstat']['spare']['items'][$id+1]['status'] = "S";
                        $this->_result['mdstat']['spare']['items'][$id+1]['type'] = "disk";
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
                            if (!isset($this->_result['dmraid'][$partition[2]]['items'][0]['parentid'])) {
                                $this->_result['dmraid'][$partition[2]]['items'][0]['parentid'] = 0;
                                $this->_result['dmraid'][$partition[2]]['items'][0]['name'] = $partition[2];
                            }
                            $this->_result['dmraid'][$partition[2]]['items'][$partition[1]]['status'] = "ok";
                            $this->_result['dmraid'][$partition[2]]['items'][$partition[1]]['type'] = "disk";
                            $this->_result['dmraid'][$partition[2]]['items'][$partition[1]]['parentid'] = 1;
                            $this->_result['dmraid'][$partition[2]]['items'][$partition[1]]['name'] = $partition[1];
                            $this->_result['dmraid'][$partition[2]]['status'] = "ok";
                            $this->_result['dmraid'][$partition[2]]['level'] = "unknown";
                        } elseif (preg_match('/^ERROR: .* device\s+\/dev\/(.+)\s+(.+)\s+in RAID set\s+\"(.+)\"/', $line, $partition)) {
                            if (!isset($this->_result['dmraid'][$partition[3]]['items'][0]['parentid'])) {
                                $this->_result['dmraid'][$partition[3]]['items'][0]['parentid'] = 0;
                                $this->_result['dmraid'][$partition[3]]['items'][0]['name'] = $partition[3];
                            }
                            $this->_result['dmraid'][$partition[3]]['level'] = "unknown";
                            $this->_result['dmraid'][$partition[3]]['items'][$partition[1]]['type'] = "disk";
                            $this->_result['dmraid'][$partition[3]]['items'][$partition[1]]['parentid'] = 1;
                            if ($partition[2]=="broken") {
                                $this->_result['dmraid'][$partition[3]]['items'][$partition[1]]['status'] = "F";
                                $this->_result['dmraid'][$partition[3]]['status'] = "F";
                            } else {
                                $this->_result['dmraid'][$partition[3]]['items'][$partition[1]]['status'] = "W";
                                $this->_result['dmraid'][$partition[3]]['status'] = "W";
                            }
                            $this->_result['dmraid'][$partition[3]]['items'][$partition[1]]['name'] = $partition[1];
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
                        $this->_result['dmraid'][$group]['name'] = trim($arrname[1]);

                        $this->_result['dmraid'][$group]['items'][0]['name'] = trim($arrname[1]);

                        if (preg_match('/^size\s*:\s*(.*)/m', $block, $capacity)) {
                            $this->_result['dmraid'][$group]['capacity'] = trim($capacity[1]);
                        }
                        if (preg_match('/^stride\s*:\s*(.*)/m', $block, $stride)) {
                                $this->_result['dmraid'][$group]['stride'] = trim($stride[1]);
                        }
                        if (preg_match('/^type\s*:\s*(.*)/m', $block, $type)) {
                            $this->_result['dmraid'][$group]['level'] = trim($type[1]);
                            //$this->_result['dmraid'][$group]['items'][0]['name'] .= " ".trim($type[1]);
                            $this->_result['dmraid'][$group]['items'][0]['name'] = trim($type[1]);
                        }
                        if (preg_match('/^status\s*:\s*(.*)/m', $block, $status)) {
                            $this->_result['dmraid'][$group]['status'] = trim($status[1]);
                            switch (trim($status[1])) {
                            case "broken":
                                $this->_result['dmraid'][$group]['items'][0]['status'] = "F";
                                break;
                            case "inconsistent":
                                $this->_result['dmraid'][$group]['items'][0]['status'] = "W";
                                break;
                            default:
                                $this->_result['dmraid'][$group]['items'][0]['status'] = trim($status[1]);
                            }
                        }
                        if (preg_match('/^subsets\s*:\s*(.*)/m', $block, $subsets)) {
                            $this->_result['dmraid'][$group]['subsets'] = trim($subsets[1]);
                        }
                        if (preg_match('/^devs\s*:\s*(.*)/m', $block, $devs)) {
                            $this->_result['dmraid'][$group]['devs'] = trim($devs[1]);
                        }
                        if (preg_match('/^spares\s*:\s*(.*)/m', $block, $spares)) {
                                $this->_result['dmraid'][$group]['spares'] = trim($spares[1]);
                        }

                        if (!isset($this->_result['dmraid'][$group]['items'][0]['parentid'])) {
                            $this->_result['dmraid'][$group]['items'][0]['parentid'] = 0;
                        }

                        $group = "";
                    }
                }
            }
            if (isset($this->_result['dmraid'])) {
                foreach ($this->_result['dmraid'] as $gid=>$group) {
                    $id = 1;
                    if (isset($group['devs']) && ($group['devs']>0) &&
                       (!isset($group['items']) || (count($group['items'])<$group['devs'])) &&
                       isset($group['subsets']) && ($group['subsets']>0)) for ($i = 0; $i < $group['subsets']; $i++) {
                        if (isset($this->_result['dmraid'][$gid."-".$i]['items'][0]['parentid'])) {
                            foreach ($this->_result['dmraid'][$gid."-".$i]['items'] as $fid=>$from) {
                                if ($fid===0) {
                                    $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['parentid'] = 1;
                                    $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['status'] = $from['status'];
                                    $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['name'] = $gid."-".$i." ".$from['name'];
                                    if (isset($from['type'])) $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['type'] = $from['type'];
                                } else {
                                    $this->_result['dmraid'][$gid]['items'][$from['name']]['parentid'] = 1+$id;
                                    $this->_result['dmraid'][$gid]['items'][$from['name']]['status'] = $from['status'];
                                    $this->_result['dmraid'][$gid]['items'][$from['name']]['name'] = $from['name'];
                                    if (isset($from['type'])) $this->_result['dmraid'][$gid]['items'][$from['name']]['type'] = $from['type'];
                                }
                            }
                            $id+=count($this->_result['dmraid'][$gid."-".$i]['items']);
                            unset($this->_result['dmraid'][$gid."-".$i]);
                        } else {
                            $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['parentid'] = 1;
                            $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['status'] = "unknown";
                            $this->_result['dmraid'][$gid]['items'][$gid."-".$i]['name'] = $gid."-".$i;
                            $id++;
                        }
                    }
                }
                foreach ($this->_result['dmraid'] as $gid=>$group) {
                    if (($group['name'] !== $gid) && isset($group['items'][0]['parentid'])) {
                        $this->_result['dmraid'][$gid]['items'][0]['name'] = $group['name']." ".$group['items'][0]['name'];
                    }
                }
            }
        }
    }

    private function execute_megactl($buffer, $sas = false)
    {
        if ($sas === true) {
            $prog = "megasasctl";
        } else {
            $prog = "megactl";
        }
        $raiddata = preg_split("/(\r?\n)+(?=[a-z]\d+ )/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) foreach ($raiddata as $raidgroup) {
            if (preg_match("/^([a-z]\d+) /", $raidgroup, $buff)) {
                if (preg_match("/^[a-z]\d+ ([^:\r\n]+) [^:\r\n]+:/", $raidgroup, $geom) || preg_match("/^[a-z]\d+ ([^:\r\n]+)/", $raidgroup, $geom)) {
                    $controller = trim($geom[1]);
                } else {
                    $controller = '';
                }
                if (preg_match("/^[a-z]\d+ .+ batt:([^:\r\n,]+)/", $raidgroup, $batt)) {
                    if (preg_match("/^([^\/]+)\/((\d+)mV\/(-?\d+)C)$/", trim($batt[1]), $battarr) && ($battarr !== "0mV/0C")) {
                        $battery = trim($battarr[1]);
                        $battvolt = $battarr[3]/1000;
                        $batttemp = $battarr[4];
                    } else {
                        $battery = trim($batt[1]);
                        $battvolt = '';
                        $batttemp = '';
                    }
                } else {
                    $battery = '';
                    $battvolt = '';
                    $batttemp = '';
                }
                if (preg_match("/^[a-z]\d+ .+ mem:(\d+)MiB/", $raidgroup, $batt)) {
                    $cache_size = $batt[1]*1024*1024;
                } else {
                    $cache_size = '';
                }
                $group = $buff[1];
                $lines = preg_split("/\r?\n/", $raidgroup, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($lines)) {
                    unset($lines[0]);
                    foreach ($lines as $line) {
                        $details = preg_split('/ /', preg_replace('/^hot spares +:/', 'hotspare:', $line), -1, PREG_SPLIT_NO_EMPTY);
                        if (($countdet = count($details)) >= 2) {
                            $size[0] = -1;
                            for ($ind = $countdet; $ind > 1;) {
                               if (preg_match('/(\d+)((B)|(KiB)|(MiB)|(GiB)|(TiB)|(PiB))/', $details[--$ind], $size)) { //Find size
                                    $size[0] = $ind;
                                    break;
                                } else {
                                   $size[0] = -1;
                                }
                            }
                            $model = '';
                            $serial = '';
                            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS && ($size[0] >= 2)) {
                                for ($ind = 1; $ind < $size[0]; $ind++) {
                                    if (preg_match('/:/', $details[$ind])) {
                                        break;
                                    }
                                    $model .= " ".$details[$ind];
                                }
                                $model = trim($model);
                                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                    for (; $ind < $size[0]; $ind++) {
                                        if (preg_match('/^s\/n:(.+)$/', $details[$ind], $ser)) {
                                            $serial = $ser[1];
                                            break;
                                        }
                                    }
                                }
                            }
                            if (($countdet == 6) && ($details[2] === "RAID") && ($size[0] == 1)) {
                                switch ($size[2]) {
                                case 'B':
                                    $this->_result[$prog][$details[0]]['capacity'] = $size[1];
                                    break;
                                case 'KiB':
                                    $this->_result[$prog][$details[0]]['capacity'] = 1024*$size[1];
                                    break;
                                case 'MiB':
                                    $this->_result[$prog][$details[0]]['capacity'] = 1024*1024*$size[1];
                                    break;
                                case 'GiB':
                                    $this->_result[$prog][$details[0]]['capacity'] = 1024*1024*1024*$size[1];
                                    break;
                                case 'TiB':
                                    $this->_result[$prog][$details[0]]['capacity'] = 1024*1024*1024*1024*$size[1];
                                    break;
                                case 'PiB':
                                    $this->_result[$prog][$details[0]]['capacity'] = 1024*1024*1024*1024*1024*$size[1];
                                }
                                $this->_result[$prog][$details[0]]['level'] = "RAID".$details[3]." ".$details[4];
                                $this->_result[$prog][$details[0]]['status'] = $details[5];
                                if ($controller !== '') $this->_result[$prog][$details[0]]['controller'] = $controller;
                                if ($battery !== '') $this->_result[$prog][$details[0]]['battery'] = $battery;
                                if ($battvolt !== '') $this->_result[$prog][$details[0]]['battvolt'] = $battvolt;
                                if ($batttemp !== '') $this->_result[$prog][$details[0]]['batttemp'] = $batttemp;
                                if ($cache_size !== '') $this->_result[$prog][$details[0]]['cache_size'] = $cache_size;
                                $this->_result[$prog][$details[0]]['items'][$details[0]]['parentid'] = 0;
                                $this->_result[$prog][$details[0]]['items'][$details[0]]['name'] = "RAID".$details[3]." ".$details[4];
                                if ($details[5] !== 'optimal') {
                                    $this->_result[$prog][$details[0]]['items'][$details[0]]['info'] = $details[5];
                                }
                                switch ($details[5]) {
                                case 'optimal':
                                    $this->_result[$prog][$details[0]]['items'][$details[0]]['status'] = "ok";
                                    break;
                                case 'OFFLINE':
                                    $this->_result[$prog][$details[0]]['items'][$details[0]]['status'] = "F";
                                    break;
                                default:
                                    $this->_result[$prog][$details[0]]['items'][$details[0]]['status'] = "W";
                                }
                            } elseif (($countdet >= 2) && (($details[0]==='unconfigured:') || ($details[0]==='hotspare:'))) {
                                $itemn0 = rtrim($details[0], ':');
                                $itemn = $group .'-'.$itemn0;
                                $this->_result[$prog][$itemn]['status'] = $itemn0;
                                if ($controller !== '') $this->_result[$prog][$itemn]['controller'] = $controller;
                                if ($battery !== '') $this->_result[$prog][$itemn]['battery'] = $battery;
                                if ($battvolt !== '') $this->_result[$prog][$itemn]['battvolt'] = $battvolt;
                                if ($batttemp !== '') $this->_result[$prog][$itemn]['batttemp'] = $batttemp;
                                if ($cache_size !== '') $this->_result[$prog][$itemn]['cache_size'] = $cache_size;
                                $this->_result[$prog][$itemn]['items'][$itemn]['parentid'] = 0;
                                $this->_result[$prog][$itemn]['items'][$itemn]['name'] = $itemn0;
                                if ($details[0]==='unconfigured:') {
                                    $this->_result[$prog][$itemn]['items'][$itemn]['status'] = "U";
                                } else {
                                    $this->_result[$prog][$itemn]['items'][$itemn]['status'] = "S";
                                }
                            } elseif (($countdet >= 4) && ($size[0] >= 1) && ($countdet - $size[0] == 3)) {
                                if (isset($this->_result[$prog][$details[$countdet-2]])) {
                                    $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['parentid'] = 1;
                                    $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['type'] = "disk";
                                    $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['name'] = $details[0];
                                    if ($details[$countdet-1] !== 'online') {
                                        $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['info'] = $details[$countdet-1];
                                    }
                                    switch ($details[$countdet-1]) {
                                    case 'online':
                                        $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['status'] = "ok";
                                        break;
                                    case 'hotspare':
                                        $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['status'] = "S";
                                        break;
                                    case 'rdy/fail':
                                        $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['status'] = "F";
                                        break;
                                    default:
                                        $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['status'] = "W";
                                    }
                                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                        switch ($size[2]) {
                                        case 'B':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = $size[1];
                                            break;
                                        case 'KiB':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = 1024*$size[1];
                                            break;
                                        case 'MiB':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = 1024*1024*$size[1];
                                            break;
                                        case 'GiB':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = 1024*1024*1024*$size[1];
                                            break;
                                        case 'TiB':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = 1024*1024*1024*1024*$size[1];
                                            break;
                                        case 'PiB':
                                            $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['capacity'] = 1024*1024*1024*1024*1024*$size[1];
                                        }
                                        if ($model !== '') $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['model'] = $model;
                                        if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                            if ($serial !== '') $this->_result[$prog][$details[$countdet-2]]['items'][$details[0]]['serial'] = $serial;
                                        }
                                    }
                                }
                            } elseif (($countdet >= 3) && ($size[0] >= 1) && ($countdet - $size[0] == 2)) {
                                $itemn = '';
                                switch ($details[$countdet-1]) {
                                case 'BAD':
                                case 'ready':
                                    $itemn = $group .'-unconfigured';
                                    break;
                                case 'hotspare':
                                    $itemn = $group .'-hotspare';
                                }
                                if (($itemn !== '') && isset($this->_result[$prog][$itemn])) {
                                    $this->_result[$prog][$itemn]['items'][$details[0]]['parentid'] = 1;
                                    $this->_result[$prog][$itemn]['items'][$details[0]]['type'] = "disk";
                                    $this->_result[$prog][$itemn]['items'][$details[0]]['name'] = $details[0];
                                    $this->_result[$prog][$itemn]['items'][$details[0]]['info'] = $details[$countdet-1];
                                    switch ($details[$countdet-1]) {
                                    case 'ready':
                                        $this->_result[$prog][$itemn]['items'][$details[0]]['status'] = "U";
                                        break;
                                    case 'hotspare':
                                        $this->_result[$prog][$itemn]['items'][$details[0]]['status'] = "S";
                                        break;
                                    default:
                                        $this->_result[$prog][$itemn]['items'][$details[0]]['status'] = "F";
                                    }
                                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                        switch ($size[2]) {
                                        case 'B':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = $size[1];
                                            break;
                                        case 'KiB':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = 1024*$size[1];
                                            break;
                                        case 'MiB':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = 1024*1024*$size[1];
                                            break;
                                        case 'GiB':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = 1024*1024*1024*$size[1];
                                            break;
                                        case 'TiB':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = 1024*1024*1024*1024*$size[1];
                                            break;
                                        case 'PiB':
                                            $this->_result[$prog][$itemn]['items'][$details[0]]['capacity'] = 1024*1024*1024*1024*1024*$size[1];
                                        }
                                        if ($model !== '') $this->_result[$prog][$itemn]['items'][$details[0]]['model'] = $model;
                                        if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                            if ($serial !== '') $this->_result[$prog][$itemn]['items'][$details[0]]['serial'] = $serial;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function execute_status($buffer, $_3ware = false)
    {
        $carr=array();
        if ($_3ware === true) {
            $prog = "3ware-status";
            $split = "/\t/";
        } else {
            $prog = "megaclisas-status";
            $split = "/[\s]?\|[\s]?/";
        }
        $buffLines = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($buffLines as $buffLine) {
            if (preg_match("/^(c\d+)[ |]/", $buffLine, $cbuff)) {
                $cname = $cbuff[1];
                $buffArgs = preg_split("/[\s]?\|[\s]?/", $buffLine, -1, PREG_SPLIT_NO_EMPTY);
                if ((($countargs = count($buffArgs)) == 2) || ($countargs == 5) || ($countargs == 6)) {
                    $carr[$cname]['controller'] = trim($buffArgs[1]);
                    if ($countargs == 6) {
                        $carr[$cname]['battery'] = trim($buffArgs[4]);
                    }
                    if ($countargs > 2) {
                        $carr[$cname]['cache_size'] = trim($buffArgs[2]);
                        if (preg_match("/^FW:\s+(\S+)$/", trim($buffArgs[$countargs - 1]), $pregout)) {
                            $carr[$cname]['firmware'] = $pregout[1];
                        } else {
                            $carr[$cname]['firmware'] = trim($buffArgs[$countargs -1]);
                        }
                        if (preg_match("/^(\d+)C$/", trim($buffArgs[3]), $pregout)) {
                            $carr[$cname]['temperature'] = $pregout[1];
                        }
                        $unit = preg_replace("/^[\d\s]+/", "", trim($buffArgs[2]));
                        $value = preg_replace("/[\D\s]+$/", "", trim($buffArgs[2]));
                        switch ($unit) {
                        case 'B':
                            $carr[$cname]['cache_size'] = $value;
                            break;
                        case 'KB':
                            $carr[$cname]['cache_size'] = 1024*$value;
                            break;
                        case 'MB':
                            $carr[$cname]['cache_size'] = 1024*1024*$value;
                            break;
                        case 'Gb':
                        case 'GB':
                            $carr[$cname]['cache_size'] = 1024*1024*1024*$value;
                            break;
                        case 'TB':
                            $carr[$cname]['cache_size'] = 1024*1024*1024*1024*$value;
                            break;
                        case 'PB':
                            $carr[$cname]['cache_size'] = 1024*1024*1024*1024*1024*$value;
                        }
                    }
                } elseif ($countargs == 3) {
                    $carr[$cname]['controller'] = trim($buffArgs[1]);
                    if (preg_match("/^FW:\s+(\S+)$/", trim($buffArgs[2]), $pregout)) {
                        $carr[$cname]['firmware'] = $pregout[1];
                    } else {
                        $carr[$cname]['firmware'] = trim($buffArgs[2]);
                    }
                }
            } elseif (preg_match("/^((c\d+)u\d+)[\t |]/", $buffLine, $ubuff)) {
                $uname = $ubuff[1];
                $cname = $ubuff[2];
                $buffArgs = preg_split($split, $buffLine, -1, PREG_SPLIT_NO_EMPTY);
                if (((count($buffArgs) == 4) || (count($buffArgs) == 5) || (count($buffArgs) == 9) || (count($buffArgs) == 10)) && isset($carr[$cname])) {
                    $this->_result[$prog][$uname]['level'] = trim($buffArgs[1]);
                    $this->_result[$prog][$uname]['controller'] = $carr[$cname]['controller'];
                    if (isset($carr[$cname]['battery'])) $this->_result[$prog][$uname]['battery'] = $carr[$cname]['battery'];
                    if (isset($carr[$cname]['cache_size'])) $this->_result[$prog][$uname]['cache_size'] = $carr[$cname]['cache_size'];
                    if (isset($carr[$cname]['firmware'])) $this->_result[$prog][$uname]['firmware'] = $carr[$cname]['firmware'];
                    if (isset($carr[$cname]['temperature'])) $this->_result[$prog][$uname]['temperature'] = $carr[$cname]['temperature'];
                    $unit = preg_replace("/^[\d\s]+/", "", trim($buffArgs[2]));
                    $value = preg_replace("/[\D\s]+$/", "", trim($buffArgs[2]));
                    switch ($unit) {
                    case 'B':
                        $this->_result[$prog][$uname]['capacity'] = $value;
                        break;
                    case 'K':
                        $this->_result[$prog][$uname]['capacity'] = 1024*$value;
                        break;
                    case 'M':
                        $this->_result[$prog][$uname]['capacity'] = 1024*1024*$value;
                        break;
                    case 'G':
                        $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*$value;
                        break;
                    case 'T':
                        $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*1024*$value;
                        break;
                    case 'P':
                        $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*1024*1024*$value;
                    }
                    if ((count($buffArgs) == 4) || (count($buffArgs) == 5)) {
                        $this->_result[$prog][$uname]['status'] = trim($buffArgs[3]);
                    } else {
                        $this->_result[$prog][$uname]['status'] = trim($buffArgs[6]);
                        $this->_result[$prog][$uname]['diskcache'] = trim($buffArgs[5]);
                        $this->_result[$prog][$uname]['name'] = trim($buffArgs[7]);
                        if (preg_match("/^(\d+) KB$/", trim($buffArgs[3]), $strsize)) {
                            $this->_result[$prog][$uname]['stripe_size'] = $strsize[1] * 1024;
                        }
                        $ctype = trim($buffArgs[4]);
                        if (preg_match("/^NORA,/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "noReadAhead";
                        elseif (preg_match("/^RA,/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "readAhead";
                        elseif (preg_match("/^ADRA,/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "adaptiveReadAhead";
                        if (preg_match("/,WT$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeThrough";
                        elseif (preg_match("/,WB$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeBack";
                        elseif (preg_match("/,WBF$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeBackForce";
                    }
                    $this->_result[$prog][$uname]['items'][$uname]['parentid'] = 0;
                    $this->_result[$prog][$uname]['items'][$uname]['name'] = trim($buffArgs[1]);
                    if (preg_match("/(.+) : Completed (\d+)%/", trim($buffArgs[count($buffArgs)-1]), $progarr)) {
                        $this->_result[$prog][$uname]['action']['name'] = trim($progarr[1]);
                        $this->_result[$prog][$uname]['action']['percent'] = trim($progarr[2]);
                    }
                    switch ($this->_result[$prog][$uname]['status']) {
                    case 'OK':
                    case 'Optimal':
                        $this->_result[$prog][$uname]['items'][$uname]['status'] = "ok";
                        break;
                    case 'Offline':
                        $this->_result[$prog][$uname]['items'][$uname]['status'] = "F";
                        break;
                    default:
                        $this->_result[$prog][$uname]['items'][$uname]['status'] = "W";
                    }
                }
            } elseif (preg_match("/^(((c\d+)u\d+)p\d+)[\t |]/", $buffLine, $pbuff)) {
                $pname = $pbuff[1];
                $uname = $pbuff[2];
                $buffArgs = preg_split($split, $buffLine, -1, PREG_SPLIT_NO_EMPTY);
                if (((count($buffArgs) == 3) || (count($buffArgs) == 9)) && (isset($this->_result[$prog][$uname]))) {
                    $this->_result[$prog][$uname]['items'][$pname]['parentid'] = 1;
                    $this->_result[$prog][$uname]['items'][$pname]['name'] = $pname;
                    if (count($buffArgs) == 3) {
                        $this->_result[$prog][$uname]['items'][$pname]['type'] = "disk";
                        $dskstat = trim($buffArgs[2]);
                        /* too long
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                           && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) { // due to mixing name and serial number
                            $this->_result[$prog][$uname]['items'][$pname]['model'] = trim($buffArgs[1]);
                        }
                        */
                    } else {
                        if (preg_match("/^(\d+)C$/", trim($buffArgs[6]), $pregout)) {
                            $this->_result[$prog][$uname]['items'][$pname]['temperature'] = $pregout[1];
                        }
                        if (trim($buffArgs[1])==="SSD") {
                            $this->_result[$prog][$uname]['items'][$pname]['type'] = "ssd";
                        } else {
                            $this->_result[$prog][$uname]['items'][$pname]['type'] = "disk";
                        }
                        $dskstat = trim($buffArgs[4]);
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                            /* too long
                            if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) { // due to mixing name and serial number
                                $this->_result[$prog][$uname]['items'][$pname]['model'] = trim($buffArgs[2]);
                            }
                            */
                            $capacity = preg_replace("/,/", ".", trim($buffArgs[3]));
                            $unit = preg_replace("/^[\d\.\s]+/", "", $capacity);
                            $value = preg_replace("/[\D\s]+$/", "", $capacity);
                            switch ($unit) {
                            case 'B':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = $value;
                                break;
                            case 'KB':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = round(1024*$value);
                                break;
                            case 'MB':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = round(1024*1024*$value);
                                break;
                            case 'Gb':
                            case 'GB':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = round(1024*1024*1024*$value);
                                break;
                            case 'TB':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = round(1024*1024*1024*1024*$value);
                                break;
                            case 'PB':
                                $this->_result[$prog][$uname]['items'][$pname]['capacity'] = round(1024*1024*1024*1024*1024*$value);
                            }
                        }
                    }
                    switch ($dskstat) {
                    case 'OK':
                    case 'Online':
                    case 'Online, Spun Up':
                        $this->_result[$prog][$uname]['items'][$pname]['status'] = "ok";
                        break;
/*                    case 'JBOD':
                    case 'Unconfigured(good), Spun Up':
                    case 'Unconfigured(good), Spun down':
                        $this->_result[$prog][$uname]['items'][$pname]['status'] = "U";
                        break;
                    case 'Hotspare, Spun Up':
                    case 'Hotspare, Spun down':
                        $this->_result[$prog][$uname]['items'][$pname]['status'] = "S";
                        break;*/
                    default:
                        if (preg_match("/^(\S+) \((\d+)%\)/", $dskstat, $progarr)) {
                            $this->_result[$prog][$uname]['items'][$pname]['status'] = "W";
                            $this->_result[$prog][$uname]['action']['name'] = trim($progarr[1]);
                            $this->_result[$prog][$uname]['action']['percent'] = trim($progarr[2]);
                        }
                    }
                    if ($dskstat !== "OK") $this->_result[$prog][$uname]['items'][$pname]['info'] = $dskstat;
                }
            } elseif (preg_match("/^((c\d+)uXpY)[\t |]/", $buffLine, $pbuff)) {
                $pname = $pbuff[1];
                $cname = $pbuff[2];
                $uname = $cname."-unconfigured";
                $this->_result[$prog][$uname]['controller'] = $carr[$cname]['controller'];
                if (isset($carr[$cname]['battery'])) $this->_result[$prog][$uname]['battery'] = $carr[$cname]['battery'];
                if (isset($carr[$cname]['cache_size'])) $this->_result[$prog][$uname]['cache_size'] = $carr[$cname]['cache_size'];
                if (isset($carr[$cname]['firmware'])) $this->_result[$prog][$uname]['firmware'] = $carr[$cname]['firmware'];
                if (isset($carr[$cname]['temperature'])) $this->_result[$prog][$uname]['temperature'] = $carr[$cname]['temperature'];
                $this->_result[$prog][$uname]['status'] = "unconfigured";
                $this->_result[$prog][$uname]['items'][$uname]['parentid'] = 0;
                $this->_result[$prog][$uname]['items'][$uname]['name'] = "unconfigured";
                $this->_result[$prog][$uname]['items'][$uname]['status'] = "U";
                if (!isset($this->_result[$prog][$uname]['items'][$uname]['count'])) {
                    $id = 0;
                } else {
                    $id = $this->_result[$prog][$uname]['items'][$uname]['count'];
                    $id++;
                }
                $this->_result[$prog][$uname]['items'][$uname]['count'] = $id;

                $buffArgs = preg_split($split, $buffLine, -1, PREG_SPLIT_NO_EMPTY);
                if (((count($buffArgs) == 3) || (count($buffArgs) == 9) || (count($buffArgs) == 10))) {
                    $this->_result[$prog][$uname]['items'][$uname."-".$id]['parentid'] = 1;
                    $this->_result[$prog][$uname]['items'][$uname."-".$id]['name'] = $pname;
                    if (count($buffArgs) == 3) {
                        $this->_result[$prog][$uname]['items'][$uname."-".$id]['type'] = "disk";
                        $dskstat = trim($buffArgs[2]);
                    } else {
                        if (trim($buffArgs[1])==="SSD") {
                            $this->_result[$prog][$uname]['items'][$uname."-".$id]['type'] = "ssd";
                        } else {
                            $this->_result[$prog][$uname]['items'][$uname."-".$id]['type'] = "disk";
                        }
                        $dskstat = trim($buffArgs[4]);
                    }
                    switch ($dskstat) {
/*                        case 'Online':
                        case 'Online, Spun Up':*/
                    case 'JBOD':
                        $this->_result[$prog][$uname]['items'][$uname."-".$id]['status'] = "ok";
                        break;
                    case 'Unconfigured(bad)':
                        $this->_result[$prog][$uname]['items'][$uname."-".$id]['status'] = "F";
                        break;
                    case 'Unconfigured(good), Spun Up':
                    case 'Unconfigured(good), Spun down':
                        $this->_result[$prog][$uname]['items'][$uname."-".$id]['status'] = "U";
                        break;
                    case 'Hotspare, Spun Up':
                    case 'Hotspare, Spun down':
                        $this->_result[$prog][$uname]['items'][$uname."-".$id]['status'] = "S";
                    }
                    $this->_result[$prog][$uname]['items'][$uname."-".$id]['info'] = $dskstat;
                    if ((count($buffArgs) == 10) && (trim($buffArgs[9]) != 'N/A')) $this->_result[$prog][$uname]['items'][$uname."-".$id]['info'].=" ".trim($buffArgs[9]);
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
                    } elseif (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                       && ($disk !== "") && preg_match('/^\s+Mediasize:\s+(\d+)/', $line, $data)) {
                        $disksinfo[$disk]['capacity'] = trim($data[1]);
                    }
                }
            }
            $lines = preg_split("/\r?\n/", trim($raiddata[0]), -1, PREG_SPLIT_NO_EMPTY);
            $group = "";
            foreach ($lines as $line) {
                if (preg_match("/^\d+\.\s+Name:\s+(.+)/", $line, $data)) {
                    $group = $data[1];
                    if ($controller !== '') $this->_result['graid'][$group]['controller'] = $controller;
                } elseif ($group!=="") {
                    if (preg_match('/^\s+Mediasize:\s+(\d+)/', $line, $data)) {
                        $this->_result['graid'][$group]['capacity'] = trim($data[1]);
                    } elseif (preg_match('/^\s+State:\s+(.+)/', $line, $data)) {
                        $this->_result['graid'][$group]['status'] = trim($data[1]);
                    } elseif (preg_match('/^\s+RAIDLevel:\s+(.+)/', $line, $data)) {
                        $this->_result['graid'][$group]['level'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Components:\s+(\d+)/', $line, $data)) {
                        $this->_result['graid'][$group]['devs'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Label:\s+(.+)/', $line, $data)) {
                        $this->_result['graid'][$group]['name'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Stripesize:\s+(.+)/', $line, $data)) {
                        $this->_result['graid'][$group]['stripe_size'] = trim($data[1]);
                    } elseif (preg_match('/^\s+Subdisks:\s+(.+)/', $line, $data)) {
                        $disks = preg_split('/\s*,\s*/', trim($data[1]), -1, PREG_SPLIT_NO_EMPTY);
                        $nones = 0;
                        $this->_result['graid'][$group]['items'][0]['parentid'] = 0;
                        foreach ($disks as $disk) {
                            if (preg_match("/^(\S+)\s+\(([^\)]+)\)/", $disk, $partition)) {
                                $this->_result['graid'][$group]['items'][$partition[1]]['parentid'] = 1;
                                $this->_result['graid'][$group]['items'][$partition[1]]['type'] = "disk";
                                if ($partition[2]=="ACTIVE") {
                                    if (isset($disksinfo[$partition[1]]["status"])) {
                                        if ($disksinfo[$partition[1]]["status"]!=="ACTIVE") {
                                            $this->_result['graid'][$group]['items'][$partition[1]]['status'] = "W";
                                        } elseif ($disksinfo[$partition[1]]["substatus"]=="ACTIVE") {
                                            $this->_result['graid'][$group]['items'][$partition[1]]['status'] = "ok";
                                        } else {
                                            $this->_result['graid'][$group]['items'][$partition[1]]['status'] = "W";
                                            if (isset($disksinfo[$partition[1]]["percent"])) {
                                                $this->_result['graid'][$group]['action']['name'] = $disksinfo[$partition[1]]["substatus"];
                                                $this->_result['graid'][$group]['action']['percent'] = $disksinfo[$partition[1]]["percent"];
                                            }
                                        }
                                    } else {
                                        $this->_result['graid'][$group]['items'][$partition[1]]['status'] = "ok";
                                        $this->_result['graid'][$group]['items'][$partition[1]]['name'] = $partition[1];
                                    }
                                    $this->_result['graid'][$group]['items'][$partition[1]]['name'] = $partition[1];
                                } elseif ($partition[2]=="NONE") {
                                    $this->_result['graid'][$group]['items']["none".$nones]['status'] = 'E';
                                    $this->_result['graid'][$group]['items']["none".$nones]['name'] = "none".$nones;
                                    $nones++;
                                }
                            }
                        }
                    }
                }
            }
            if (isset($this->_result['graid'][$group]['items'][0]['parentid'])) {
                $name = "";
                if (isset($this->_result['graid'][$group]['name'])) {
                    $name = $this->_result['graid'][$group]['name'];
                }
                if (isset($this->_result['graid'][$group]['level'])) {
                    $name .= " " .$this->_result['graid'][$group]['level'];
                }
                $this->_result['graid'][$group]['items'][0]['name'] = trim($name);
                if (isset($this->_result['graid'][$group]['status'])) {
                      if ($this->_result['graid'][$group]['status']==="OPTIMAL") {
                          $this->_result['graid'][$group]['items'][0]['status'] = "ok";
                      } else {
                          $this->_result['graid'][$group]['items'][0]['status'] = "W";
                          $this->_result['graid'][$group]['items'][0]['info'] = $this->_result['graid'][$group]['status'];
                      }
                } else {
                    $this->_result['graid'][$group]['items'][0]['status'] = "ok";
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
                if (preg_match("/^ +state: (\S+)/m", $raid, $buff)) {
                    $this->_result['zpool'][$group]['status'] = $buff[1];
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
                                $this->_result['zpool'][$group]['items'][$id]['name'] = "";//$fullbuff[0];
                                if (count($fullbuff) > 1) {
                                    $this->_result['zpool'][$group]['items'][$id]['status'] = $fullbuff[1];
                                }
                                $this->_result['zpool'][$group]['items'][$id]['parentid'] = -2;
                                continue;
                            }
                            if ($offset < $rootoffset) { // some errors
                                continue;
                            }

                            $this->_result['zpool'][$group]['items'][$id]['name'] = $fullbuff[0];

                            if (count($fullbuff) > 1) {
                                $this->_result['zpool'][$group]['items'][$id]['status'] = $fullbuff[1];
                            }
                            if (count($fullbuff) > 5) {
                                $this->_result['zpool'][$group]['items'][$id]['info'] = $fullbuff[5];
                            }

                            $indent = ($offset - $rootoffset)/2;
                            if ($indent > $lastindent) {
                                $lastparentids[$indent] = $lastid;
                            }
                            $this->_result['zpool'][$group]['items'][$id]['parentid'] = $lastparentids[$indent];

                            if ($lastparentids[$indent] >= 0) {
                                if (isset($this->_result['zpool'][$group]['items'][$lastparentids[$indent]]['childs'])) {
                                    $this->_result['zpool'][$group]['items'][$lastparentids[$indent]]['childs']++;
                                } else {
                                    $this->_result['zpool'][$group]['items'][$lastparentids[$indent]]['childs'] = 1;
                                }
                            }

                            $lastindent = $indent;
                            $lastid = $id;
                        }
                    }
                    foreach ($this->_result['zpool'][$group]['items'] as $id=>$data) { // type analize
                        if ((!isset($data['childs']) || ($data['childs']<1)) && ($data['parentid']>=0) && !preg_match("/^mirror$|^mirror-|^spare$|^spare-|^replacing$|^replacing-|^raidz[123]$|^raidz[123]-/", $data['name'])) {
                            $this->_result['zpool'][$group]['items'][$id]['type'] = "disk";
                        } elseif (isset($data['childs']) && !preg_match("/^spares$|^mirror$|^mirror-|^spare$|^spare-|^replacing$|^replacing-|^raidz[123]$|^raidz[123]-/", $data['name'])) {
                            if (($data['childs']==1) && ($data['parentid']==-2) && isset($this->_result['zpool'][$group]['items'][$id+1]) && !preg_match("/^mirror$|^mirror-|^spare$|^spare-|^replacing$|^replacing-|^raidz[123]$|^raidz[123]-/", $this->_result['zpool'][$group]['items'][$id+1]['name'])) {
                                $this->_result['zpool'][$group]['items'][$id]['name2'] = "jbod";
                            } elseif ($data['childs']>1) {
                                $this->_result['zpool'][$group]['items'][$id]['name2'] = "stripe";
                            }
                        }
                    }

                    foreach ($this->_result['zpool'][$group]['items'] as $id=>$data) { // size optimize
                        if (($data['parentid']<0) && isset($data['childs']) && ($data['childs']==1) && (!isset($data['name2']) || ($data['name2']!=="jbod"))) {
                            if ($data['parentid']==-2) {
                                unset($this->_result['zpool'][$group]['items'][$id]);
                            } elseif (($data['parentid'] == -1) && !isset($this->_result['zpool'][$group]['items'][$id+1]['type'])) {
                                $this->_result['zpool'][$group]['items'][$id+1]['name2'] = $data['name'];
                                $this->_result['zpool'][$group]['items'][$id+1]['parentid'] = $data['parentid'];
                                unset($this->_result['zpool'][$group]['items'][$id]);
                                foreach ($this->_result['zpool'][$group]['items'] as $id2=>$data2) {
                                    if ($data2['parentid']>$id) {
                                        $this->_result['zpool'][$group]['items'][$id2]['parentid'] = $data2['parentid'] - 1;
                                    }
                                }
                            }
                        }
                    }

                    if (isset($this->_result['zpool'][$group]['items'][0])) {
                        $shift = true;
                    } else {
                        $shift = false;
                    }
                    foreach ($this->_result['zpool'][$group]['items'] as $id=>$data) {
                        // reindex
                        if ($shift) {
                            $this->_result['zpool'][$group]['items'][$id]['parentid']++;
                        }
                        if ($data['parentid']<0) {
                            $this->_result['zpool'][$group]['items'][$id]['parentid'] = 0;
                        }

                         // name append
                        if (isset($data['name2'])) {
                            if (($data['name2']==="cache") || ($data['name2']==="logs")) {
                                $this->_result['zpool'][$group]['items'][$id]['name'] = trim($data['name2']." ".$data['name']);
                            } else {
                                $this->_result['zpool'][$group]['items'][$id]['name'] = trim($data['name']." ".$data['name2']);
                            }
                            unset($this->_result['zpool'][$group]['items'][$id]['name2']);
                        }

                        // status and info normalize
                        if (isset($data['status'])) {
                                switch ($data['status']) {
                                case 'AVAIL':
                                    if (isset($data['info'])) {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                    } else {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status'];
                                    }
                                    $this->_result['zpool'][$group]['items'][$id]['status'] = "S";
                                    break;
                                case 'INUSE':
                                case 'DEGRADED':
                                    if (isset($data['info'])) {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                    } else {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status'];
                                    }
                                    $this->_result['zpool'][$group]['items'][$id]['status'] = "W";
                                    break;
                                case 'UNAVAIL':
                                case 'FAULTED':
                                    if (isset($data['info'])) {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status']." ".$data['info'];
                                    } else {
                                        $this->_result['zpool'][$group]['items'][$id]['info'] = $data['status'];
                                    }
                                    $this->_result['zpool'][$group]['items'][$id]['status'] = "F";
                                    break;
                                default:
                                    $this->_result['zpool'][$group]['items'][$id]['status'] = "ok";
                                }
                        } else {
                            if ($this->_result['zpool'][$group]['items'][$id]['name'] == "spares") {
                                $this->_result['zpool'][$group]['items'][$id]['status'] = "S";
                            } else {
                                $this->_result['zpool'][$group]['items'][$id]['status'] = "ok";
                            }
                        }
                    }
                }
            }
        }
    }

    private function execute_idrac($buffer, $device)
    {
        $snmptablec = array(); //controller table
        $snmptableb = array(); //battery table
        $snmptablev = array(); //virtual disks table
        $snmptablep = array(); //physical disks table

        $buffer = preg_replace('/End of MIB\r?\n/', '', $buffer);
        $buffer = preg_replace('/\s\r?\n([^\.])/', ' $1', $buffer);
        $raiddata = preg_split("/\r?\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
        if (!empty($raiddata)) {
            foreach ($raiddata as $line) {
                if (preg_match('/^(.+) = Hex-STRING:\s(.+)/', $line, $linetmp)) {
                    $hexchars = explode(" ", trim($linetmp[2]));
                    $newstring = "";
                    foreach ($hexchars as $hexchar) {
                        $hexint = hexdec($hexchar);
                        if (($hexint<32) || ($hexint>126)) {
                            $newstring .= ".";
                        } else {
                            $newstring .= chr($hexint);
                        }
                    }
                    if ($newstring!=="") {
                        $line = $linetmp[1]." = STRING: ".$newstring;
                    }
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.1\.1\.2\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablec[$data[1]]['controllerName']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.1\.1\.8\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablec[$data[1]]['controllerFWVersion']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.1\.1\.9\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablec[$data[1]]['controllerCacheSizeInMB']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.1\.1\.37\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablec[$data[1]]['controllerRollUpStatus']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.1\.1\.78\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablec[$data[1]]['controllerFQDD']=trim($data[2], "\"");

                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.15\.1\.4\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptableb[$data[1]]['batteryState']=$data[2];
//                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.15\.1\.6\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
//                    $snmptableb[$data[1]]['batteryComponentStatus']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.15\.1\.20\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptableb[$data[1]]['batteryFQDD']=trim($data[2], "\"");

                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.2\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskName']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.3\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskManufacturer']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.4\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskState']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.6\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskProductID']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.7\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskSerialNo']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.11\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskCapacityInMB']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.21\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskBusType']=$data[2];
//                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.22\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
//                    $snmptablep[$data[1]]['physicalDiskSpareState']=$data[2];
//                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.24\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
//                    $snmptablep[$data[1]]['physicalDiskComponentStatus']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.50\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskOperationalState']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.51\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskProgress']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.130\.4\.1\.54\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablep[$data[1]]['physicalDiskFQDD']=trim($data[2], "\"");

                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.2\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskName']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.4\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskState']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.6\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskSizeInMB']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.10\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskWritePolicy']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.11\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskReadPolicy']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.13\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskLayout']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.14\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskStripeSize']=$data[2];
//                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.20\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
//                    $snmptablev[$data[1]]['virtualDiskComponentStatus']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.23\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskBadBlocksDetected']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.26\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskDiskCachePolicy']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.30\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskOperationalState']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.31\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskProgress']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.674\.10892\.5\.5\.1\.20\.140\.1\.1\.35\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $snmptablev[$data[1]]['virtualDiskFQDD']=trim($data[2], "\"");
                }
            }

            foreach ($snmptablec as $raid_controller) {
                $tablec = array(); //controller result table
                if (isset($raid_controller['controllerRollUpStatus'])) {
                    switch ($raid_controller['controllerRollUpStatus']) {
                    case 1:
                        $tablec['status'] = "W";
                        $tablec['info'] = "Other";
                        break;
                    case 2:
                        $tablec['status'] = "W";
                        $tablec['info'] = "Unknown";
                        break;
                    case 3:
                        $tablec['status'] ="ok";
                        break;
                    case 4:
                        $tablec['status'] ="W";
                        $tablec['info'] ="Non-critical";
                        break;
                    case 5:
                        $tablec['status'] = "F";
                        $tablec['info'] = "Critical";
                        break;
                    case 6:
                        $tablec['status'] = "F";
                        $tablec['info'] = "Non-recoverable";
                    }
                }
                if (isset($raid_controller['controllerName'])) {
                    $tablec['controller'] = $raid_controller['controllerName'];
                }
                if (isset($raid_controller['controllerFWVersion'])) {
                    $tablec['firmware'] = $raid_controller['controllerFWVersion'];
                }
                if (isset($raid_controller['controllerCacheSizeInMB'])) {
                    $tablec['cache_size'] = $raid_controller['controllerCacheSizeInMB'] * 1024 * 1024;
                }
                foreach ($snmptableb as $raid_battery) {
                    if (isset($raid_battery['batteryFQDD'])
                       && isset($raid_controller['controllerFQDD'])
                       && preg_match("/:".$raid_controller['controllerFQDD']."$/", $raid_battery['batteryFQDD'])) {
                        if (isset($raid_battery['batteryState'])) {
                            switch ($raid_battery['batteryState']) {
                            case 1:
                                $tablec['battery'] = "unknown";
                                break;
                            case 2:
                                $tablec['battery'] = "ready";
                                break;
                            case 3:
                                $tablec['battery'] = "failed";
                                break;
                            case 4:
                                $tablec['battery'] = "degraded";
                                break;
                            case 5:
                                $tablec['battery'] = "missing";
                                break;
                            case 6:
                                $tablec['battery'] = "charging";
                                break;
                            case 7:
                                $tablec['battery'] = "bellowThreshold";
                            }
                        }
                        break;
                    }
                }
                foreach ($snmptablep as $raid_physical) {
                    if (isset($raid_physical['physicalDiskFQDD'])
                       && isset($raid_controller['controllerFQDD'])
                       && preg_match("/:".$raid_controller['controllerFQDD']."$/", $raid_physical['physicalDiskFQDD'])) {
                        $devname = $device.'-'.preg_replace('/[a-zA-Z\.]/', '', $raid_controller['controllerFQDD']);
                        $this->_result['idrac'][$devname]['name']=$raid_controller['controllerFQDD'];
                        if (isset($tablec['controller'])) {
                            $this->_result['idrac'][$devname]['controller'] = $tablec['controller'];
                        }
                        if (isset($tablec['firmware'])) {
                            $this->_result['idrac'][$devname]['firmware'] = $tablec['firmware'];
                        }
                        if (isset($tablec['battery'])) {
                            $this->_result['idrac'][$devname]['battery'] = $tablec['battery'];
                        }
                        if (isset($tablec['cache_size'])) {
                            $this->_result['idrac'][$devname]['cache_size'] = $tablec['cache_size'];
                        }
                        if (isset($tablec['info'])) {
                            $this->_result['idrac'][$devname]['status'] = $tablec['info'];
                        } elseif (isset($tablec['status'])) {
                            $this->_result['idrac'][$devname]['status'] = $tablec['status'];
                        }
                        $this->_result['idrac'][$devname]['items'][0]['name']=$raid_controller['controllerFQDD'];
                        $this->_result['idrac'][$devname]['items'][0]['parentid'] = 0;
                        if (isset($tablec['status'])) {
                            $this->_result['idrac'][$devname]['items'][0]['status'] = $tablec['status'];
                            if (isset($tablec['info'])) {
                                $this->_result['idrac'][$devname]['items'][0]['info'] = $tablec['info'];
                            }
                        }
                        $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['name']=$raid_physical['physicalDiskName'];
                        $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['parentid'] = 1;
                        if (preg_match("/^Solid State Disk /", $raid_physical['physicalDiskName'])) {
                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['type'] = "ssd";
                        } else {
                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['type'] = "disk";
                        }

                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                            if (isset($raid_physical['physicalDiskCapacityInMB'])) {
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['capacity'] = $raid_physical['physicalDiskCapacityInMB'] * 1024 * 1024;
                            }

                            $model = "";
                            if (isset($raid_physical['physicalDiskManufacturer'])) {
                                $model = $raid_physical['physicalDiskManufacturer'];
                            }
                            if (isset($raid_physical['physicalDiskProductID'])) {
                                $model .= " ".$raid_physical['physicalDiskProductID'];
                            }
                            if (($model = trim($model)) !== '') {
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['model'] = $model;
                            }
                            if (isset($raid_physical['physicalDiskBusType'])) {
                                switch ($raid_physical['physicalDiskBusType']) {
//                              case 1:
//                                  $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['bus'] = "unknown";
//                                  break;
                                case 2:
                                    $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['bus'] = "SCSI";
                                    break;
                                case 3:
                                    $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['bus'] = "SAS";
                                    break;
                                case 4:
                                    $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['bus'] = "SATA";
                                    break;
                                case 5:
                                    $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['bus'] = "Fibre";
                                }
                            }
                            if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                if (isset($raid_physical['physicalDiskSerialNo'])) {
                                    $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['serial'] = " ".$raid_physical['physicalDiskSerialNo'];
                                }
                            }
                        }
                        if (isset($raid_physical['physicalDiskState'])) {
                            switch ($raid_physical['physicalDiskState']) {
                            case 1:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "unknown";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "ready";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "ok";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "online";
                                if (isset($raid_physical['physicalDiskOperationalState'])) {
                                    switch ($raid_physical['physicalDiskOperationalState']) {
                                    case 1:
                                        break;
                                    case 2:
                                        $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                        if (isset($raid_physical['physicalDiskProgress'])) {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Rebuilding ('.$raid_physical['physicalDiskProgress'].'%)';
                                            $this->_result['idrac'][$devname]['action']['name'] = 'Rebuilding';
                                            $this->_result['idrac'][$devname]['action']['percent'] = $raid_physical['physicalDiskProgress'];
                                        } else {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Rebuilding';
                                        }
                                        break;
                                    case 3:
                                        $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                        if (isset($raid_physical['physicalDiskProgress'])) {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Erasing ('.$raid_physical['physicalDiskProgress'].'%)';
                                            $this->_result['idrac'][$devname]['action']['name'] = 'Erasing';
                                            $this->_result['idrac'][$devname]['action']['percent'] = $raid_physical['physicalDiskProgress'];
                                        } else {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Erasing';
                                        }
                                        break;
                                    case 4:
                                        $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                        if (isset($raid_physical['physicalDiskProgress'])) {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Copying ('.$raid_physical['physicalDiskProgress'].'%)';
                                            $this->_result['idrac'][$devname]['action']['name'] = 'Copying';
                                            $this->_result['idrac'][$devname]['action']['percent'] = $raid_physical['physicalDiskProgress'];
                                        } else {
                                            $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = 'Copying';
                                        }
                                    }
                                }
                                break;
                            case 4:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "W";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "foreign";
                                break;
                            case 5:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "F";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "offline";
                                break;
                            case 6:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "F";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "blocked";
                                break;
                            case 7:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "F";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "failed";
                                break;
                            case 8:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "S";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "non-raid";
                                break;
                            case 9:
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['status'] = "F";
                                $this->_result['idrac'][$devname]['items'][$raid_physical['physicalDiskName']]['info'] = "removed";
                            }
                        }
                    }
                }
                foreach ($snmptablev as $raid_virtual) {
                    if (isset($raid_virtual['virtualDiskFQDD'])
                       && isset($raid_controller['controllerFQDD'])
                       && preg_match("/:".$raid_controller['controllerFQDD']."$/", $raid_virtual['virtualDiskFQDD'])) {
                        $devname = $device.'-'.preg_replace('/[a-zA-Z\.]/', '', $raid_virtual['virtualDiskFQDD']);
                        $this->_result['idrac'][$devname]['name']=$raid_virtual['virtualDiskFQDD'];
                        $this->_result['idrac'][$devname]['items'][0]['name']=$raid_virtual['virtualDiskFQDD'];
                        $this->_result['idrac'][$devname]['items'][0]['parentid'] = 0;
                        if (isset($tablec['controller'])) {
                            $this->_result['idrac'][$devname]['controller'] = $tablec['controller'];
                        }
                        if (isset($tablec['firmware'])) {
                            $this->_result['idrac'][$devname]['firmware'] = $tablec['firmware'];
                        }
                        if (isset($tablec['battery'])) {
                            $this->_result['idrac'][$devname]['battery'] = $tablec['battery'];
                        }
                        if (isset($tablec['cache_size'])) {
                            $this->_result['idrac'][$devname]['cache_size'] = $tablec['cache_size'];
                        }
                        if (isset($raid_virtual['virtualDiskLayout'])) {
                            switch ($raid_virtual['virtualDiskLayout']) {
                            case 1:
                                $this->_result['idrac'][$devname]['level'] = "other";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['level'] = "raid0";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['level'] = "raid1";
                                break;
                            case 4:
                                $this->_result['idrac'][$devname]['level'] = "raid5";
                                break;
                            case 5:
                                $this->_result['idrac'][$devname]['level'] = "raid6";
                                break;
                            case 6:
                                $this->_result['idrac'][$devname]['level'] = "raid10";
                                break;
                            case 7:
                                $this->_result['idrac'][$devname]['level'] = "raid50";
                                break;
                            case 8:
                                $this->_result['idrac'][$devname]['level'] = "raid60";
                                break;
                            case 9:
                                $this->_result['idrac'][$devname]['level'] = "concatraid1";
                                break;
                            case 10:
                                $this->_result['idrac'][$devname]['level'] = "concatraid5";
                                break;
                            default:
                                $this->_result['idrac'][$devname]['level'] = "unknown";
                            }
                            if (isset($this->_result['idrac'][$devname]['level'])) {
                                $this->_result['idrac'][$devname]['items'][0]['name'] = $this->_result['idrac'][$devname]['level'];
                            }
                        }
                        if (isset($raid_virtual['virtualDiskState'])) {
                            switch ($raid_virtual['virtualDiskState']) {
                            case 1:
                                $this->_result['idrac'][$devname]['status'] = "unknown";
                                $this->_result['idrac'][$devname]['items'][0]['status']="W";
                                $this->_result['idrac'][$devname]['items'][0]['info'] = $this->_result['idrac'][$devname]['status'];
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['status'] = "online";
                                $this->_result['idrac'][$devname]['items'][0]['status']="ok";
                                $this->_result['idrac'][$devname]['items'][0]['info'] = $this->_result['idrac'][$devname]['status'];
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['status'] = "failed";
                                $this->_result['idrac'][$devname]['items'][0]['status']="F";
                                $this->_result['idrac'][$devname]['items'][0]['info'] = $this->_result['idrac'][$devname]['status'];
                                break;
                            case 4:
                                $this->_result['idrac'][$devname]['status'] = "degraded";
                                $this->_result['idrac'][$devname]['items'][0]['status']="W";
                                $this->_result['idrac'][$devname]['items'][0]['info'] = $this->_result['idrac'][$devname]['status'];
                            }
                        }
                        if (isset($raid_virtual['virtualDiskOperationalState'])) {
                            switch ($raid_virtual['virtualDiskOperationalState']) {
                            case 1:
                                //$this->_result['idrac'][$devname]['action']['name'] = "notApplicable";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['action']['name'] = "reconstructing";
                                if (isset($raid_virtual['virtualDiskProgress'])) {
                                    $this->_result['idrac'][$devname]['action']['percent'] = $raid_virtual['virtualDiskProgress'];
                                } else {
                                    $this->_result['idrac'][$devname]['action']['percent'] = 0;
                                }
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['action']['name'] = "resyncing";
                                if (isset($raid_virtual['virtualDiskProgress'])) {
                                    $this->_result['idrac'][$devname]['action']['percent'] = $raid_virtual['virtualDiskProgress'];
                                } else {
                                    $this->_result['idrac'][$devname]['action']['percent'] = 0;
                                }
                                break;
                            case 4:
                                $this->_result['idrac'][$devname]['action']['name'] = "initializing";
                                if (isset($raid_virtual['virtualDiskProgress'])) {
                                    $this->_result['idrac'][$devname]['action']['percent'] = $raid_virtual['virtualDiskProgress'];
                                } else {
                                    $this->_result['idrac'][$devname]['action']['percent'] = 0;
                                }
                                break;
                            case 5:
                                $this->_result['idrac'][$devname]['action']['name'] = "backgroundInit";
                                if (isset($raid_virtual['virtualDiskProgress'])) {
                                    $this->_result['idrac'][$devname]['action']['percent'] = $raid_virtual['virtualDiskProgress'];
                                } else {
                                    $this->_result['idrac'][$devname]['action']['percent'] = 0;
                                }
                            }
                        }

                        if (isset($raid_virtual['virtualDiskSizeInMB'])) {
                            $this->_result['idrac'][$devname]['capacity'] = $raid_virtual['virtualDiskSizeInMB'] * 1024 * 1024;
                        }

                        if (isset($raid_virtual['virtualDiskReadPolicy'])) {
                            switch ($raid_virtual['virtualDiskReadPolicy']) {
                            case 1:
                                $this->_result['idrac'][$devname]['readpolicy'] = "noReadAhead";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['readpolicy'] = "readAhead";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['readpolicy'] = "adaptiveReadAhead";
                            }
                        }
                        if (isset($raid_virtual['virtualDiskWritePolicy'])) {
                            switch ($raid_virtual['virtualDiskWritePolicy']) {
                            case 1:
                                $this->_result['idrac'][$devname]['writepolicy'] = "writeThrough";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['writepolicy'] = "writeBack";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['writepolicy'] = "writeBackForce";
                            }
                        }
                        if (isset($raid_virtual['virtualDiskState'])) {
                            switch ($raid_virtual['virtualDiskState']) {
                            case 1:
                                $this->_result['idrac'][$devname]['status'] = "unknown";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['status'] = "online";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['status'] = "failed";
                                break;
                            case 4:
                                $this->_result['idrac'][$devname]['status'] = "degraded";
                            }
                        }
                        if (isset($raid_virtual['virtualDiskDiskCachePolicy'])) {
                            switch ($raid_virtual['virtualDiskDiskCachePolicy']) {
                            case 1:
                                $this->_result['idrac'][$devname]['diskcache'] = "enabled";
                                break;
                            case 2:
                                $this->_result['idrac'][$devname]['diskcache'] = "disabled";
                                break;
                            case 3:
                                $this->_result['idrac'][$devname]['diskcache'] = "default";
                            }
                        }
                        if (isset($raid_virtual['virtualDiskBadBlocksDetected'])) {
                            $this->_result['idrac'][$devname]['bad_blocks'] = $raid_virtual['virtualDiskBadBlocksDetected'];
                        }
                        if (isset($raid_virtual['virtualDiskStripeSize'])) {
                            $virtualDiskStripeSize = $raid_virtual['virtualDiskStripeSize'];
                            if ($virtualDiskStripeSize >= 3) {
                                $this->_result['idrac'][$devname]['stripe_size'] = 512<<($virtualDiskStripeSize - 3);
                            }
                        }

                    }
                }
            }
        }
    }

    private function execute_storcli($buffer, $_perccli = false)
    {

        if ($_perccli === true) {
            $prog = "perccli";
        } else {
            $prog = "storcli";
        }

        if (!empty($buffer)) {
            $raiddata = preg_split("/\n(?=.+\s+:\r?\n===)/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            $carr = array();
            $cnr = -1;
            if (count($raiddata) > 2) foreach ($raiddata as $items) {
                if (preg_match("/^(.+)\s+:\r?\n===+\r?\n([\s\S]+)$/", $items, $buff)) {
                    if ($buff[1] === "Basics") {
                        $cnr++;
                    }
                    if ($cnr >= 0) {
                        $stage = 0;
                        $lines = preg_split('/\r?\n/', $buff[2], -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($lines as $line) {
                            if (($line = trim($line)) !== '') {
                                $parts = preg_split("/ = /", $line);
                                switch ($stage) {
                                case 0:
                                    if (count($parts) == 2) {
                                        $carr[$cnr][$buff[1]][trim($parts[0])] = trim($parts[1]);
                                    } elseif (preg_match("/^---/", $line)) {
                                        $stage = 1;
                                    }
                                    break;
                                case 1:
                                    $args = preg_split("/ /" ,preg_replace("/ RetentionTime /", " Retention Hours ", preg_replace("/ Size /", " Size Unit ", $line)), -1, PREG_SPLIT_NO_EMPTY);
                                    $stage = 2;
                                    break;
                                case 2:
                                    if (preg_match("/^---/", $line)) {
                                        $stage = 3;
                                    }
                                    break;
                                case 3:
                                    if (preg_match("/^---/", $line)) {
                                        $stage = 4;
                                    } else {
                                        $values = preg_split("/ /" ,$line, -1, PREG_SPLIT_NO_EMPTY);
                                        if (count($values) == count($args)-1) { //no Name
                                            $values[] = "";
                                        }
                                        $diffc = count($values) - count($args);
                                        if (($diffc >= 0) && (count($values) > 6)) {
                                            $valarr = array();
                                            for ($vnr = 0; $vnr < count($args); $vnr++) {
                                                if (($diffc == 0) || (($args[$vnr] !== "Name") && ($args[$vnr] !== "Model"))) {
                                                    $valarr[$args[$vnr]] = $values[$vnr];
                                                } else {
                                                    $valarr[$args[$vnr]] = $values[$vnr];
                                                    break;
                                                }
                                            }
                                            if (($diffc > 0) && ($vnr < count($args))) for ($enr = count($values)-1; $enr >= 0; $enr--) {
                                                if (($args[$enr-$diffc] !== "Name") && ($args[$enr-$diffc] !== "Model")) {
                                                    $valarr[$args[$enr-$diffc]] = $values[$enr];
                                                } else {
                                                    break;
                                                }
                                            }
                                            if (($diffc > 0) && ($vnr < $enr)) {
                                                for ($xnr = $vnr + 1; $xnr <= $enr; $xnr++) {
                                                    $valarr[$args[$vnr]] .= " ".$values[$xnr];
                                                }
                                            }
                                            $carr[$cnr][$buff[1]]['values'][] = $valarr;
                                        } else {
                                            $stage = 4;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($carr as $controller) if (isset($controller["Basics"]["Controller"])
               && (($cnr = $controller["Basics"]["Controller"]) >= 0)) {
                $dg = -1;
                if (isset($controller["TOPOLOGY"]["values"])) foreach ($controller["TOPOLOGY"]["values"] as $topol) {
                    if (isset($topol["Arr"]) && ($topol["Arr"] !== "-" )) {
                        if ($topol["DG"] != $dg) {
                            $dg = $topol["DG"];
                            $uname = 'c'.$cnr.'u'.$dg;
                            if (isset($controller["Basics"]["Model"])) $this->_result[$prog][$uname]['controller'] = $controller["Basics"]["Model"];
                            if (isset($controller["Version"]["Firmware Package Build"])) $this->_result[$prog][$uname]['firmware'] = $controller["Version"]["Firmware Package Build"];
                            if (isset($controller["Status"]["Controller Status"])) {
                                $this->_result[$prog][$uname]['status'] = $controller["Status"]["Controller Status"];
                            } else {
                                $this->_result[$prog][$uname]['status'] = 'Unknown';
                            }
                            if (isset($controller["BBU_Info"]["values"][0])) {
                                if (isset($controller["BBU_Info"]["values"][0]["State"])) {
                                    if (($state = $controller["BBU_Info"]["values"][0]["State"]) === "Optimal") {
                                        $this->_result[$prog][$uname]['battery'] = "good";
                                    } else {
                                        $this->_result[$prog][$uname]['battery'] = $state;
                                    }
                                }
                                if (isset($controller["BBU_Info"]["values"][0]["Temp"]) && preg_match("/^(\d+)C$/" , $controller["BBU_Info"]["values"][0]["Temp"], $batt)) {
                                    $this->_result[$prog][$uname]['batttemp'] = $batt[1];
                                }
                            }
                            if (isset($controller["Capabilities"]["RAID Level Supported"])) $this->_result[$prog][$uname]['supported'] = $controller["Capabilities"]["RAID Level Supported"];
                            if (isset($controller["HwCfg"]["ROC temperature(Degree Celsius)"])) $this->_result[$prog][$uname]['temperature'] = $controller["HwCfg"]["ROC temperature(Degree Celsius)"];
                            if (isset($controller["HwCfg"]["On Board Memory Size"]) && preg_match("/^(\d+)(\S+)$/", $controller["HwCfg"]["On Board Memory Size"], $value)) {
                                switch ($value[2]) {
                                case 'B':
                                    $this->_result[$prog][$uname]['cache_size'] = $value[1];
                                    break;
                                case 'KB':
                                    $this->_result[$prog][$uname]['cache_size'] = 1024*$value[1];
                                    break;
                                case 'MB':
                                    $this->_result[$prog][$uname]['cache_size'] = 1024*1024*$value[1];
                                    break;
                                case 'GB':
                                    $this->_result[$prog][$uname]['cache_size'] = 1024*1024*1024*$value[1];
                                    break;
                                case 'TB':
                                    $this->_result[$prog][$uname]['cache_size'] = 1024*1024*1024*1024*$value[1];
                                    break;
                                case 'PB':
                                    $this->_result[$prog][$uname]['cache_size'] = 1024*1024*1024*1024*1024*$value[1];
                                }
                            }
                            if (isset($topol["Size"]) && isset($topol["Unit"])) {
                                switch ($topol["Unit"]) {
                                case 'B':
                                    $this->_result[$prog][$uname]['capacity'] = $topol["Size"];
                                    break;
                                case 'KB':
                                    $this->_result[$prog][$uname]['capacity'] = 1024*$topol["Size"];
                                    break;
                                case 'MB':
                                    $this->_result[$prog][$uname]['capacity'] = 1024*1024*$topol["Size"];
                                    break;
                                    case 'GB':
                                    $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*$topol["Size"];
                                    break;
                                case 'TB':
                                    $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*1024*$topol["Size"];
                                    break;
                                case 'PB':
                                    $this->_result[$prog][$uname]['capacity'] = 1024*1024*1024*1024*1024*$topol["Size"];
                                }
                            }
                            if (isset($topol["PDC"])) {
                                switch ($topol["PDC"]) {
                                case 'dflt':
                                    $this->_result[$prog][$uname]['diskcache'] = "default";
                                    break;
                                default:
                                    $this->_result[$prog][$uname]['diskcache'] = strtolower($topol["PDC"]);
                                }
                            }
                            if (isset($controller["VD LIST"]["values"])) foreach ($controller["VD LIST"]["values"] as $vdlist) {
                                if (isset($vdlist["DG/VD"])) {
                                    if ($vdlist["DG/VD"] === $dg."/".$dg) {
                                        if (isset($vdlist["TYPE"])) {
                                            $this->_result[$prog][$uname]['items'][-1]['parentid'] = 0;
                                            $this->_result[$prog][$uname]['level'] = $vdlist["TYPE"];
                                            if (isset($vdlist["Name"]) && (trim($vdlist["Name"]) !== "")) {
                                                $this->_result[$prog][$uname]['items'][-1]['name'] = trim($vdlist["Name"]);
                                            } else {
                                                $this->_result[$prog][$uname]['items'][-1]['name'] = $vdlist["TYPE"];
                                            }
                                            if (isset($vdlist["State"])) {
                                                switch ($vdlist["State"]) {
                                                case 'Rec':
                                                    $this->_result[$prog][$uname]['status'] = "Recovery";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "W";
                                                    break;
                                                case 'OfLn':
                                                    $this->_result[$prog][$uname]['status'] = "OffLine";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "F";
                                                    break;
                                                case 'Pdgd':
                                                    $this->_result[$prog][$uname]['status'] = "Partially Degraded";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "W";
                                                    break;
                                                case 'Dgrd':
                                                    $this->_result[$prog][$uname]['status'] = "Degraded";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "W";
                                                    break;
                                                case 'Optl':
                                                    $this->_result[$prog][$uname]['status'] = "Optimal";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "ok";
                                                    break;
                                                default:
                                                    $this->_result[$prog][$uname]['status'] = "Unknown";
                                                    $this->_result[$prog][$uname]['items'][-1]['status'] = "F";
                                                }
                                            }
                                            if (isset($vdlist["Cache"])) {
                                                $ctype = $vdlist["Cache"];
                                                if (preg_match("/^NR/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "noReadAhead";
                                                elseif (preg_match("/^R/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "readAhead";
                                                elseif (preg_match("/^AR/", $ctype)) $this->_result[$prog][$uname]['readpolicy'] = "adaptiveReadAhead";
                                                if (preg_match("/WT[DC]$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeThrough";
                                                elseif (preg_match("/WB[DC]$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeBack";
                                                elseif (preg_match("/FWB[DC]$/", $ctype)) $this->_result[$prog][$uname]['writepolicy'] = "writeBackForce";
                                            }
                                        }
                                        break;
                                    }
                                } else {
                                        break;
                                }
                            }
                        } elseif (($dg >= 0) && isset($topol["Row"]) && ($topol["Row"] !== '-')
                           /*&& isset($topol["DID"]) && ($topol["DID"] !== '-'))*/) {
                            $uname = 'c'.$cnr.'u'.$dg;
                            $this->_result[$prog][$uname]['items'][$topol["DID"]]['parentid'] = 1;
                            $this->_result[$prog][$uname]['items'][$topol["DID"]]['name'] = $uname.'p'.($topol["Row"]);
                            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS && isset($topol["Size"]) && isset($topol["Unit"])) {
                                switch ($topol["Unit"]) {
                                case 'B':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = $topol["Size"];
                                    break;
                                case 'KB':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = 1024*$topol["Size"];
                                    break;
                                case 'MB':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = 1024*1024*$topol["Size"];
                                    break;
                                case 'GB':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = 1024*1024*1024*$topol["Size"];
                                    break;
                                case 'TB':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = 1024*1024*1024*1024*$topol["Size"];
                                    break;
                                case 'PB':
                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['capacity'] = 1024*1024*1024*1024*1024*$topol["Size"];
                                }
                            }
                            if (isset($topol["DID"])) {
                                if ($topol["DID"] === '-') {
                                    if (isset($topol["State"]) && ($topol["State"] === "Msng")) {
                                        $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Missing";
                                        $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "E";
                                        $this->_result[$prog][$uname]['items'][$topol["DID"]]['type'] = "disk";
                                     }
                                } elseif (isset($controller["PD LIST"]["values"])) foreach ($controller["PD LIST"]["values"] as $pdlist) {
                                    if (isset($pdlist["DID"])) {
                                        if ($pdlist["DID"] === $topol["DID"]) {
                                            if (isset($pdlist["State"])) {
                                                switch ($pdlist["State"]) {
                                                case 'DHS':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Dedicated Hot Spare";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "S";
                                                    break;
                                                case 'UGood':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Unconfigured Good";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "U";
                                                    break;
                                                case 'GHS':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Global Hotspare";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "S";
                                                    break;
                                                case 'UBad':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Unconfigured Bad";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "F";
                                                    break;
                                                case 'Onln':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Online";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "ok";
                                                    break;
                                                case 'Offln':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Offline";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "F";
                                                    break;
                                                default:
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] = "Unknown";
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['status'] = "F";
                                                }
                                                if (isset($pdlist["Sp"])) {
                                                    switch ($pdlist["Sp"]) {
                                                    case 'U':
                                                        $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] .= ", Spun Up";
                                                        break;
                                                    case 'D':
                                                       $this->_result[$prog][$uname]['items'][$topol["DID"]]['info'] .= ", Spun Down";
                                                    }
                                                }
                                            }
                                            if (isset($pdlist["Med"])) {
                                                switch ($pdlist["Med"]) {
                                                case 'HDD':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['type'] = "disk";
                                                    break;
                                                case 'SSD':
                                                    $this->_result[$prog][$uname]['items'][$topol["DID"]]['type'] = "ssd";
                                                }
                                            }
                                            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                                if (isset($pdlist["Model"])) $this->_result[$prog][$uname]['items'][$topol["DID"]]['model'] = $pdlist["Model"];
                                                if (isset($pdlist["Intf"])) $this->_result[$prog][$uname]['items'][$topol["DID"]]['bus'] = $pdlist["Intf"];
                                            }
                                            break;
                                        }
                                    } else {
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                if (isset($controller["PD LIST"]["values"])) foreach ($controller["PD LIST"]["values"] as $pdlist) {
                    if (isset($pdlist["DG"]) && preg_match("/\D/", $pdlist["DG"])) {
                        if (isset($pdlist["State"]) && isset($pdlist["DID"])) {
                            $cname = '';
                            switch ($pdlist["State"]) {
                            case 'DHS':
                                $cname = 'c'.$cnr.'-hotspare';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "S";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Dedicated Hot Spare";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "S";
                                break;
                            case 'UGood':
                                $cname = 'c'.$cnr.'-unconfigured';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "U";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Unconfigured Good";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "U";
                                $this->_result[$prog][$cname]['status'] = "Unconfigured";
                                break;
                            case 'GHS':
                                $cname = 'c'.$cnr.'-hotspare';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "S";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Global Hotspare";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "S";
                                $this->_result[$prog][$cname]['status'] = "Hotspare";
                                break;
                            case 'UBad':
                                $cname = 'c'.$cnr.'-unconfigured';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "U";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Unconfigured Bad";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "F";
                                $this->_result[$prog][$cname]['status'] = "Unconfigured";
                                break;
                          /*  case 'Onln':
                                $cname = 'c'.$cnr.'-online';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "ok";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Online";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "ok";
                                $this->_result[$prog][$cname]['status'] = "Online";
                                break;*/
                            case 'Offln':
                                $cname = 'c'.$cnr.'-offine';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "F";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Offline";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "F";
                                $this->_result[$prog][$cname]['status'] = "Offline";
                                break;
                            case 'JBOD':
                                $cname = 'c'.$cnr.'-jbod';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "ok";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "JBOD";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "ok";
                                $this->_result[$prog][$cname]['level'] = "JBOD";
                                $this->_result[$prog][$cname]['status'] = "JBOD";
                                break;
                            default:
                                $cname = 'c'.$cnr.'-unknown';
                                $this->_result[$prog][$cname]['items'][-1]['status'] = "F";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] = "Unknown";
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['status'] = "F";
                                $this->_result[$prog][$cname]['status'] = "Unknown";
                            }
                            if ($cname !== '') {
                                $this->_result[$prog][$cname]['items'][-1]['parentid'] = 0;
                                $this->_result[$prog][$cname]['items'][-1]['name'] = $this->_result[$prog][$cname]['status'];
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['parentid'] = 1;
                                $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['name'] = 'c'.$cnr.'p'.$pdlist["DID"];
                                if (isset($pdlist["Med"])) {
                                    switch ($pdlist["Med"]) {
                                    case 'HDD':
                                        $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['type'] = "disk";
                                        break;
                                    case 'SSD':
                                        $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['type'] = "ssd";
                                    }
                                }
                                if (isset($pdlist["Sp"])) {
                                    switch ($pdlist["Sp"]) {
                                    case 'U':
                                        $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] .= ", Spun Up";
                                        break;
                                    case 'D':
                                        $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['info'] .= ", Spun Down";
                                    }
                                }
                                if (isset($controller["Basics"]["Model"])) $this->_result[$prog][$cname]['controller'] = $controller["Basics"]["Model"];
                                if (isset($controller["Version"]["Firmware Package Build"])) $this->_result[$prog][$cname]['firmware'] = $controller["Version"]["Firmware Package Build"];
                                if (isset($controller["Status"]["Controller Status"])) {
                                    $this->_result[$prog][$cname]['status'] = $controller["Status"]["Controller Status"];
                                } else {
                                    $this->_result[$prog][$cname]['status'] = 'Unknown';
                                }
                                if (isset($controller["BBU_Info"]["values"][0])) {
                                    if (isset($controller["BBU_Info"]["values"][0]["State"])) {
                                       if (($state = $controller["BBU_Info"]["values"][0]["State"]) === "Optimal") {
                                            $this->_result[$prog][$cname]['battery'] = "good";
                                        } else {
                                            $this->_result[$prog][$cname]['battery'] = $state;
                                        }
                                    }
                                    if (isset($controller["BBU_Info"]["values"][0]["Temp"]) && preg_match("/^(\d+)C$/" , $controller["BBU_Info"]["values"][0]["Temp"], $batt)) {
                                        $this->_result[$prog][$cname]['batttemp'] = $batt[1];
                                    }
                                }
                                if (isset($controller["Capabilities"]["RAID Level Supported"])) $this->_result[$prog][$cname]['supported'] = $controller["Capabilities"]["RAID Level Supported"];
                                if (isset($controller["HwCfg"]["On Board Memory Size"]) && preg_match("/^(\d+)(\S+)$/", $controller["HwCfg"]["On Board Memory Size"], $value)) {
                                    switch ($value[2]) {
                                    case 'B':
                                        $this->_result[$prog][$cname]['cache_size'] = $value[1];
                                        break;
                                    case 'KB':
                                        $this->_result[$prog][$cname]['cache_size'] = 1024*$value[1];
                                        break;
                                    case 'MB':
                                        $this->_result[$prog][$cname]['cache_size'] = 1024*1024*$value[1];
                                        break;
                                    case 'GB':
                                        $this->_result[$prog][$cname]['cache_size'] = 1024*1024*1024*$value[1];
                                        break;
                                    case 'TB':
                                        $this->_result[$prog][$cname]['cache_size'] = 1024*1024*1024*1024*$value[1];
                                        break;
                                    case 'PB':
                                        $this->_result[$prog][$cname]['cache_size'] = 1024*1024*1024*1024*1024*$value[1];
                                    }
                                }

                                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                    if (isset($pdlist["Size"]) && isset($pdlist["Unit"])) {
                                        switch ($pdlist["Unit"]) {
                                        case 'B':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = $pdlist["Size"];
                                            break;
                                        case 'KB':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = 1024*$pdlist["Size"];
                                            break;
                                        case 'MB':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = 1024*1024*$pdlist["Size"];
                                            break;
                                        case 'GB':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = 1024*1024*1024*$pdlist["Size"];
                                            break;
                                        case 'TB':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = 1024*1024*1024*1024*$pdlist["Size"];
                                            break;
                                        case 'PB':
                                            $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['capacity'] = 1024*1024*1024*1024*1024*$pdlist["Size"];
                                        }
                                    }
                                    if (isset($pdlist["Model"])) $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['model'] = $pdlist["Model"];
                                    if (isset($pdlist["Intf"])) $this->_result[$prog][$cname]['items'][$pdlist["DID"]]['bus'] = $pdlist["Intf"];
                                }
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
            foreach ($this->prog_items as $item) if (isset($this->_filecontent[$item])) {
                if ($item !== 'idrac') {
                    if ((($buffer = $this->_filecontent[$item]) !== null) && (($buffer = trim($buffer)) != "")) {
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
                        case 'megaclisas-status':
                            $this->execute_status($buffer, false);
                            break;
                        case '3ware-status':
                            $this->execute_status($buffer, true);
                            break;
                        case 'graid':
                            $this->execute_graid($buffer);
                            break;
                        case 'zpool':
                            $this->execute_zpool($buffer);
                            break;
                        case 'storcli':
                            $this->execute_storcli($buffer, false);
                            break;
                        case 'perccli':
                            $this->execute_storcli($buffer, true);
                        }
                    }
                } else {
                    if (is_array($this->_filecontent[$item])) {
                        foreach ($this->_filecontent[$item] as $device=>$buffer) if (($buffer = trim($buffer)) != "") {
                            $this->execute_idrac($buffer, /*'idrac-'.*/$device);
                        }
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

        foreach ($this->prog_items as $item) if (isset($this->_result[$item])) {
            foreach ($this->_result[$item] as $key=>$device) {
                if (!in_array($key, $hideRaids, true)) {
                    $dev = $this->xml->addChild("Raid");
                    $dev->addAttribute("Device_Name", $key);
                    $dev->addAttribute("Program", $item);
                    if (isset($device['level'])) $dev->addAttribute("Level", strtolower($device["level"]));
                    $dev->addAttribute("Status", strtolower($device["status"]));
                    if (isset($device['name'])) $dev->addAttribute("Name", $device["name"]);
                    if (isset($device['capacity'])) $dev->addAttribute("Capacity", $device["capacity"]);
                    if (isset($device['stride'])) $dev->addAttribute("Stride", $device["stride"]);
                    if (isset($device['subsets'])) $dev->addAttribute("Subsets", $device["subsets"]);
                    if (isset($device['devs'])) $dev->addAttribute("Devs", $device["devs"]);
                    if (isset($device['spares'])) $dev->addAttribute("Spares", $device["spares"]);

                    if (isset($device['chunk_size'])) $dev->addAttribute("Chunk_Size", $device["chunk_size"]);
                    if (isset($device['stripe_size'])) $dev->addAttribute("Stripe_Size", $device["stripe_size"]);
                    if (isset($device['pers_superblock'])) $dev->addAttribute("Persistend_Superblock", $device["pers_superblock"]);
                    if (isset($device['algorithm'])) $dev->addAttribute("Algorithm", $device["algorithm"]);
                    if (isset($device['registered'])) $dev->addAttribute("Disks_Registered", $device["registered"]);
                    if (isset($device['active'])) $dev->addAttribute("Disks_Active", $device["active"]);
                    if (isset($device['controller'])) $dev->addAttribute("Controller", $device["controller"]);
                    if (isset($device['firmware'])) $dev->addAttribute("Firmware", $device["firmware"]);
                    if (isset($device['temperature'])) $dev->addAttribute("Temperature", $device["temperature"]);
                    if (isset($device['battery'])) $dev->addAttribute("Battery", $device["battery"]);
                    if (isset($device['battvolt'])) $dev->addAttribute("Batt_Volt", $device["battvolt"]);
                    if (isset($device['batttemp'])) $dev->addAttribute("Batt_Temp", $device["batttemp"]);
                    if (isset($device['supported'])) $dev->addAttribute("Supported", $device["supported"]);
                    if (isset($device['readpolicy'])) $dev->addAttribute("ReadPolicy", $device["readpolicy"]);
                    if (isset($device['writepolicy'])) $dev->addAttribute("WritePolicy", $device["writepolicy"]);
                    if (isset($device['cache_size'])) $dev->addAttribute("Cache_Size", $device["cache_size"]);
                    if (isset($device['diskcache'])) $dev->addAttribute("DiskCache", $device["diskcache"]);
                    if (isset($device['bad_blocks'])) $dev->addAttribute("Bad_Blocks", $device["bad_blocks"]);

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
                            if (isset($disk['status'])) $disktemp->addAttribute("Status", $disk['status']);
                            //} else {
                            //    $disktemp->addAttribute("Status", "W");
                            //}
                            if (isset($disk['info'])) $disktemp->addAttribute("Info", $disk['info']);
                            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                if (isset($disk['bus'])) $disktemp->addAttribute("Bus", $disk['bus']);
                                if (isset($disk['capacity'])) $disktemp->addAttribute("Capacity", $disk['capacity']);
                                if (isset($disk['model'])) $disktemp->addAttribute("Model", $disk['model']);
                                if (isset($disk['temperature'])) $disktemp->addAttribute("Temperature", $disk['temperature']);
                                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                    if (isset($disk['serial'])) $disktemp->addAttribute("Serial", $disk['serial']);
                                }
                            }
                        }
                    }
                }
            }
            $this->_result[$item] = array(); // clear preventing duplicate items
        }

        return $this->xml->getSimpleXmlElement();
    }
}
