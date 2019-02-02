<?php
/**
 * SNMPPInfo Plugin, which displays printers info via SNMP
 *
 * @category  PHP
 * @package   PSI_Plugin_SNMPPInfo
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2011 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   $Id: class.snmppinfo.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class SNMPPInfo extends PSI_Plugin
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
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        switch (strtolower(PSI_PLUGIN_SNMPPINFO_ACCESS)) {
        case 'command':
                if (defined('PSI_PLUGIN_SNMPPINFO_DEVICES') && is_string(PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                        $printers = eval(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    } else {
                        $printers = array(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    }
                    foreach ($printers as $printer) {
                        CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." ".$printer." .1.3.6.1.2.1.1.5", $buffer, PSI_DEBUG);
                        if (strlen($buffer) > 0) {
                            $this->_filecontent[$printer] = $buffer;

                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." ".$printer." .1.3.6.1.4.1.367.3.2.1.2.24.1.1", $buffer1, false);
                            if (strlen($buffer1) > 0) {
                               $this->_filecontent[$printer] .= "\n".$buffer1;
                            }

                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." ".$printer." .1.3.6.1.2.1.43.11.1.1", $buffer2, PSI_DEBUG);
                            if (strlen($buffer2) > 0) {
                               $this->_filecontent[$printer] .= "\n".$buffer2;
                            }
                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." ".$printer." .1.3.6.1.2.1.43.18.1.1", $buffer3, PSI_DEBUG);
                            if (strlen($buffer3) > 0) {
                               $this->_filecontent[$printer] .= "\n".$buffer3;
                            }
                        }
                    }
                }
                break;
        case 'php-snmp':
                if (!extension_loaded("snmp")) {
                    $this->global_error->addError("Requirements error", "SNMPPInfo plugin requires the snmp extension to php in order to work properly");
                    break;
                }
                snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
                snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
                if (defined('PSI_PLUGIN_SNMPPINFO_DEVICES') && is_string(PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                        $printers = eval(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    } else {
                        $printers = array(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    }
                    foreach ($printers as $printer) {
                        if (! PSI_DEBUG) {
                            restore_error_handler(); /* default error handler */
                            $old_err_rep = error_reporting();
                            error_reporting(E_ERROR); /* fatal errors only */
                        }
                        $bufferarr=snmprealwalk($printer, "public", ".1.3.6.1.2.1.1.5", 1000000 * PSI_SNMP_TIMEOUT_INT, PSI_SNMP_RETRY_INT);
                        if (! PSI_DEBUG) {
                            error_reporting($old_err_rep); /* restore error level */
                            set_error_handler('errorHandlerPsi'); /* restore error handler */
                        }
                        if (! empty($bufferarr)) {
                            $buffer="";
                            foreach ($bufferarr as $id=>$string) {
                                $buffer .= $id." = ".$string."\n";
                            }

                            restore_error_handler(); /* default error handler */
                            $old_err_rep = error_reporting();
                            error_reporting(E_ERROR); /* fatal errors only */

                            $bufferarr1=snmprealwalk($printer, "public", ".1.3.6.1.4.1.367.3.2.1.2.24.1.1", 1000000 * PSI_SNMP_TIMEOUT_INT, PSI_SNMP_RETRY_INT);

                            error_reporting($old_err_rep); /* restore error level */
                            set_error_handler('errorHandlerPsi'); /* restore error handler */
                            if (! empty($bufferarr1)) {
                                foreach ($bufferarr1 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            }

                            if (! PSI_DEBUG) {
                                restore_error_handler(); /* default error handler */
                                $old_err_rep = error_reporting();
                                error_reporting(E_ERROR); /* fatal errors only */
                            }
                            $bufferarr2=snmprealwalk($printer, "public", ".1.3.6.1.2.1.43.11.1.1", 1000000 * PSI_SNMP_TIMEOUT_INT, PSI_SNMP_RETRY_INT);
                            if (! PSI_DEBUG) {
                                error_reporting($old_err_rep); /* restore error level */
                                set_error_handler('errorHandlerPsi'); /* restore error handler */
                            }
                            if (! empty($bufferarr2)) {
                                foreach ($bufferarr2 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            }

                            if (! PSI_DEBUG) {
                                restore_error_handler(); /* default error handler */
                                $old_err_rep = error_reporting();
                                error_reporting(E_ERROR); /* fatal errors only */
                            }
                            $bufferarr3=snmprealwalk($printer, "public", ".1.3.6.1.2.1.43.18.1.1", 1000000 * PSI_SNMP_TIMEOUT_INT, PSI_SNMP_RETRY_INT);
                            if (! PSI_DEBUG) {
                                error_reporting($old_err_rep); /* restore error level */
                                set_error_handler('errorHandlerPsi'); /* restore error handler */
                            }
                            if (! empty($bufferarr3)) {
                                foreach ($bufferarr3 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            }

                            if (strlen(trim($buffer)) > 0) {
                                $this->_filecontent[$printer] = $buffer;
                            }
                        }
                    }
                }
                break;
        case 'data':
                if (defined('PSI_PLUGIN_SNMPPINFO_DEVICES') && is_string(PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                    if (preg_match(ARRAY_EXP, PSI_PLUGIN_SNMPPINFO_DEVICES)) {
                        $printers = eval(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    } else {
                        $printers = array(PSI_PLUGIN_SNMPPINFO_DEVICES);
                    }
                    $pn=0;
                    foreach ($printers as $printer) {
                        $buffer="";
                        if (CommonFunctions::rfts(PSI_APP_ROOT."/data/snmppinfo{$pn}.txt", $buffer) && !empty($buffer)) {
                            $this->_filecontent[$printer] = $buffer;
                        }
                        $pn++;
                    }
                }
                break;
            default:
                $this->global_error->addConfigError("__construct()", "[snmppinfo] ACCESS");
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
        if (empty($this->_filecontent)) {
            return;
        }
        foreach ($this->_filecontent as $printer=>$result) {
            $result = preg_replace('/End of MIB\r?\n/', '', $result);
            $result = preg_replace('/\s\r?\n([^\.])/', ' $1', $result);
            $lines = preg_split('/\r?\n/', $result);
            foreach ($lines as $line) {
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
                if (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.11\.1\.1\.6\.1\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesDescription']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.11\.1\.1\.7\.1\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesSupplyUnit']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.11\.1\.1\.8\.1\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesMaxCapacity']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.11\.1\.1\.9\.1\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesLevel']=$data[2];
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.1\.5\.0 = STRING:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][0]['prtMarkerSuppliesDescription']=trim($data[1], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.18\.1\.1\.8\.1\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][99][$data[1]]["message"]=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.2\.1\.43\.18\.1\.1\.2\.1\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][99][$data[1]]["severity"]=$data[2];

                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.367\.3\.2\.1\.2\.24\.1\.1\.2\.(.*) = STRING:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesDescriptionRicoh']=trim($data[2], "\"");
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.367\.3\.2\.1\.2\.24\.1\.1\.5\.(.*) = INTEGER:\s(.*)/', $line, $data)) {
                    $this->_result[$printer][$data[1]]['prtMarkerSuppliesLevelRicoh']=$data[2];
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
        foreach ($this->_result as $printer=>$markersupplies_item) {
            $xmlsnmppinfo_printer = $this->xml->addChild("Printer");
            $xmlsnmppinfo_printer->addAttribute("Device", $printer);
            if (isset($markersupplies_item[1]) &&
                isset($markersupplies_item[1]['prtMarkerSuppliesDescriptionRicoh']) &&
                isset($markersupplies_item[1]['prtMarkerSuppliesLevelRicoh'])) {
                $ricoh = true;
            } else {
                $ricoh = false;
            }

            foreach ($markersupplies_item as $marker=>$snmppinfo_item) {
                if ($marker==0) {
                    $xmlsnmppinfo_printer->addAttribute("Name", $snmppinfo_item['prtMarkerSuppliesDescription']);
                } elseif ($marker==99) {
                    foreach ($snmppinfo_item as $item=>$iarr) {
                        if (isset($iarr["message"]) && $iarr["message"] != "") {
                            $xmlsnmppinfo_errors = $xmlsnmppinfo_printer->addChild("PrinterMessage");
                            $xmlsnmppinfo_errors->addAttribute("Message", $iarr["message"]);
                            $xmlsnmppinfo_errors->addAttribute("Severity", $iarr["severity"]);
                        }
                    }
                } else {
                    if ($ricoh) {
                        if (isset($snmppinfo_item['prtMarkerSuppliesDescriptionRicoh'])) {
                            $xmlsnmppinfo = $xmlsnmppinfo_printer->addChild("MarkerSupplies");

                            $xmlsnmppinfo->addAttribute("Description", $snmppinfo_item['prtMarkerSuppliesDescriptionRicoh']);
                            $xmlsnmppinfo->addAttribute("SupplyUnit", "19");
                            $xmlsnmppinfo->addAttribute("MaxCapacity", "100");
                            $xmlsnmppinfo->addAttribute("Level", isset($snmppinfo_item['prtMarkerSuppliesLevelRicoh']) ? $snmppinfo_item['prtMarkerSuppliesLevelRicoh'] : "");
                        }
                    } else {
                        $xmlsnmppinfo = $xmlsnmppinfo_printer->addChild("MarkerSupplies");

                        $xmlsnmppinfo->addAttribute("Description", isset($snmppinfo_item['prtMarkerSuppliesDescription']) ? $snmppinfo_item['prtMarkerSuppliesDescription'] : "");
                        $xmlsnmppinfo->addAttribute("SupplyUnit", isset($snmppinfo_item['prtMarkerSuppliesSupplyUnit']) ? $snmppinfo_item['prtMarkerSuppliesSupplyUnit'] : "");
                        $xmlsnmppinfo->addAttribute("MaxCapacity", isset($snmppinfo_item['prtMarkerSuppliesMaxCapacity']) ? $snmppinfo_item['prtMarkerSuppliesMaxCapacity'] : "");
                        $xmlsnmppinfo->addAttribute("Level", isset($snmppinfo_item['prtMarkerSuppliesLevel']) ? $snmppinfo_item['prtMarkerSuppliesLevel'] : "");
                    }
                }
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
