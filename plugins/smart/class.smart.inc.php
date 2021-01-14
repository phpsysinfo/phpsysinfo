<?php
/**
 * SMART plugin, which displays all SMART informations available
 *
 * @category  PHP
 * @package   PSI_Plugin_SMART
 * @author    Antoine Bertin <diaoulael@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SMART extends PSI_Plugin
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

    /**
     * variable, which holds the events of disks
     * @var array
     */
    private $_event = array();

    /**
     * variable, which holds PSI_PLUGIN_SMART_IDS well formated datas
     * @var array
     */
    private $_ids = array();

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc target encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        switch (strtolower(PSI_PLUGIN_SMART_ACCESS)) {
            case 'wmi':
            case 'command':
            case 'data':
                if (defined('PSI_PLUGIN_SMART_DEVICES') && is_string(PSI_PLUGIN_SMART_DEVICES)) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SMART_DEVICES)) {
                        $disks = eval(PSI_PLUGIN_SMART_DEVICES);
                    } else {
                        $disks = array(PSI_PLUGIN_SMART_DEVICES);
                    }
                    if (defined('PSI_PLUGIN_SMART_IDS') && is_string(PSI_PLUGIN_SMART_IDS)) {
                        if (preg_match(ARRAY_EXP, PSI_PLUGIN_SMART_IDS)) {
                            $fullIds = eval(PSI_PLUGIN_SMART_IDS);
                        } else {
                            $fullIds = array(PSI_PLUGIN_SMART_IDS);
                        }
                        foreach ($fullIds as $fullId) {
                            $arrFullId = preg_split('/-/', $fullId);
                            $this->_ids[intval($arrFullId[0])] = strtolower($arrFullId[1]);
                            if (!empty($arrFullId[2]))
                                $this->_ids[intval($arrFullId[2])] = "#replace-".intval($arrFullId[0]);
                        }
                    }
                }
                break;
            default:
                $this->global_error->addConfigError("__construct()", "[smart] ACCESS");
                break;
        }

        switch (strtolower(PSI_PLUGIN_SMART_ACCESS)) {
            case 'wmi':
                if ((PSI_OS == 'WINNT') || defined('PSI_EMU_HOSTNAME')) {
                    if ((PSI_OS == 'WINNT') && !defined('PSI_EMU_HOSTNAME') && !CommonFunctions::isAdmin()) {
                        $this->global_error->addError("SMART WMI mode error", "Mode allowed for WinNT systems, with administrator privileges (run as administrator)");
                    } else {
                        $asd_wmi = null;
                        try {
                            $wmi = CommonFunctions::initWMI('root\wmi');
                            $asd_wmi = CommonFunctions::getWMI($wmi, 'MSStorageDriver_ATAPISmartData', array('VendorSpecific'));
                        } catch (Exception $e) {
                        }
                        foreach ($asd_wmi as $_nr=>$asd) {
                            $_name = "/dev/sd".chr(97+$_nr);
                            if (array_search($_name, $disks) !== false) {
                                $this->_filecontent[$_name] = "\nVendor Specific SMART Attributes with Thresholds\n";
                                $this->_filecontent[$_name] .= "ID# _ATTRIBUTE_NAME_ FLAG VALUE WORST RAW_VALUE\n";
                                $asdvs = $asd['VendorSpecific'];
                                for ($c = 2; $c < count($asdvs); $c += 12) {
                                    //Attribute values 0x00, 0xff are invalid
                                    $id = $asdvs[$c];
                                    if (($id != 0) && ($id != 255)) {
                                        switch ($id) {
                                            case 3:
                                                //raw16(avg16)
                                                $this->_filecontent[$_name] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                                break;
                                            case 5:
                                            case 196:
                                                //raw16(raw16)
                                                $this->_filecontent[$_name] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                                break;
                                            case 9:
                                            case 240:
                                                //raw24(raw8)
                                                $this->_filecontent[$_name] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6]+65536*$asdvs[$c+7])."\n";
                                                break;
                                            case 190:
                                            case 194:
                                                //tempminmax
                                                $this->_filecontent[$_name] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                                break;
                                            default:
                                                //raw48
                                                $this->_filecontent[$_name] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6]+65536*$asdvs[$c+7]+16777216*$asdvs[$c+8])."\n";
                                                break;
                                        }
                                    }
                                }
                                $this->_filecontent[$_name] .= "SMART Error Log Version";
                            }
                        }
                    }
                }
                break;
            case 'command':
                if (!defined('PSI_EMU_HOSTNAME')) foreach ($disks as $disk) {
                    if (trim($disk) != "") {
                        $diskdev = "";
                        if (preg_match("/\s*\(([^\(\(]*)\)\s*(.*)/", $disk, $devdisk)) {
                            $diskname = trim($devdisk[2]);
                            if (trim($devdisk[1]) != "") {
                                $diskdev = "--device ".preg_replace('/\./', ',', trim($devdisk[1]));
                            }
                        } else {
                            $diskname = trim($disk);
                        }
                        $buffer = "";
                        if (trim($diskname != "") && (CommonFunctions::executeProgram('smartctl', '--all'.' '.$diskdev.' '.$diskname, $buffer, PSI_DEBUG))) {
                            $this->_filecontent[trim($disk)] = $buffer;
                        }
                    }
                }
                break;
            case 'data':
                $dn=0;
                if (!defined('PSI_EMU_HOSTNAME')) foreach ($disks as $disk) {
                    $buffer="";
                    if (CommonFunctions::rfts(PSI_APP_ROOT."/data/smart{$dn}.txt", $buffer) && !empty($buffer)) {
                        if (preg_match("/^.+\n.{(.+)}/", $buffer, $out)) { //wmic format
                            $line = trim(preg_replace('/[\x00-\x09\x0b-\x1F]/', '', $out[1]));
                            $this->_filecontent[$disk] = "\nVendor Specific SMART Attributes with Thresholds\n";
                            $this->_filecontent[$disk] .= "ID# _ATTRIBUTE_NAME_ FLAG VALUE WORST RAW_VALUE\n";
                            $asdvs = preg_split('/\s*,\s*/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
                            for ($c = 2; $c < count($asdvs); $c += 12) {
                                //Attribute values 0x00, 0xff are invalid
                                $id = $asdvs[$c];
                                if (($id != 0) && ($id != 255)) {
                                    switch ($id) {
                                        case 3:
                                            //raw16(avg16)
                                            $this->_filecontent[$disk] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                            break;
                                        case 5:
                                        case 196:
                                            //raw16(raw16)
                                            $this->_filecontent[$disk] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                            break;
                                        case 9:
                                        case 240:
                                            //raw24(raw8)
                                            $this->_filecontent[$disk] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6]+65536*$asdvs[$c+7])."\n";
                                            break;
                                        case 190:
                                        case 194:
                                            //tempminmax
                                            $this->_filecontent[$disk] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6])."\n";
                                            break;
                                        default:
                                            //raw48
                                            $this->_filecontent[$disk] .= $id." ID".$id." 0x".substr("0".dechex($asdvs[$c+2]),-2).substr("0".dechex($asdvs[$c+1]),-2)." ".substr("00".$asdvs[$c+3],-3)." ".substr("00".$asdvs[$c+4],-3)." ".($asdvs[$c+5]+256*$asdvs[$c+6]+65536*$asdvs[$c+7]+16777216*$asdvs[$c+8])."\n";
                                            break;
                                    }
                                }
                            }
                            $this->_filecontent[$disk] .= "SMART Error Log Version";
                        } else {
                            $this->_filecontent[$disk] = $buffer;
                        }
                    }
                    $dn++;
                }
                break;
        }
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array
     *
     * @return void
     */
    public function execute()
    {
        if (empty($this->_filecontent) || empty($this->_ids)) {
            return;
        }
        foreach ($this->_filecontent as $disk=>$result) {
            // set the start and end offset in the result string at the beginning and end respectively
            // just in case we don't find the two strings, so that it still works as expected.
            $startIndex = 0;
            $endIndex = 0;
            $vendorInfos = "";

            // locate the beginning string offset for the attributes
            if (preg_match('/(Vendor Specific SMART Attributes with Thresholds)/', $result, $matches, PREG_OFFSET_CAPTURE))
               $startIndex = $matches[0][1];

            // locate the end string offset for the attributes, this is usually right before string "SMART Error Log Version" or "SMART Error Log not supported" or "Error SMART Error Log Read failed" (hopefully every output has it!) 
            if (preg_match('/(SMART Error Log Version)|(SMART Error Log not supported)|(Error SMART Error Log Read failed)/', $result, $matches, PREG_OFFSET_CAPTURE))
               $endIndex = $matches[0][1];

            if ($startIndex && $endIndex && ($endIndex>$startIndex))
                 $vendorInfos = preg_split("/\n/", substr($result, $startIndex, $endIndex - $startIndex));

            if (!empty($vendorInfos)) {
                if (preg_match('/\nSMART overall-health self-assessment test result\: ([^!\n]+)/', $result, $tmpbuf)) {
                    $event=trim($tmpbuf[1]);
                    if (!empty($event) && ($event!=='PASSED')) {
                        $this->_event[$disk] = $event;
                    }
                }

                $i = 0; // Line number
                if (preg_match('/\nATA Error Count\: (\d+)/', $result, $tmpbuf)) {
                    $this->_result[$disk][$i]['id'] = 0;
                    $this->_result[$disk][$i]['attribute_name'] = "ATA_Error_Count";
                    $this->_result[$disk][$i]['raw_value'] = $tmpbuf[1];
                    $i++;
                } elseif (preg_match('/\nNo Errors Logged/', $result, $tmpbuf)) {
                    $this->_result[$disk][$i]['id'] = 0;
                    $this->_result[$disk][$i]['attribute_name'] = "ATA_Error_Count";
                    $this->_result[$disk][$i]['raw_value'] = 0;
                    $i++;
                }

                $labels = preg_split('/\s+/', $vendorInfos[1]);
                foreach ($labels as $k=>$v) {
                    $labels[$k] = str_replace('#', '', strtolower($v));
                }
                foreach ($vendorInfos as $line) if (preg_match('/^\s*((\d+)|(id))\s/', $line)) {
                    $line = preg_replace('/^\s+/', '', $line);
                    $values = preg_split('/\s+/', $line);
                    if (count($values) > count($labels)) {
                        $values = array_slice($values, 0, count($labels), true);
                    }
                    $j = 0;
                    $found = 0;
                    foreach ($values as $value) {
                        if ((in_array($value, array_keys($this->_ids)) && $labels[$j] == 'id')) {
                          $arrFullVa = preg_split('/-/', $this->_ids[$value]);
                          if (($arrFullVa[0]=="#replace") && !empty($arrFullVa[1])) {
                              $value=$arrFullVa[1];
                          }
                        }
                        if (in_array($value, array_keys($this->_ids)) && ($labels[$j] == 'id') && ($value > 0) && ($value < 255)) {
                            $this->_result[$disk][$i][$labels[$j]] = $value;
                            $found = $value;
                        } elseif (($found > 0) && (($labels[$j] == 'attribute_name') || ($labels[$j] == $this->_ids[$found]))) {
                            $this->_result[$disk][$i][$labels[$j]] = $value;
                        }
                        $j++;
                    }
                    $i++;
                }
            } else {
                //SCSI and MVMe devices
                if (!empty($this->_ids[1]) && ($this->_ids[1]=="raw_value")) {
                    if (preg_match('/\nread\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[7]!=null)) {
                            $this->_result[$disk][0]['id'] = 1;
                            $this->_result[$disk][0]['attribute_name'] = "Raw_Read_Error_Rate";
                            $this->_result[$disk][0]['raw_value'] = trim($values[7]);
                        }
                    } elseif (preg_match('/\nMedia and Data Integrity Errors\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[5]!=null)) {
                            $vals=preg_replace('/,/', '', trim($values[5]));
                            $this->_result[$disk][0]['id'] = 1;
                            $this->_result[$disk][0]['attribute_name'] = "Raw_Read_Error_Rate";
                            $this->_result[$disk][0]['raw_value'] = $vals;
                        }
                    }
                }
                if (!empty($this->_ids[5]) && ($this->_ids[5]=="raw_value")) {
                    if (preg_match('/\nElements in grown defect list\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[5]!=null)) {
                            $this->_result[$disk][1]['id'] = 5;
                            $this->_result[$disk][1]['attribute_name'] = "Reallocated_Sector_Ct";
                            $this->_result[$disk][1]['raw_value'] = trim($values[5]);
                        }
                    }
                }
                if (!empty($this->_ids[9]) && ($this->_ids[9]=="raw_value")) {
                    if (preg_match('/\n +number of hours powered up = (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[7]!=null)) {
                            $vals=preg_split('/[,\.]/', trim($values[7]));
                            $this->_result[$disk][2]['id'] = 9;
                            $this->_result[$disk][2]['attribute_name'] = "Power_On_Hours";
                            $this->_result[$disk][2]['raw_value'] =  $vals[0];
                        }
                    } elseif (preg_match('/\nPower On Hours\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[3]!=null)) {
                            $vals=preg_replace('/,/', '', trim($values[3]));
                            $this->_result[$disk][2]['id'] = 9;
                            $this->_result[$disk][2]['attribute_name'] = "Power_On_Hours";
                            $this->_result[$disk][2]['raw_value'] =  $vals;
                        }
                    }
                }
                if (!empty($this->_ids[194]) && ($this->_ids[194]=="raw_value")) {
                    if (preg_match('/\nCurrent Drive Temperature\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[3]!=null)) {
                            $this->_result[$disk][3]['id'] = 194;
                            $this->_result[$disk][3]['attribute_name'] = "Temperature_Celsius";
                            $this->_result[$disk][3]['raw_value'] = trim($values[3]);
                        }
                    } elseif (preg_match('/\nTemperature\: (.*) Celsius/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[1]!=null)) {
                            $this->_result[$disk][3]['id'] = 194;
                            $this->_result[$disk][3]['attribute_name'] = "Temperature_Celsius";
                            $this->_result[$disk][3]['raw_value'] = trim($values[1]);
                        }
                    }
                }
                if (!empty($this->_ids[12]) && ($this->_ids[12]=="raw_value")) {
                    if (preg_match('/\nPower Cycles\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[2]!=null)) {
                            $vals=preg_replace('/,/', '', trim($values[2]));
                            $this->_result[$disk][4]['id'] = 12;
                            $this->_result[$disk][4]['attribute_name'] = "Power_Cycle_Count";
                            $this->_result[$disk][4]['raw_value'] = $vals;
                        }
                    }
                }
                if (!empty($this->_ids[192]) && ($this->_ids[192]=="raw_value")) {
                    if (preg_match('/\nUnsafe Shutdowns\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[2]!=null)) {
                            $vals=preg_replace('/,/', '', trim($values[2]));
                            $this->_result[$disk][5]['id'] = 192;
                            $this->_result[$disk][5]['attribute_name'] = "Unsafe_Shutdown_Count";
                            $this->_result[$disk][5]['raw_value'] = $vals;
                        }
                    }
                }                
                if (!empty($this->_ids[255]) && ($this->_ids[255]=="raw_value")) {
                    if (preg_match('/\nNon-medium error count\: (.*)\n/', $result, $tmpbuf)) {
                        $values=preg_split('/ +/', $tmpbuf[0]);
                        if (!empty($values) && ($values[3]!=null)) {
                            $this->_result[$disk][6]['id'] = 255;
                            $this->_result[$disk][6]['attribute_name'] = "Non-medium_Error_Count";
                            $this->_result[$disk][6]['raw_value'] = trim($values[3]);
                        }
                    }
                }
                if (preg_match('/\nSMART Health Status\: ([^\[\n]+)/', $result, $tmpbuf)) {
                    $event=trim($tmpbuf[1]);
                    if (!empty($event) && ($event!=='OK')) {
                        $this->_event[$disk] = $event;
                    }
                } 
            }
        }
        //Usage test
        $newIds = array();
        foreach ($this->_ids as $id=>$column_name) {
            $found = 0;
            foreach ($this->_result as $diskName=>$diskInfos) {
                if ($found!=2) foreach ($diskInfos as $lineInfos) {
                    if ($found!=2) {
                        $found = 0;
                        foreach ($lineInfos as $label=>$value) {
                            if (($found==0) && ($label=="id") && ($value==$id))
                                $found = 1;
                            if (($found==1) && ($label==$column_name))
                                $found = 2;
                        }
                    }
                }
            }
            if ($found==2) $newIds[$id] = $this->_ids[$id];
        }
        $this->_ids = $newIds;
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        if (empty($this->_result) || empty($this->_ids)) {
            return $this->xml->getSimpleXmlElement();
        }

        $columnsChild = $this->xml->addChild('columns');
        // Fill the xml with preferences
        foreach ($this->_ids as $id=>$column_name) {
            $columnChild = $columnsChild->addChild('column');
            $columnChild->addAttribute('id', $id);
            $columnChild->addAttribute('name', $column_name);
        }

        $disksChild = $this->xml->addChild('disks');
        // Now fill the xml with S.M.A.R.T datas
        foreach ($this->_result as $diskName=>$diskInfos) {
            $diskChild = $disksChild->addChild('disk');
            $diskChild->addAttribute('name', $diskName);
            if (isset($this->_event[$diskName])) $diskChild->addAttribute('event', $this->_event[$diskName]);
            foreach ($diskInfos as $lineInfos) {
                $lineChild = $diskChild->addChild('attribute');

                if (($lineInfos['id'] == 9) && isset($lineInfos['attribute_name']) && ($lineInfos['attribute_name'] !== "Power_On_Hours")) { //Power_On_Hours_and_Msec and Power_On_Seconds
                    $lineInfos['attribute_name'] = "Power_On_Hours";
                    $raw_value = preg_split("/h/", $lineInfos['raw_value'], -1, PREG_SPLIT_NO_EMPTY);
                    $lineInfos['raw_value'] = $raw_value[0];
                }

                foreach ($lineInfos as $label=>$value) {
                    $lineChild->addAttribute($label, $value);
                }
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
