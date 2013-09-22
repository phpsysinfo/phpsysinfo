<?php
 /**
 * BAT Plugin, which displays battery state
 *
 * @category  PHP
 * @package   PSI_Plugin_BAT
 * @author    Erkan V
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
     * holds the COM object that we pull all the WMI data from
     *
     * @var Object
     */
    private $_wmi = null;
    
    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        switch (strtolower(PSI_PLUGIN_BAT_ACCESS)) {
        case 'command':
            if (PSI_OS == 'Android') {
                CommonFunctions::rfts('/sys/class/power_supply/battery/uevent', $buffer_info);
                $buffer_state = '';
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/capacity', $buffer1, 1, 4096, false)) {
                    $buffer_state .= 'POWER_SUPPLY_CAPACITY='.$buffer1;
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/batt_temp', $buffer1, 1, 4096, false)) {
                    $buffer_state .= 'POWER_SUPPLY_TEMP='.$buffer1;
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/batt_vol', $buffer1, 1, 4096, false)) {
                   $buffer_state .= 'POWER_SUPPLY_VOLTAGE_NOW='.($buffer1*1000)."\n";
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/voltage_max_design', $buffer1, 1, 4096, false)) {
                   $buffer_state .= 'POWER_SUPPLY_VOLTAGE_MAX_DESIGN='.($buffer1*1000)."\n";
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/technology', $buffer1, 1, 4096, false)) {
                    $buffer_state .= 'POWER_SUPPLY_TECHNOLOGY='.$buffer1;
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/status', $buffer1, 1, 4096, false)) {
                    $buffer_state .= 'POWER_SUPPLY_STATUS='.$buffer1;
                }
                if (CommonFunctions::rfts('/sys/class/power_supply/battery/health', $buffer1, 1, 4096, false)) {
                    $buffer_state .= 'POWER_SUPPLY_HEALTH='.$buffer1;
                }
            } elseif (PSI_OS == 'WINNT') {
                // don't set this params for local connection, it will not work
                $strHostname = '';
                $strUser = '';
                $strPassword = '';
                try {
                    // initialize the wmi object
                    $objLocator = new COM('WbemScripting.SWbemLocator');
                    if ($strHostname == "") {
                        $this->_wmi = $objLocator->ConnectServer();

                    } else {
                        $this->_wmi = $objLocator->ConnectServer($strHostname, 'rootcimv2', $strHostname.'\\'.$strUser, $strPassword);
                    }
                } catch (Exception $e) {
                    $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
                }
                $buffer_info = '';
                $buffer_state = '';
                $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_Battery', array('EstimatedChargeRemaining', 'DesignVoltage', 'BatteryStatus', 'Chemistry'));
                $capacity = '';
                if (isset($buffer[0]['EstimatedChargeRemaining'])) {
                    $capacity = $buffer[0]['EstimatedChargeRemaining'];
                }
                if (isset($buffer[0]['DesignVoltage'])) {
                    $buffer_state .= 'POWER_SUPPLY_VOLTAGE_NOW='.(1000*$buffer[0]['DesignVoltage'])."\n";
                }
                if (isset($buffer[0]['BatteryStatus'])) {
                    switch ($buffer[0]['BatteryStatus']) {
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
                    if ($batstat != '') $buffer_state .= 'POWER_SUPPLY_STATUS='.$batstat."\n";
                }
                $techn = '';
                if (isset($buffer[0]['Chemistry'])) {
                    switch ($buffer[0]['Chemistry']) {
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
                $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_PortableBattery', array('DesignVoltage', 'Chemistry', 'DesignCapacity'));
                if (isset($buffer[0]['DesignVoltage'])) {
                    $buffer_info .= 'POWER_SUPPLY_VOLTAGE_MAX_DESIGN='.(1000*$buffer[0]['DesignVoltage'])."\n";
                }
                // sometimes Chemistry from Win32_Battery returns 2 but Win32_PortableBattery returns e.g. 6
                if ((($techn == '') || ($techn == 'Unknown')) && isset($buffer[0]['Chemistry'])) {
                    switch ($buffer[0]['Chemistry']) {
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
                if ($techn != '') $buffer_info .= 'POWER_SUPPLY_TECHNOLOGY='.$techn."\n";
                if (isset($buffer[0]['DesignCapacity'])) {
                    $buffer_info .= 'design capacity:'.$buffer[0]['DesignCapacity']." mWh\n";
                    if ($capacity != '') $buffer_state .= 'remaining capacity:'.round($capacity*$buffer[0]['DesignCapacity']/100)." mWh\n";
                } else {
                    if ($capacity != '') $buffer_state .= 'POWER_SUPPLY_CAPACITY='.$capacity."\n";
                }
            } else {
                $rfts_bi = CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/info', $buffer_info, 0, 4096, false);
                $rfts_bs = CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/state', $buffer_state, 0, 4096, false);
                if (!$rfts_bi && !$rfts_bs) {
                    CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/uevent', $buffer_info);
                    $buffer_state = '';
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/voltage_min_design', $buffer1, 1, 4096, false)) {
                       $buffer_state .= 'POWER_SUPPLY_VOLTAGE_MIN_DESIGN='.$buffer1."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/voltage_now', $buffer1, 1, 4096, false)) {
                       $buffer_state .= 'POWER_SUPPLY_VOLTAGE_NOW='.$buffer1."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/energy_full', $buffer1, 1, 4096, false)) {
                       $buffer_state .= 'POWER_SUPPLY_ENERGY_FULL='.$buffer1."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/energy_now', $buffer1, 1, 4096, false)) {
                       $buffer_state .= 'POWER_SUPPLY_ENERGY_NOW='.$buffer1."\n";
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/capacity', $buffer1, 1, 4096, false)) {
                        $buffer_state .= 'POWER_SUPPLY_CAPACITY='.$buffer1;
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/technology', $buffer1, 1, 4096, false)) {
                        $buffer_state .= 'POWER_SUPPLY_TECHNOLOGY='.$buffer1;
                    }
                    if (CommonFunctions::rfts('/sys/class/power_supply/'.PSI_PLUGIN_BAT_DEVICE.'/status', $buffer1, 1, 4096, false)) {
                        $buffer_state .= 'POWER_SUPPLY_STATUS='.$buffer1;
                    }
                }
            }
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/bat_info.txt", $buffer_info);
            CommonFunctions::rfts(APP_ROOT."/data/bat_state.txt", $buffer_state);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_BAT_ACCESS");
            break;
        }
        $this->_filecontent['info'] = preg_split("/\n/", $buffer_info, -1, PREG_SPLIT_NO_EMPTY);
        $this->_filecontent['state'] = preg_split("/\n/", $buffer_state, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array
     *
     * @return void
     */
    public function execute()
    {
        if ( empty($this->_filecontent)) {
            return;
        }
        foreach ($this->_filecontent['info'] as $roworig) {
            if (preg_match('/^design capacity\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_capacity'] = $data[1];
            } elseif (preg_match('/^design voltage\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_voltage'] = $data[1];
            } elseif (preg_match('/^battery type\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];

            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MIN_DESIGN\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_ENERGY_FULL\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_capacity'] = ($data[1]/1000).' mWh';
            } elseif (preg_match('/^POWER_SUPPLY_ENERGY_NOW\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['remaining_capacity'] = ($data[1]/1000).' mWh';

            /* Android */
            } elseif (preg_match('/^POWER_SUPPLY_CAPACITY\s*=\s*(.*)$/', trim($roworig), $data) && !isset($bat['remaining_capacity'])) {
                $bat['remaining_capacity'] = $data[1];
                $bat['design_capacity'] = '%';
            } elseif (preg_match('/^POWER_SUPPLY_TEMP\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_temperature'] = $data[1]/10;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['present_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_STATUS\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_HEALTH\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_condition'] = $data[1];
            }
        }
        foreach ($this->_filecontent['state'] as $roworig) {
            if (preg_match('/^remaining capacity\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['remaining_capacity'] = $data[1];
            } elseif (preg_match('/^present voltage\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['present_voltage'] = $data[1];
            } elseif (preg_match('/^charging state\s*:\s*(.*)$/', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];

            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MIN_DESIGN\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_ENERGY_FULL\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_capacity'] = ($data[1]/1000).' mWh';
            } elseif (preg_match('/^POWER_SUPPLY_ENERGY_NOW\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['remaining_capacity'] = ($data[1]/1000).' mWh';

            /* Android */
            } elseif (preg_match('/^POWER_SUPPLY_CAPACITY\s*=\s*(.*)$/', trim($roworig), $data) && !isset($bat['remaining_capacity'])) {
                $bat['remaining_capacity'] = $data[1];
                $bat['design_capacity'] = '%';
            } elseif (preg_match('/^POWER_SUPPLY_TEMP\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_temperature'] = $data[1]/10;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['present_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['design_voltage'] = ($data[1]/1000).' mV';
            } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_STATUS\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_HEALTH\s*=\s*(.*)$/', trim($roworig), $data)) {
                $bat['battery_condition'] = $data[1];
            }
        }

        if (isset($bat)) $this->_result[0] = $bat;
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
            if (isset($bat_item['design_capacity'])) {
                $xmlbat->addAttribute("DesignCapacity", $bat_item['design_capacity']);
            }
            if (isset($bat_item['design_voltage'])) {
                $xmlbat->addAttribute("DesignVoltage", $bat_item['design_voltage']);
            }
            if (isset($bat_item['remaining_capacity'])) {
                $xmlbat->addAttribute("RemainingCapacity", $bat_item['remaining_capacity']);
            }
            if (isset($bat_item['present_voltage'])) {
                $xmlbat->addAttribute("PresentVoltage", $bat_item['present_voltage']);
            }
            if (isset($bat_item['charging_state'])) {
                $xmlbat->addAttribute("ChargingState", $bat_item['charging_state']);
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
        }

        return $this->xml->getSimpleXmlElement();
    }
}
