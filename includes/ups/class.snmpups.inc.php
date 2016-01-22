<?php
/**
 * SNMPups class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.apcupsd.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting ups information from SNMPups program
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    Artem Volk <artvolk@mail.ru>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SNMPups extends UPS
{
    /**
     * internal storage for all gathered data
     *
     * @var Array
     */
    private $_output = array();

    /**
     * get all information from all configured ups in phpsysinfo.ini and store output in internal array
     */
    public function __construct()
    {
        parent::__construct();
        switch (strtolower(PSI_UPS_SNMPUPS_ACCESS)) {
        case 'command':
                if (defined('PSI_UPS_SNMPUPS_LIST') && is_string(PSI_UPS_SNMPUPS_LIST)) {
                    if (preg_match(ARRAY_EXP, PSI_UPS_SNMPUPS_LIST)) {
                        $upss = eval(PSI_UPS_SNMPUPS_LIST);
                    } else {
                        $upss = array(PSI_UPS_SNMPUPS_LIST);
                    }
                    foreach ($upss as $ups) {
                        CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -r 1 ".$ups." .1.3.6.1.4.1.318.1.1.1.1", $buffer, PSI_DEBUG);
                        if (strlen(trim($buffer)) > 0) {
                            $this->_output[$ups] = $buffer;
                            $buffer = "";
                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -r 1 ".$ups." .1.3.6.1.4.1.318.1.1.1.2", $buffer, PSI_DEBUG);
                            if (strlen(trim($buffer)) > 0) {
                                $this->_output[$ups] .=  "\n".$buffer;
                            }
                            $buffer = "";
                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -r 1 ".$ups." .1.3.6.1.4.1.318.1.1.1.3", $buffer, PSI_DEBUG);
                            if (strlen(trim($buffer)) > 0) {
                                $this->_output[$ups] .=  "\n".$buffer;
                            }
                            $buffer = "";
                            CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -r 1 ".$ups." .1.3.6.1.4.1.318.1.1.1.4", $buffer, PSI_DEBUG);
                            if (strlen(trim($buffer)) > 0) {
                                $this->_output[$ups] .=  "\n".$buffer;
                            }
                        }
                    }
                }
                break;
        case 'php-snmp':
                if (!extension_loaded("snmp")) {
                    $this->error->addError("Requirements error", "SNMPups plugin requires the snmp extension to php in order to work properly");
                    break;
                }
                snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
                snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
                if (defined('PSI_UPS_SNMPUPS_LIST') && is_string(PSI_UPS_SNMPUPS_LIST)) {
                    if (preg_match(ARRAY_EXP, PSI_UPS_SNMPUPS_LIST)) {
                        $upss = eval(PSI_UPS_SNMPUPS_LIST);
                    } else {
                        $upss = array(PSI_UPS_SNMPUPS_LIST);
                    }
                    foreach ($upss as $ups) {
                        if (! PSI_DEBUG) {
                            restore_error_handler(); /* default error handler */
                            $old_err_rep = error_reporting();
                            error_reporting(E_ERROR); /* fatal errors only */
                        }
                        $bufferarr=snmprealwalk($ups, "public", ".1.3.6.1.4.1.318.1.1.1.1", 1000000, 1);
                        if (! PSI_DEBUG) {
                            error_reporting($old_err_rep); /* restore error level */
                            set_error_handler('errorHandlerPsi'); /* restore error handler */
                        }
                        if (! empty($bufferarr)) {
                            $buffer="";
                            foreach ($bufferarr as $id=>$string) {
                                $buffer .= $id." = ".$string."\n";
                            }

                            if (! PSI_DEBUG) {
                                restore_error_handler(); /* default error handler */
                                $old_err_rep = error_reporting();
                                error_reporting(E_ERROR); /* fatal errors only */
                            }
                            $bufferarr2=snmprealwalk($ups, "public", ".1.3.6.1.4.1.318.1.1.1.2", 1000000, 1);
                            $bufferarr3=snmprealwalk($ups, "public", ".1.3.6.1.4.1.318.1.1.1.3", 1000000, 1);
                            $bufferarr4=snmprealwalk($ups, "public", ".1.3.6.1.4.1.318.1.1.1.4", 1000000, 1);
                            if (! PSI_DEBUG) {
                                error_reporting($old_err_rep); /* restore error level */
                                set_error_handler('errorHandlerPsi'); /* restore error handler */
                            }
                            if (! empty($bufferarr2)) {
                                foreach ($bufferarr2 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            if (! empty($bufferarr3)) {
                                foreach ($bufferarr3 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            }                            }
                            if (! empty($bufferarr4)) {
                                foreach ($bufferarr4 as $id=>$string) {
                                    $buffer .= $id." = ".$string."\n";
                                }
                            }
                            if (strlen(trim($buffer)) > 0) {
                                $this->_output[$ups] = $buffer;
                            }
                        }
                    }
                }
                break;
            default:
                $this->error->addError("switch(PSI_UPS_SNMPUPS_ACCESS)", "Bad SNMPups configuration in phpsysinfo.ini");
                break;
        }
    }

    /**
     * parse the input and store data in resultset for xml generation
     *
     * @return Void
     */
    private function _info()
    {
        if (empty($this->_output)) {
            return;
        }
        foreach ($this->_output as $ups) {
            $lines = preg_split('/\r?\n/', $ups);
            $dev = new UPSDevice();
            $status1 = "";
            $status2 = "";
            $status3 = "";
            foreach ($lines as $line) {
                $dev->setMode("SNMP");
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.1\.1\.2\.0 = STRING:\s(.*)/', $line, $data)) {
                    $dev->setName(trim($data[1], "\""));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.1\.1\.1\.0 = STRING:\s(.*)/', $line, $data)) {
                    $dev->setModel(trim($data[1], "\""));
                }
                 if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.4\.1\.1\.0 = INTEGER:\s(.*)/', $line, $data)) {
                    switch (trim($data[1])) {
                        case 1: $status1 = "Unknown";
                                break;
                        case 2: $status1 = "On Line";
                                break;
                        case 3: $status1 = "On Battery";
                                break;
                        case 4: $status1 = "On Smart Boost";
                                break;
                        case 5: $status1 = "Timed Sleeping";
                                break;
                        case 6: $status1 = "Software Bypass";
                                break;
                        case 7: $status1 = "Off";
                                break;
                        case 8: $status1 = "Rebooting";
                                break;
                        case 9: $status1 = "Switched Bypass";
                                break;
                        case 10:$status1 = "Hardware Failure Bypass";
                                break;
                        case 11:$status1 = "Sleeping Until Power Returns";
                                break;
                        case 12:$status1 = "On Smart Trim";
                                break;
                       default: $status1 = "Unknown state: ".trim($data[1]);
                                break;
                    }
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.1\.1\.0 = INTEGER:\s(.*)/', $line, $data)) {
                    switch (trim($data[1])) {
                        case 1: $status2 = "Battery Unknown";
                                break;
                        case 2: break;
                        case 3: $status2 = "Battery Low";
                                break;
                       default: $status2 = "Battery Unknown: ".trim($data[1]);
                                break;
                    }
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.2\.4\.0 = INTEGER:\s(.*)/', $line, $data)) {
                    switch (trim($data[1])) {
                        case 1: break;
                        case 2: $status3 = "Replace Battery";
                                break;
                       default: $status3 = "Replace Battery: ".trim($data[1]);
                                break;
                    }
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.3\.3\.1\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLineVoltage(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.3\.2\.1\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLineVoltage(trim($data[1]));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.4\.3\.3\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLoad(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.4\.2\.3\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLoad(trim($data[1]));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.3\.4\.0 = INTEGER:\s(.*)/', $line, $data)) {
                    $dev->setBatteryVoltage(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.2\.8\.0 = INTEGER:\s(.*)/', $line, $data)) {
                    $dev->setBatteryVoltage(trim($data[1]));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.3\.1\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setBatterCharge(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.2\.1\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setBatterCharge(trim($data[1]));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.2\.3\.0 = Timeticks:\s\((\d*)\)/', $line, $data)) {
                    $dev->setTimeLeft($data[1]/6000);
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.3\.2\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setTemperatur(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.2\.2\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setTemperatur(trim($data[1]));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.2\.1\.3\.0 = STRING:\s(.*)/', $line, $data)) {
                    $dev->setBatteryDate(trim($data[1], "\""));
                }
                if (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.3\.3\.4\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLineFrequency(trim($data[1])/10);
                } elseif (preg_match('/^\.1\.3\.6\.1\.4\.1\.318\.1\.1\.1\.3\.2\.4\.0 = Gauge32:\s(.*)/', $line, $data)) {
                    $dev->setLineFrequency(trim($data[1]));
                }
            }
            $status = "";
            if ($status1 !== "") {
                 $status = $status1;
            }
            if ($status2 !== "") {
                 if ($status !== "") {
                     $status .= ", ".$status2;
                 } else {
                     $status = $status2;
                 }
            }
            if ($status3 !== "") {
                 if ($status !== "") {
                     $status .= ", ".$status3;
                 } else {
                     $status = $status3;
                 }
            }
            if ($status !== "") {
               $dev->setStatus(trim($status));
            }

            $this->upsinfo->setUpsDevices($dev);
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_UPS::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_info();
    }
}
