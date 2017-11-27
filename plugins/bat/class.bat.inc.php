<?php
 /**
 * BAT Plugin, which displays battery state
 *
 * @category  PHP
 * @package   PSI_Plugin_BAT
 * @author    Erkan V
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   $Id: class.bat.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class BAT extends PSI_Plugin
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
        $buffer = array();
        switch (strtolower(PSI_PLUGIN_BAT_ACCESS)) {
        case 'command':
            if (PSI_OS == 'WINNT') {
                $_cim = null; //root\CIMv2
                $_wmi = null; //root\WMI
                try {
                    // initialize the wmi object
                    $objLocatorCIM = new COM('WbemScripting.SWbemLocator');
                    $_cim = $objLocatorCIM->ConnectServer('', 'root\CIMv2');

                    // initialize the wmi object
                    $objLocatorWMI = new COM('WbemScripting.SWbemLocator');
                    $_wmi = $objLocatorWMI->ConnectServer('', 'root\WMI');
                } catch (Exception $e) {
                    $this->global_error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
                }

                $bufferWB = CommonFunctions::getWMI($_cim, 'Win32_Battery', array('Caption', 'EstimatedChargeRemaining', 'DesignVoltage', 'BatteryStatus', 'Chemistry'));
                if (sizeof($bufferWB)>0) {
                    $bufferWPB = CommonFunctions::getWMI($_cim, 'Win32_PortableBattery', array('DesignVoltage', 'Chemistry', 'DesignCapacity', 'FullChargeCapacity', 'Manufacturer'));
                    $bufferBS = CommonFunctions::getWMI($_wmi, 'BatteryStatus', array('RemainingCapacity', 'Voltage'));
                    $bufferBCC = CommonFunctions::getWMI($_wmi, 'BatteryCycleCount', array('CycleCount'));
                    $bufferBFCC = CommonFunctions::getWMI($_wmi, 'BatteryFullChargedCapacity', array('FullChargedCapacity'));
                    $sobWB = sizeof($bufferWB);
                    if (sizeof($bufferWPB) != $sobWB) {
                        $bufferWPB = null;
                    }
                    if (sizeof($bufferBS) != $sobWB) {
                        $bufferBS = null;
                    }
                    if (sizeof($bufferBCC) != $sobWB) {
                        $bufferBCC = null;
                    }
                    if (sizeof($bufferBFCC) != $sobWB) {
                        $bufferBFCC = null;
                    }
                    for ($bi = 0; $bi < $sobWB; $bi++) {
                        $buffer[$bi]['state'] = '';
                        $buffer[$bi]['info'] = '';
                        $capacity = '';
                        if (isset($bufferWB[$bi]['EstimatedChargeRemaining'])) {
                            $capacity = $bufferWB[$bi]['EstimatedChargeRemaining'];
                        }
                        if (isset($bufferWB[$bi]['BatteryStatus'])) {
                            switch ($bufferWB[$bi]['BatteryStatus']) {
                                case  1: $batstat = 'Discharging'; break;
                                case  2: $batstat = 'AC connected'; break;
                                case  3: $batstat = 'Fully Charged'; break;
                                case  4: $batstat = 'Low'; break;
                                case  5: $batstat = 'Critical'; break;
                                case  6: $batstat = 'Charging'; break;
                                case  7: $batstat = 'Charging and High'; break;
                                case  8: $batstat = 'Charging and Low'; break;
                                case  9: $batstat = 'Charging and Critical'; break;
                                case 10: $batstat = 'Undefined'; break;
                                case 11: $batstat = 'Partially Charged'; break;
                                default: $batstat = '';
                            }
                            if ($batstat != '') $buffer[$bi]['state'] .= 'POWER_SUPPLY_STATUS='.$batstat."\n";
                        }
                        $techn = '';
                        if (isset($bufferWB[$bi]['Chemistry'])) {
                            switch ($bufferWB[$bi]['Chemistry']) {
                                case 1: $techn = 'Other'; break;
                                case 2: $techn = 'Unknown'; break;
                                case 3: $techn = 'PbAc'; break;
                                case 4: $techn = 'NiCd'; break;
                                case 5: $techn = 'NiMH'; break;
                                case 6: $techn = 'Li-ion'; break;
                                case 7: $techn = 'Zinc-air'; break;
                                case 8: $techn = 'Li-poly'; break;
                            }
                        }
                        if (isset($bufferWPB[$bi]['DesignVoltage'])) {
                            $buffer[$bi]['info'] .= 'POWER_SUPPLY_VOLTAGE_MIN_DESIGN='.($bufferWPB[$bi]['DesignVoltage']*1000)."\n";
                        }
                        if (isset($bufferWPB[$bi]['Manufacturer'])) {
                            $manuf = $bufferWPB[$bi]['Manufacturer'];
                            if ($manuf != '') {
                                $buffer[$bi]['info'] .= 'POWER_SUPPLY_MANUFACTURER='.$manuf."\n";
                            }
                        }
                        // sometimes Chemistry from Win32_Battery returns 2 but Win32_PortableBattery returns e.g. 6
                        if ((($techn == '') || ($techn == 'Unknown')) && isset($bufferWPB[$bi]['Chemistry'])) {
                            switch ($bufferWPB[$bi]['Chemistry']) {
                                case 1: $techn = 'Other'; break;
                                case 2: $techn = 'Unknown'; break;
                                case 3: $techn = 'PbAc'; break;
                                case 4: $techn = 'NiCd'; break;
                                case 5: $techn = 'NiMH'; break;
                                case 6: $techn = 'Li-ion'; break;
                                case 7: $techn = 'Zinc-air'; break;
                                case 8: $techn = 'Li-poly'; break;
                            }
                        }
                        if ($techn != '') $buffer[$bi]['info'] .= 'POWER_SUPPLY_TECHNOLOGY='.$techn."\n";

                        if (sizeof($bufferBS)>0) {
                            if (isset($bufferBS[$bi]['RemainingCapacity']) && ($bufferBS[$bi]['RemainingCapacity']>0)) { // ? >=
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_ENERGY_NOW='.($bufferBS[$bi]['RemainingCapacity']*1000)."\n";
                                $capacity = '';
                            }
                            if (isset($bufferBS[$bi]['Voltage']) && ($bufferBS[$bi]['Voltage']>0)) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.($bufferBS[$bi]['Voltage']*1000)."\n";
                            } elseif (isset($bufferWB[$bi]['DesignVoltage'])) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.($bufferWB[$bi]['DesignVoltage']*1000)."\n";
                            }
                        }

                        if (isset($bufferWB[$bi]['Caption'])) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_NAME='.$bufferWB[$bi]['Caption']."\n";
                        }
                        if (!isset($bufferWPB[$bi]['FullChargeCapacity']) && isset($bufferBFCC[$bi]['FullChargedCapacity'])) {
                            $bufferWPB[$bi]['FullChargeCapacity'] = $bufferBFCC[$bi]['FullChargedCapacity'];
                        }
                        if (isset($bufferWPB[$bi]['FullChargeCapacity'])) {
                            $buffer[$bi]['info'] .= 'POWER_SUPPLY_ENERGY_FULL='.($bufferWPB[$bi]['FullChargeCapacity']*1000)."\n";
                            if ($capacity != '') $buffer[$bi]['state'] .= 'POWER_SUPPLY_ENERGY_NOW='.(round($capacity*$bufferWPB[$bi]['FullChargeCapacity']*10)."\n");
                            if (isset($bufferWPB[$bi]['DesignCapacity']) && ($bufferWPB[$bi]['DesignCapacity']>0))
                                $buffer[$bi]['info'] .= 'POWER_SUPPLY_ENERGY_FULL_DESIGN='.($bufferWPB[$bi]['DesignCapacity']*1000)."\n";
                        } elseif (isset($bufferWPB[$bi]['DesignCapacity']) && ($bufferWPB[$bi]['DesignCapacity']>0)) {
                            $buffer[$bi]['info'] .= 'POWER_SUPPLY_ENERGY_FULL_DESIGN='.($bufferWPB[$bi]['DesignCapacity']*1000)."\n";
                            if ($capacity != '') $buffer[$bi]['state'] .= 'POWER_SUPPLY_ENERGY_NOW='.(round($capacity*$bufferWPB[$bi]['DesignCapacity']*10)."\n");
                        } else {
                            if ($capacity != '') $buffer[$bi]['state'] .= 'POWER_SUPPLY_CAPACITY='.$capacity."\n";
                        }

                        if (isset($bufferBCC[$bi]['CycleCount']) && ($bufferBCC[$bi]['CycleCount']>0)) {
                            $buffer[$bi]['info'] .= 'POWER_SUPPLY_CYCLE_COUNT='.$bufferBCC[$bi]['CycleCount']."\n";
                        }
                    }
                }
            } elseif (PSI_OS == 'Darwin') {
                $buffer[0]['info'] = '';
                CommonFunctions::executeProgram('ioreg', '-w0 -l -n AppleSmartBattery -r', $buffer[0]['info'], false);
                if ($buffer[0]['info'] !== '') {
                    $buffer[0]['info'] .= "POWER_SUPPLY_NAME=AppleSmartBattery\n";
                }
            } elseif (PSI_OS == 'FreeBSD') {
                $buffer[0]['info'] = '';
                CommonFunctions::executeProgram('acpiconf', '-i batt', $buffer[0]['info'], false);
                if ($buffer[0]['info'] !== '') {
                    $buffer[0]['info'] .= "POWER_SUPPLY_NAME=batt\n";
                }
            } elseif (PSI_OS == 'OpenBSD') {
                $buffer[0]['info'] = '';
                CommonFunctions::executeProgram('sysctl', 'hw.sensors.acpibat0', $buffer[0]['info'], false);
                if ($buffer[0]['info'] !== '') {
                    $buffer[0]['info'] .= "POWER_SUPPLY_NAME=acpibat0\n";
                }
            } else {
                $buffer[0]['info'] = '';
                $buffer[0]['state'] = '';
                $bat_name = PSI_PLUGIN_BAT_DEVICE;
                $rfts_bi = CommonFunctions::rfts('/proc/acpi/battery/'.$bat_name.'/info', $buffer[0]['info'], 0, 4096, false);
                $rfts_bs = CommonFunctions::rfts('/proc/acpi/battery/'.$bat_name.'/state', $buffer[0]['state'], 0, 4096, false);
                if (!$rfts_bi && !$rfts_bs) {
                    $buffer[0]['info'] = '';
                    $buffer[0]['state'] = '';
                    if (!CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/uevent', $buffer[0]['info'], 0, 4096, false)) {
                        if (CommonFunctions::rfts('/sys/class/power_supply/battery/uevent', $buffer[0]['info'], 0, 4096, false)) {
                            $bat_name = 'battery';
                        } else {
                            CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/uevent', $buffer[0]['info'], 0, 4096, PSI_DEBUG); // Once again but with debug
                        }
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/voltage_min_design', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_VOLTAGE_MIN_DESIGN='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/voltage_max_design', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_VOLTAGE_MAX_DESIGN='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/voltage_now', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/energy_full', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_ENERGY_FULL='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/energy_now', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_ENERGY_NOW='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/charge_full', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_CHARGE_FULL='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/charge_now', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_CHARGE_NOW='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/capacity', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_CAPACITY='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/technology', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_TECHNOLOGY='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/status', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_STATUS='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/batt_temp', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_TEMP='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/batt_vol', $buffer1, 1, 4096, false)) {
                       $buffer[0]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/health', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_HEALTH='.trim($buffer1)."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.$bat_name.'/manufacturer', $buffer1, 1, 4096, false)) {
                        $buffer[0]['state'] .= 'POWER_SUPPLY_MANUFACTURER='.trim($buffer1)."\n";
                    }
                }
                if ($buffer[0]['info'] !== '') {
                    $buffer[0]['info'] .= 'POWER_SUPPLY_NAME='.$bat_name."\n";
                } elseif ($buffer[0]['state'] !== '') {
                    $buffer[0]['state'] .= 'POWER_SUPPLY_NAME='.$bat_name."\n";
                }
            }
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/bat_info.txt", $buffer[0]['info']);
            CommonFunctions::rfts(APP_ROOT."/data/bat_state.txt", $buffer[0]['state']);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_BAT_ACCESS");
            break;
        }
        for ($bi = 0; $bi < sizeof($buffer); $bi++) {
            if (isset($buffer[$bi]['info'])) {
                $this->_filecontent[$bi]['info'] = preg_split("/\n/", $buffer[$bi]['info'], -1, PREG_SPLIT_NO_EMPTY);
            }
            if (isset($buffer[$bi]['state'])) {
                $this->_filecontent[$bi]['state'] = preg_split("/\n/", $buffer[$bi]['state'], -1, PREG_SPLIT_NO_EMPTY);
            }
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
        for ($bi = 0; $bi < sizeof($this->_filecontent); $bi++) {
            if (isset($this->_filecontent[$bi]['info'])) foreach ($this->_filecontent[$bi]['info'] as $roworig) {
                $roworig = trim($roworig);
                if (preg_match('/^[dD]esign capacity:\s*(.*) (.*)$/', $roworig, $data)) {
                    $bat['design_capacity'] = $data[1];
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = trim($data[2]);
                    } elseif ($bat['capacity_unit'] != trim($data[2])) {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^[lL]ast full capacity:\s*(.*) (.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = $data[1];
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = trim($data[2]);
                    } elseif ($bat['capacity_unit'] != trim($data[2])) {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^cycle count:\s*(.*)$/', $roworig, $data) && ($data[1]>0)) {
                    $bat['cycle_count'] = $data[1];
                } elseif (preg_match('/^[dD]esign voltage:\s*(.*) (.*)$/', $roworig, $data)) {
                    if ($data[2]=="mV") { // uV or mV detection
                        $bat['design_voltage'] = $data[1];
                    } else {
                        $bat['design_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^battery type:\s*(.*)$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^OEM info:\s*(.*)$/', $roworig, $data)) {
                    $bat['battery_manufacturer'] = $data[1];

                } elseif (preg_match('/^POWER_SUPPLY_CYCLE_COUNT=(.*)$/', $roworig, $data) && ($data[1]>0)) {
                    $bat['cycle_count'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MIN_DESIGN=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['design_voltage'] = $data[1];
                    } else {
                        $bat['design_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['design_voltage_max'] = $data[1];
                    } else {
                        $bat['design_voltage_max'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_ENERGY_FULL=(.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = ($data[1]/1000);
                    if ($data[1]>=1000000000) { // µWh or nWh detection
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "µWh";
                        } elseif ($bat['capacity_unit'] != "µWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    } else {
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "mWh";
                        } elseif ($bat['capacity_unit'] != "mWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    }
                } elseif (preg_match('/^POWER_SUPPLY_CHARGE_FULL=(.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = ($data[1]/1000);
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mAh";
                    } elseif ($bat['capacity_unit'] != "mAh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^POWER_SUPPLY_ENERGY_NOW=(.*)$/', $roworig, $data)) {
                    if (!isset($bat['capacity_unit']) || ($bat['capacity_unit'] == "mWh")) {
                        $bat['capacity_unit'] = "mWh";
                        $bat['remaining_capacity'] = ($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_CHARGE_NOW=(.*)$/', $roworig, $data)) {
                    if (!isset($bat['capacity_unit']) || ($bat['capacity_unit'] == "mAh")) {
                        $bat['capacity_unit'] = "mAh";
                        $bat['remaining_capacity'] = ($data[1]/1000);
                    }

                /* auxiary */
                } elseif (preg_match('/^POWER_SUPPLY_ENERGY_FULL_DESIGN=(.*)$/', $roworig, $data)) {
                    $bat['design_capacity'] = ($data[1]/1000);
                    if ($data[1]>=1000000000) { // µWh or nWh detection
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "µWh";
                        } elseif ($bat['capacity_unit'] != "µWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    } else {
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "mWh";
                        } elseif ($bat['capacity_unit'] != "mWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    }
                } elseif (preg_match('/^POWER_SUPPLY_CHARGE_FULL_DESIGN=(.*)$/', $roworig, $data)) {
                    $bat['design_capacity'] = ($data[1]/1000);
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mAh";
                    } elseif ($bat['capacity_unit'] != "mAh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['present_voltage'] = $data[1];
                    } else {
                        $bat['present_voltage'] = round($data[1]/1000);
                    }

                } elseif (preg_match('/^POWER_SUPPLY_CAPACITY=(.*)$/', $roworig, $data)) {
                    $bat['capacity'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_TEMP=(.*)$/', $roworig, $data)) {
                    $bat['battery_temperature'] = $data[1]/10;
                } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY=(.*)$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_STATUS=(.*)$/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_HEALTH=(.*)$/', $roworig, $data)) {
                    $bat['battery_condition'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_MANUFACTURER=(.*)$/', $roworig, $data)) {
                    $bat['battery_manufacturer'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_NAME=(.*)$/', $roworig, $data)) {
                    $bat['name'] = $data[1];

                /* Darwin */
                } elseif (preg_match('/^"MaxCapacity"\s*=\s*(.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = $data[1];
                } elseif (preg_match('/^"CurrentCapacity"\s*=\s*(.*)$/', $roworig, $data)) {
                    $bat['remaining_capacity'] = $data[1];
                } elseif (preg_match('/^"Voltage"\s*=\s*(.*)$/', $roworig, $data)) {
                    $bat['present_voltage'] = $data[1];
                } elseif (preg_match('/^"BatteryType"\s*=\s*"(.*)"$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^"Temperature"\s*=\s*(.*)$/', $roworig, $data)) {
                    if ($data[1]>0) $bat['battery_temperature'] = $data[1]/100;
                } elseif (preg_match('/^"DesignCapacity"\s*=\s*(.*)$/', $roworig, $data)) {
                    $bat['design_capacity'] = $data[1];
                } elseif (preg_match('/^"CycleCount"\s*=\s*(.*)$/', $roworig, $data) && ($data[1]>0)) {
                    $bat['cycle_count'] = $data[1];
                /* auxiary */
                } elseif (preg_match('/^"FullyCharged"\s*=\s*Yes$/', $roworig, $data)) {
                    $bat['charging_state_f'] = true;
                } elseif (preg_match('/^"IsCharging"\s*=\s*Yes$/', $roworig, $data)) {
                    $bat['charging_state_i'] = true;
                } elseif (preg_match('/^"ExternalConnected"\s*=\s*Yes$/', $roworig, $data)) {
                    $bat['charging_state_e'] = true;

                /* FreeBSD */
                } elseif (preg_match('/^Type:\s*(.*)$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^State:\s*(.*)$/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];
                } elseif (preg_match('/^Present voltage:\s*(.*) (.*)$/', $roworig, $data)) {
                    if ($data[2]=="mV") { // uV or mV detection
                        $bat['present_voltage'] = $data[1];
                    } else {
                        $bat['present_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^Voltage:\s*(.*) (.*)$/', $roworig, $data)) {
                    if ($data[2]=="mV") { // uV or mV detection
                        $bat['present_voltage'] = $data[1];
                    } else {
                        $bat['present_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^Remaining capacity:\s*(.*)%$/', $roworig, $data)) {
                    $bat['capacity'] = $data[1];

                /* OpenBSD */
                } elseif (preg_match('/^hw.sensors.acpibat0.volt0=(.*) VDC \(voltage\)$/', $roworig, $data)) {
                    $bat['design_voltage'] = 1000*$data[1];
                } elseif (preg_match('/^hw.sensors.acpibat0.volt1=(.*) VDC \(current voltage\)$/', $roworig, $data)) {
                    $bat['present_voltage'] = 1000*$data[1];
                } elseif (preg_match('/^hw.sensors.acpibat0.watthour0=(.*) Wh \(last full capacity\)$/', $roworig, $data)) {
                    $bat['full_capacity'] = 1000*$data[1];
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mWh";
                    } elseif ($bat['capacity_unit'] != "mWh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^hw.sensors.acpibat0.watthour4=(.*) Wh \(design capacity\)$/', $roworig, $data)) {
                    $bat['design_capacity'] = 1000*$data[1];
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mWh";
                    } elseif ($bat['capacity_unit'] != "mWh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^hw.sensors.acpibat0.watthour3=(.*) Wh \(remaining capacity\)/', $roworig, $data)) {
                    $bat['remaining_capacity'] = 1000*$data[1];
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mWh";
                    } elseif ($bat['capacity_unit'] != "mWh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^hw.sensors.acpibat0.raw0=.* \((.*)\)/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];
                }
            }
            if (isset($this->_filecontent[$bi]['state'])) foreach ($this->_filecontent[$bi]['state'] as $roworig) {
                $roworig = trim($roworig);
                if (preg_match('/^remaining capacity:\s*(.*) (.*)$/', $roworig, $data)) {
                    if (!isset($bat['capacity_unit']) || ($bat['capacity_unit'] == trim($data[2]))) {
                        $bat['capacity_unit'] = trim($data[2]);
                        $bat['remaining_capacity'] = $data[1];
                    }
                } elseif (preg_match('/^present voltage:\s*(.*) (.*)$/', $roworig, $data)) {
                    if ($data[2]=="mV") { // uV or mV detection
                        $bat['present_voltage'] = $data[1];
                    } else {
                        $bat['present_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^charging state:\s*(.*)$/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];

                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MIN_DESIGN=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['design_voltage'] = $data[1];
                    } else {
                        $bat['design_voltage'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['design_voltage_max'] = $data[1];
                    } else {
                        $bat['design_voltage_max'] = round($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_ENERGY_FULL=(.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = ($data[1]/1000);
                    if ($data[1]>=1000000000) { // µWh or nWh detection
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "µWh";
                        } elseif ($bat['capacity_unit'] != "µWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    } else {
                        if (!isset($bat['capacity_unit'])) {
                            $bat['capacity_unit'] = "mWh";
                        } elseif ($bat['capacity_unit'] != "mWh") {
                            $bat['capacity_unit'] = "???";
                        }
                    }
                } elseif (preg_match('/^POWER_SUPPLY_CHARGE_FULL=(.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = ($data[1]/1000);
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = "mAh";
                    } elseif ($bat['capacity_unit'] != "mAh") {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^POWER_SUPPLY_ENERGY_NOW=(.*)$/', $roworig, $data)) {
                    if (!isset($bat['capacity_unit']) || ($bat['capacity_unit'] == "mWh")) {
                        $bat['capacity_unit'] = "mWh";
                        $bat['remaining_capacity'] = ($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_CHARGE_NOW=(.*)$/', $roworig, $data)) {
                    if (!isset($bat['capacity_unit']) || ($bat['capacity_unit'] == "mAh")) {
                        $bat['capacity_unit'] = "mAh";
                        $bat['remaining_capacity'] = ($data[1]/1000);
                    }
                } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW=(.*)$/', $roworig, $data)) {
                    if ($data[1]<100000) { // uV or mV detection
                        $bat['present_voltage'] = $data[1];
                    } else {
                        $bat['present_voltage'] = round($data[1]/1000);
                    }

                } elseif (preg_match('/^POWER_SUPPLY_CAPACITY=(.*)$/', $roworig, $data)) {
                    $bat['capacity'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_TEMP=(.*)$/', $roworig, $data)) {
                    $bat['battery_temperature'] = $data[1]/10;
                } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY=(.*)$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_STATUS=(.*)$/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_HEALTH=(.*)$/', $roworig, $data)) {
                    $bat['battery_condition'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_MANUFACTURER=(.*)$/', $roworig, $data)) {
                    $bat['battery_manufacturer'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_NAME=(.*)$/', $roworig, $data)) {
                    $bat['name'] = $data[1];
                }
            }

            if (isset($bat)) $this->_result[$bi] = $bat;
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        foreach ($this->_result as $bat_item) {
            $xmlbat = $this->xml->addChild("Bat");
            if (isset($bat_item['name'])) {
                $xmlbat->addAttribute("Name", $bat_item['name']);
            }
            if ((!isset($bat_item['remaining_capacity']) || (isset($bat_item['full_capacity']) && ($bat_item['full_capacity'] == 0))) &&
                isset($bat_item['capacity']) && ($bat_item['capacity']>=0)) {
                if (isset($bat_item['capacity_unit']) && ($bat_item['capacity_unit'] !== "???")
                   && (isset($bat_item['full_capacity']) && ($bat_item['full_capacity'] > 0))) {
                    $xmlbat->addAttribute("CapacityUnit", $bat_item['capacity_unit']);
                    $xmlbat->addAttribute("RemainingCapacity", round($bat_item['capacity']*$bat_item['full_capacity']/100));
                    $xmlbat->addAttribute("FullCapacity", $bat_item['full_capacity']);
                    if (isset($bat_item['design_capacity'])) {
                        $xmlbat->addAttribute("DesignCapacity", $bat_item['design_capacity']);
                    }
                } else {
                    $xmlbat->addAttribute("FullCapacity", 100);
                    $xmlbat->addAttribute("RemainingCapacity", $bat_item['capacity']);
                    $xmlbat->addAttribute("CapacityUnit", "%");
                }
            } else {
                if (isset($bat_item['full_capacity'])) {
                    if (isset($bat_item['design_capacity'])) {
                        $xmlbat->addAttribute("DesignCapacity", $bat_item['design_capacity']);
                    }
                    $xmlbat->addAttribute("FullCapacity", $bat_item['full_capacity']);
                } elseif (isset($bat_item['design_capacity'])) {
                    $xmlbat->addAttribute("FullCapacity", $bat_item['design_capacity']);
                }
                if (isset($bat_item['remaining_capacity'])) {
                    $xmlbat->addAttribute("RemainingCapacity", $bat_item['remaining_capacity']);
                }
                if (isset($bat_item['capacity_unit'])) {
                    $xmlbat->addAttribute("CapacityUnit", $bat_item['capacity_unit']);
                }
            }
            if (isset($bat_item['design_voltage'])) {
                $xmlbat->addAttribute("DesignVoltage", $bat_item['design_voltage']);
                if (isset($bat_item['design_voltage_max']) && ($bat_item['design_voltage_max'] != $bat_item['design_voltage'])) {
                    $xmlbat->addAttribute("DesignVoltageMax", $bat_item['design_voltage_max']);
                }
            } elseif (isset($bat_item['design_voltage_max'])) {
                $xmlbat->addAttribute("DesignVoltage", $bat_item['design_voltage_max']);
            }
            if (isset($bat_item['present_voltage'])) {
                $xmlbat->addAttribute("PresentVoltage", $bat_item['present_voltage']);
            }
            if (isset($bat_item['charging_state'])) {
                $xmlbat->addAttribute("ChargingState", $bat_item['charging_state']);
            } else {
                if (isset($bat_item['charging_state_i'])) {
                    $xmlbat->addAttribute("ChargingState", 'Charging');
                } elseif (!isset($bat_item['charging_state_e'])) {
                    $xmlbat->addAttribute("ChargingState", 'Discharging');
                } elseif (isset($bat_item['charging_state_f'])) {
                    $xmlbat->addAttribute("ChargingState", 'Fully Charged');
                } else {
                    $xmlbat->addAttribute("ChargingState", 'Unknown state');
                }
            }
            if (isset($bat_item['battery_type'])) {
                $xmlbat->addAttribute("BatteryType", $bat_item['battery_type']);
            }
            if (isset($bat_item['battery_temperature'])) {
                $xmlbat->addAttribute("BatteryTemperature", $bat_item['battery_temperature']);
            }
            if (isset($bat_item['battery_condition'])) {
                $xmlbat->addAttribute("BatteryCondition", $bat_item['battery_condition']);
            }
            if (isset($bat_item['battery_manufacturer'])) {
                $xmlbat->addAttribute("BatteryManufacturer", $bat_item['battery_manufacturer']);
            }
            if (isset($bat_item['cycle_count'])) {
                $xmlbat->addAttribute("CycleCount", $bat_item['cycle_count']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
