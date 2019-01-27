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

                $bufferWB = CommonFunctions::getWMI($_cim, 'Win32_Battery', array('Caption', 'Name', 'EstimatedChargeRemaining', 'DesignVoltage', 'BatteryStatus', 'Chemistry'));
                if (sizeof($bufferWB)>0) {
                    $bufferWPB = CommonFunctions::getWMI($_cim, 'Win32_PortableBattery', array('DesignVoltage', 'Chemistry', 'DesignCapacity', 'FullChargeCapacity', 'Manufacturer'));
                    $bufferBS = CommonFunctions::getWMI($_wmi, 'BatteryStatus', array('RemainingCapacity', 'Voltage'));
                    $bufferBCC = CommonFunctions::getWMI($_wmi, 'BatteryCycleCount', array('CycleCount'));
                    $bufferBFCC = CommonFunctions::getWMI($_wmi, 'BatteryFullChargedCapacity', array('FullChargedCapacity'));
                    $sobWB = sizeof($bufferWB);
                    if (sizeof($bufferWPB) != $sobWB) {
                        $bufferWPB = array();
                    }
                    if (sizeof($bufferBS) != $sobWB) {
                        $bufferBS = array();
                    }
                    if (sizeof($bufferBCC) != $sobWB) {
                        $bufferBCC = array();
                    }
                    if (sizeof($bufferBFCC) != $sobWB) {
                        $bufferBFCC = array();
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
                            $buffer[$bi]['info'] .= 'POWER_SUPPLY_MANUFACTURER='.$bufferWPB[$bi]['Manufacturer']."\n";
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
                            $hasvolt = false;
                            if (isset($bufferBS[$bi]['Voltage']) && ($bufferBS[$bi]['Voltage']>0)) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.($bufferBS[$bi]['Voltage']*1000)."\n";
                                $hasvolt = true;
                            } elseif (isset($bufferWB[$bi]['DesignVoltage']) && ($bufferWB[$bi]['DesignVoltage']>0)) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_VOLTAGE_NOW='.($bufferWB[$bi]['DesignVoltage']*1000)."\n";
                                $hasvolt = true;
                            }
                            if (isset($bufferBS[$bi]['RemainingCapacity']) &&
                               (($bufferBS[$bi]['RemainingCapacity']>0) || ($hasvolt && ($bufferBS[$bi]['RemainingCapacity']==0)))) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_ENERGY_NOW='.($bufferBS[$bi]['RemainingCapacity']*1000)."\n";
                                $capacity = '';
                            }
                        }

                        if (isset($bufferWB[$bi]['Caption'])) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_NAME='.$bufferWB[$bi]['Caption']."\n";
                        }
                        if (isset($bufferWB[$bi]['Name'])) {
                                $buffer[$bi]['state'] .= 'POWER_SUPPLY_MODEL_NAME='.$bufferWB[$bi]['Name']."\n";
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
                $itemcount = 0;
                if ((PSI_OS == 'Linux') && defined('PSI_PLUGIN_BAT_UPOWER') && PSI_PLUGIN_BAT_UPOWER) {
                    $info = '';
                    CommonFunctions::executeProgram('upower', '-d', $info, false);
                    if ($info !== '') {
                        $infoarray = preg_split("/(?=^Device:|^Daemon:)/m", $info);
                        foreach ($infoarray as $infoitem) { //upower detection
                            if (preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\//', $infoitem)
                               && !preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\/line_power/', $infoitem)
                               && !preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\/DisplayDevice/', $infoitem)) {
                               $buffer[$itemcount++]['info'] = $infoitem;
                            }
                        }
                    }
                }
                if ($itemcount == 0) {
                    $batdevices = glob('/proc/acpi/battery/BAT*/info', GLOB_NOSORT);
                    if (is_array($batdevices) && (($total = count($batdevices)) > 0)) {
                        for ($i = 0; $i < $total; $i++) {
                            $infoitem = '';
                            $stateitem = '';
                            $rfts_bi = CommonFunctions::rfts($batdevices[$i], $infoitem, 0, 4096, false);
                            $rfts_bs = CommonFunctions::rfts(preg_replace('/\/info$/', '/state', $batdevices[$i]), $stateitem, 0, 4096, false);
                            if (($rfts_bi && ($infoitem!=='')) || ($rfts_bs && ($stateitem!==''))) {
                                if (preg_match('/^\/proc\/acpi\/battery\/(.+)\/info$/', $batdevices[$i], $batname)) {
                                    if ($infoitem!=='') {
                                        $infoitem .= 'POWER_SUPPLY_NAME='.$batname[1]."\n";
                                    } else {
                                        $stateitem.= 'POWER_SUPPLY_NAME='.$batname[1]."\n";
                                    }
                                }
                                if ($infoitem!=='') {
                                    $buffer[$itemcount]['info'] = $infoitem;
                                }
                                if ($stateitem!=='') {
                                    $buffer[$itemcount]['state'] = $stateitem;
                                }
                                $itemcount++;
                            }
                        }
                    }
                }
                if ($itemcount == 0) {
                    $batdevices = glob('/sys/class/power_supply/[Bb][Aa][Tt]*/present', GLOB_NOSORT);
                    if (is_array($batdevices) && (($total = count($batdevices)) > 0)) {
                        for ($i = 0; $i < $total; $i++) {
                            $pbuffer = '';
                            if (CommonFunctions::rfts($batdevices[$i], $pbuffer, 1, 4096, false) && trim($pbuffer[0]==="1")) {
                                $infoitem = '';
                                $stateitem = '';
                                CommonFunctions::rfts(preg_replace('/\/present$/', '/uevent', $batdevices[$i]), $infoitem, 0, 4096, false);

                                if ((($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/voltage_min_design'))!==null)
                                   || (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/voltage_max'))!==null)) {
                                    $stateitem .= 'POWER_SUPPLY_VOLTAGE_MIN_DESIGN='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/voltage_max_design'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_VOLTAGE_MAX_DESIGN='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/voltage_now'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_VOLTAGE_NOW='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/energy_full'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_ENERGY_FULL='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/energy_now'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_ENERGY_NOW='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/charge_full'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_CHARGE_FULL='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/charge_now'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_CHARGE_NOW='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/capacity'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_CAPACITY='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/technology'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_TECHNOLOGY='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/status'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_STATUS='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/batt_temp'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_TEMP='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/batt_vol'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_VOLTAGE_NOW='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/health'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_HEALTH='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/manufacturer'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_MANUFACTURER='.$buffer1."\n";
                                }
                                if (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/temp'))!==null) {
                                    $stateitem .= 'POWER_SUPPLY_TEMP='.$buffer1."\n";
                                }
                                if (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
                                   && (($buffer1 = CommonFunctions::rolv($batdevices[$i], '/\/present$/', '/serial_number'))!==null)) {
                                    $stateitem .= 'POWER_SUPPLY_SERIAL_NUMBER='.$buffer1."\n";
                                }
                                if (($stateitem!=='') || ($infoitem!=='')) {
                                    if (preg_match('/^\/sys\/class\/power_supply\/(.+)\/present$/', $batdevices[$i], $batname)) {
                                        $stateitem .= 'POWER_SUPPLY_NAME='.$batname[1]."\n";
                                    }
                                    if ($infoitem!=='') {
                                        $buffer[$itemcount]['info'] = $infoitem;
                                    }
                                    if ($stateitem!=='') {
                                        $buffer[$itemcount]['state'] = $stateitem;
                                    }
                                    $itemcount++;
                                }
                            }
                        }
                    }
                }
            }
            break;
        case 'data':
            CommonFunctions::rfts(PSI_APP_ROOT."/data/bat_info.txt", $info);
            $itemcount = 0;
            $infoarray = preg_split("/(?=^Device:|^Daemon:)/m", $info);
            foreach ($infoarray as $infoitem) { //upower detection
                if (preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\//', $infoitem)
                   && !preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\/line_power/', $infoitem)
                   && !preg_match('/^Device: \/org\/freedesktop\/UPower\/devices\/DisplayDevice/', $infoitem)) {
                    $buffer[$itemcount++]['info'] = $infoitem;
                }
            }
            if ($itemcount == 0) {
                $buffer[0]['info'] = $info;
            }
            CommonFunctions::rfts(PSI_APP_ROOT."/data/bat_state.txt", $buffer[0]['state']);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "[bat] ACCESS");
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
            $bat = array();
            if (isset($this->_filecontent[$bi]['info'])) foreach ($this->_filecontent[$bi]['info'] as $roworig) {
                $roworig = trim($roworig);
                if (preg_match('/^[dD]esign capacity:\s*(.*) (.*)$/', $roworig, $data)
                   || preg_match('/^energy-full-design:\s*(.*) (.*)$/', $roworig, $data)) {
                    $bat['design_capacity'] = str_replace(',', '.', $data[1])+0;
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = trim($data[2]);
                    } elseif ($bat['capacity_unit'] != trim($data[2])) {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^[lL]ast full capacity:\s*(.*) (.*)$/', $roworig, $data)
                   || preg_match('/^energy-full:\s*(.*) (.*)$/', $roworig, $data)) {
                    $bat['full_capacity'] = str_replace(',', '.', $data[1])+0;
                    if (!isset($bat['capacity_unit'])) {
                        $bat['capacity_unit'] = trim($data[2]);
                    } elseif ($bat['capacity_unit'] != trim($data[2])) {
                        $bat['capacity_unit'] = "???";
                    }
                } elseif (preg_match('/^energy:\s*(.*) (.*)$/', $roworig, $data)) {
                    $bat['remaining_capacity'] = str_replace(',', '.', $data[1])+0;
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
                } elseif (preg_match('/^battery type:\s*(.*)$/', $roworig, $data)
                         || preg_match('/^technology:\s*(.*)$/', $roworig, $data)) {
                    $bat['battery_type'] = $data[1];
                } elseif (preg_match('/^OEM info:\s*(.*)$/', $roworig, $data)
                         || preg_match('/^vendor:\s*(.*)$/', $roworig, $data)) {
                    $bat['manufacturer'] = $data[1];
                } elseif (preg_match('/^state:\s*(.*)$/', $roworig, $data)) {
                    $bat['charging_state'] = $data[1];
                } elseif (preg_match('/^voltage:\s*(.*) V$/', $roworig, $data)) {
                    $bat['present_voltage'] = str_replace(',', '.', $data[1])*1000;
                } elseif (preg_match('/^percentage:\s*(.*)%$/', $roworig, $data)) {
                    $bat['capacity'] = $data[1];
                } elseif (preg_match('/^Device:\s*\/org\/freedesktop\/UPower\/devices\/([^_]*)_/', $roworig, $data)) {
                    $bat['name'] = $data[1];
                } elseif (preg_match('/^native-path:\s*(.*)$/', $roworig, $data) && isset($data[1][0]) && ($data[1][0]!=='/')) {
                    $bat['name'] = $data[1];
                } elseif (preg_match('/^model:\s*(.*)$/', $roworig, $data)
                         || preg_match('/^[Mm]odel number:\s*(.*)$/', $roworig, $data)) {
                    $bat['model'] = $data[1];
                } elseif (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
                         && (preg_match('/^serial:\s*(.*)$/', $roworig, $data)
                          || preg_match('/^[Ss]erial number:\s*(.*)$/', $roworig, $data))) {
                    $bat['serialnumber'] = $data[1];

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
                    $bat['manufacturer'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_NAME=(.*)$/', $roworig, $data)) {
                    $bat['name'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_MODEL_NAME=(.*)$/', $roworig, $data)) {
                    $bat['model'] = $data[1];
                } elseif (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
                         && preg_match('/^POWER_SUPPLY_SERIAL_NUMBER=(.*)$/', $roworig, $data)) {
                    $bat['serialnumber'] = $data[1];

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
                } elseif (preg_match('/^"DeviceName"\s*=\s*\"?([^\"]*)\"?$/', $roworig, $data)) {
                    $bat['model'] = $data[1];
                } elseif (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
                         && preg_match('/^"BatterySerialNumber"\s*=\s*\"?([^\"]*)\"?$/', $roworig, $data)) {
                    $bat['serialnumber'] = $data[1];

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
                    $bat['manufacturer'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_NAME=(.*)$/', $roworig, $data)) {
                    $bat['name'] = $data[1];
                } elseif (preg_match('/^POWER_SUPPLY_MODEL_NAME=(.*)$/', $roworig, $data)) {
                    $bat['model'] = $data[1];
                } elseif (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
                         && preg_match('/^POWER_SUPPLY_SERIAL_NUMBER=(.*)$/', $roworig, $data)) {
                    $bat['serialnumber'] = $data[1];
                }
            }

            if (sizeof($bat)>0) $this->_result[$bi] = $bat;
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
            if (isset($bat_item['model']) && ($bat_item['model'] !== "1")) {
                $xmlbat->addAttribute("Model", $bat_item['model']);
            }
            if (defined('PSI_PLUGIN_BAT_SHOW_SERIAL') && PSI_PLUGIN_BAT_SHOW_SERIAL
               && isset($bat_item['serialnumber'])
               && ($bat_item['serialnumber'] !== "")
               && ($bat_item['serialnumber'] !== "0")
               && ($bat_item['serialnumber'] !== "0000")) {
                $xmlbat->addAttribute("SerialNumber", $bat_item['serialnumber']);
            }
            if (isset($bat_item['manufacturer'])) {
                $xmlbat->addAttribute("Manufacturer", $bat_item['manufacturer']);
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
            if (isset($bat_item['design_voltage']) && ($bat_item['design_voltage']>0)) {
                $xmlbat->addAttribute("DesignVoltage", $bat_item['design_voltage']);
                if (isset($bat_item['design_voltage_max']) && ($bat_item['design_voltage_max']>0) && ($bat_item['design_voltage_max'] != $bat_item['design_voltage'])) {
                    $xmlbat->addAttribute("DesignVoltageMax", $bat_item['design_voltage_max']);
                }
            } elseif (isset($bat_item['design_voltage_max']) && ($bat_item['design_voltage_max']>0)) {
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
            if (isset($bat_item['cycle_count'])) {
                $xmlbat->addAttribute("CycleCount", $bat_item['cycle_count']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
