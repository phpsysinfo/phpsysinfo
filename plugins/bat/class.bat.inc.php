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
            } else {
                CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/info', $buffer_info);
                CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/state', $buffer_state);
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
            if (preg_match('/^design capacity\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_capacity'] = $data[1];
            } elseif (preg_match('/^design voltage\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_voltage'] = $data[1];
            } elseif (preg_match('/^battery type\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];

            /* Android */
            } elseif (preg_match('/^POWER_SUPPLY_CAPACITY\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['remaining_capacity'] = $data[1];
                $bat['design_capacity'] = '%';
            } elseif (preg_match('/^POWER_SUPPLY_TEMP\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_temperature'] = $data[1]/10;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['present_voltage'] = $data[1]/1000000;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_voltage'] = $data[1]/1000000;
            } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_STATUS\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_HEALTH\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_condition'] = $data[1];
            }
        }
        foreach ($this->_filecontent['state'] as $roworig) {
            if (preg_match('/^remaining capacity\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['remaining_capacity'] = $data[1];
            } elseif (preg_match('/^present voltage\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['present_voltage'] = $data[1];
            } elseif (preg_match('/^charging state\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];

            /* Android */
            } elseif (preg_match('/^POWER_SUPPLY_CAPACITY\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['remaining_capacity'] = $data[1];
                $bat['design_capacity'] = '%';
            } elseif (preg_match('/^POWER_SUPPLY_TEMP\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_temperature'] = $data[1]/10;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_NOW\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['present_voltage'] = $data[1]/1000000;
            } elseif (preg_match('/^POWER_SUPPLY_VOLTAGE_MAX_DESIGN\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_voltage'] = $data[1]/1000000;
            } elseif (preg_match('/^POWER_SUPPLY_TECHNOLOGY\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_type'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_STATUS\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];
            } elseif (preg_match('/^POWER_SUPPLY_HEALTH\s*=\s*(.*)$/m', trim($roworig), $data)) {
                $bat['battery_condition'] = $data[1];
            }
        }

        $this->_result[0] = $bat;
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
