<?php
/**
 * SMART Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_SMART
 * @author    Antoine Bertin <diaoulael@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.smart.inc.php 707 2012-11-28 10:20:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * SMART plugin, which displays all SMART informations available
 *
 * @category  PHP
 * @package   PSI_Plugin_SMART
 * @author    Antoine Bertin <diaoulael@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
            case 'command':
                if ( defined('PSI_PLUGIN_SMART_DEVICES') && is_string(PSI_PLUGIN_SMART_DEVICES) ) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SMART_DEVICES)) {
                        $disks = eval(PSI_PLUGIN_SMART_DEVICES);
                    } else {
                        $disks = array(PSI_PLUGIN_SMART_DEVICES);
                    }
                    foreach ($disks as $disk) {
                        $buffer = "";
                        if (CommonFunctions::executeProgram('smartctl', '--all'.((PSI_PLUGIN_SMART_DEVICE) ? ' --device '.PSI_PLUGIN_SMART_DEVICE : '').' '.$disk, $buffer, PSI_DEBUG)) {
                            $this->_filecontent[$disk] = $buffer;
                        }
                    }
                }
                if ( defined('PSI_PLUGIN_SMART_IDS') && is_string(PSI_PLUGIN_SMART_IDS) ) {
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
                break;
            case 'data':
                if ( defined('PSI_PLUGIN_SMART_DEVICES') && is_string(PSI_PLUGIN_SMART_DEVICES) ) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SMART_DEVICES)) {
                        $disks = eval(PSI_PLUGIN_SMART_DEVICES);
                    } else {
                        $disks = array(PSI_PLUGIN_SMART_DEVICES);
                    }
                    $dn=0;
                    foreach ($disks as $disk) {
                        $buffer="";
                        if (CommonFunctions::rfts(APP_ROOT."/data/smart{$dn}.txt", $buffer) && !empty($buffer)) {
                            $this->_filecontent[$disk] = $buffer;
                        }
                        $dn++;
                    }
                }
                if ( defined('PSI_PLUGIN_SMART_IDS') && is_string(PSI_PLUGIN_SMART_IDS) ) {
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
                break;
            default:
                $this->global_error->addError("switch(PSI_PLUGIN_SMART_ACCESS)", "Bad SMART configuration in SMART.config.php");
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
        if ( empty($this->_filecontent) || empty($this->_ids)) {
            return;
        }
        foreach ($this->_filecontent as $disk=>$result) {
            // set the start and end offset in the result string at the beginning and end respectively
            // just in case we don't find the two strings, so that it still works as expected.
            $startIndex = 0;
            $endIndex = 0;
            $vendorInfos = "";

            // locate the beginning string offset for the attributes
            if ( preg_match('/(Vendor Specific SMART Attributes with Thresholds)/', $result, $matches, PREG_OFFSET_CAPTURE) )
               $startIndex = $matches[0][1];

            // locate the end string offset for the attributes, this is usually right before string "SMART Error Log Version" or "SMART Error Log not supported" (hopefully every output has it!)
            if ( preg_match('/(SMART Error Log Version)|(SMART Error Log not supported)/', $result, $matches, PREG_OFFSET_CAPTURE) )
               $endIndex = $matches[0][1];

            if ($startIndex && $endIndex && ($endIndex>$startIndex))
                 $vendorInfos = preg_split("/\n/", substr ( $result, $startIndex, $endIndex - $startIndex ));

            if (!empty($vendorInfos)) {
                $labels = preg_split('/[\s]+/', $vendorInfos[1]);
                foreach ($labels as $k=>$v) {
                    $labels[$k] = str_replace('#', '', strtolower($v));
                }
                $i = 0; // Line number
                foreach ($vendorInfos as $line) {
                    $line = preg_replace('/^[\s]+/', '', $line);
                    $values = preg_split('/[\s]+/', $line);
                    if (count($values) > count($labels)) {
                        $values = array_slice($values, 0, count($labels), true);
                    }
                    $j = 0;
                    $found = false;
                    foreach ($values as $value) {
                        if ((in_array($value, array_keys($this->_ids)) && $labels[$j] == 'id')) {
                          $arrFullVa = preg_split('/-/', $this->_ids[$value]);
                          if (($arrFullVa[0]=="#replace") && !empty($arrFullVa[1]))
                              $value=$arrFullVa[1];
                        }
                        if (((in_array($value, array_keys($this->_ids)) && $labels[$j] == 'id') || ($found && (in_array($labels[$j], array_values($this->_ids)))) || ($found && $labels[$j] == 'attribute_name'))) {
                            $this->_result[$disk][$i][$labels[$j]] = $value;
                            $found = true;
                        }
                        $j++;
                    }
                    $i++;
                }
            } else {
                //SCSI devices
                if (!empty($this->_ids[1]) && ($this->_ids[1]=="raw_value")) {
                    preg_match('/read\: (.*)\n/', $result, $lines);
                    if (!empty($lines) && !empty($lines[0])) {
                        $values=preg_split('/[\s,]+/',$lines[0]);
                        if (!empty($values) && ($values[7]!=null)) {
                            $this->_result[$disk][0]['id'] = 1;
                            $this->_result[$disk][0]['attribute_name'] = "Raw_Read_Error_Rate";
                            $this->_result[$disk][0]['raw_value'] = $values[7];
                        }
                    }
                }
                if (!empty($this->_ids[5]) && ($this->_ids[5]=="raw_value")) {
                    preg_match('/Elements in grown defect list\: (.*)\n/', $result, $lines);
                    if (!empty($lines) && !empty($lines[0])) {
                        $values=preg_split('/[\s,]+/',$lines[0]);
                        if (!empty($values) && ($values[5]!=null)) {
                            $this->_result[$disk][1]['id'] = 5;
                            $this->_result[$disk][1]['attribute_name'] = "Reallocated_Sector_Ct";
                            $this->_result[$disk][1]['raw_value'] = $values[5];
                        }
                    }
                }
                if (!empty($this->_ids[9]) && ($this->_ids[9]=="raw_value")) {
                    preg_match('/ number of hours powered up = (.*)\n/', $result, $lines);
                    if (!empty($lines) && !empty($lines[0])) {
                        $values=preg_split('/[\s,.]+/',$lines[0]);
                        if (!empty($values) && ($values[7]!=null)) {
                            $this->_result[$disk][2]['id'] = 9;
                            $this->_result[$disk][2]['attribute_name'] = "Power_On_Hours";
                            $this->_result[$disk][2]['raw_value'] =  $values[7];
                        }
                    }
                }
                if (!empty($this->_ids[194]) && ($this->_ids[194]=="raw_value")) {
                    preg_match('/Current Drive Temperature\: (.*)\n/', $result, $lines);
                    if (!empty($lines) && !empty($lines[0])) {
                        $values=preg_split('/[\s,]+/',$lines[0]);
                        if (!empty($values) && ($values[3]!=null)) {
                            $this->_result[$disk][3]['id'] = 194;
                            $this->_result[$disk][3]['attribute_name'] = "Temperature_Celsius";
                            $this->_result[$disk][3]['raw_value'] = $values[3];
                        }
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
     * @return SimpleXMLObject entire XML content for the plugin
     */
    public function xml()
    {
        if ( empty($this->_result) || empty($this->_ids)) {
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
            foreach ($diskInfos as $lineInfos) {
                $lineChild = $diskChild->addChild('attribute');
                foreach ($lineInfos as $label=>$value) {
                    $lineChild->addAttribute($label, $value);
                }
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
