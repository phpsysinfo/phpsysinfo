<?php
/**
 * Thermal Zone sensor class, getting information from Thermal Zone WMI class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class ThermalZone extends Sensors
{
    /**
     * holds the COM object that we pull all the WMI data from
     *
     * @var Object
     */
    private $_buf = array();

    /**
     * fill the private content var
     */
    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_THERMALZONE_ACCESS')?strtolower(PSI_SENSOR_THERMALZONE_ACCESS):'command') {
        case 'command':
            if ((PSI_OS == 'WINNT') || defined('PSI_EMU_HOSTNAME')) {
                if (defined('PSI_EMU_HOSTNAME') || CommonFunctions::isAdmin()) {
                    $_wmi = CommonFunctions::initWMI('root\WMI', true);
                    if ($_wmi) {
                        $this->_buf = CommonFunctions::getWMI($_wmi, 'MSAcpi_ThermalZoneTemperature', array('InstanceName', 'CriticalTripPoint', 'CurrentTemperature'));
                    }
                } else {
                    $this->error->addError("Error reading data from thermalzone sensor", "Allowed only for systems with administrator privileges (run as administrator)");
                }
            }
            break;
        case 'data':
            if (!defined('PSI_EMU_HOSTNAME') && CommonFunctions::rfts(PSI_APP_ROOT.'/data/thermalzone.txt', $lines, 0, 4096, false)) { //output of "wmic /namespace:\\root\wmi PATH MSAcpi_ThermalZoneTemperature get CriticalTripPoint,CurrentTemperature,InstanceName"
                $lines = trim(preg_replace('/[\x00-\x09\x0b-\x1F]/', '', $lines));
                $lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                if ((($clines=count($lines)) > 1) && preg_match("/CriticalTripPoint\s+CurrentTemperature\s+InstanceName/i", $lines[0])) for ($i = 1; $i < $clines; $i++) {
                    $values = preg_split("/\s+/", trim($lines[$i]), -1, PREG_SPLIT_NO_EMPTY);
                    if (count($values)==3) {
                        $this->_buf[] = array('CriticalTripPoint'=>trim($values[0]), 'CurrentTemperature'=>trim($values[1]), 'InstanceName'=>trim($values[2]));
                    }
                }
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_thermalzone] ACCESS');
            break;
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        $mode = defined('PSI_SENSOR_THERMALZONE_ACCESS')?strtolower(PSI_SENSOR_THERMALZONE_ACCESS):'command';
        if ((($mode == 'command') && ((PSI_OS == 'WINNT') || defined('PSI_EMU_HOSTNAME')))
           || (($mode == 'data') && !defined('PSI_EMU_HOSTNAME'))) {
            if ($this->_buf) foreach ($this->_buf as $buffer) {
                if (isset($buffer['CurrentTemperature']) && (($value = ($buffer['CurrentTemperature'] - 2732)/10) > -100)) {
                    $dev = new SensorDevice();
                    if (isset($buffer['InstanceName']) && preg_match("/([^\\\\ ]+)$/", $buffer['InstanceName'], $outbuf)) {
                        $dev->setName('ThermalZone '.$outbuf[1]);
                    } else {
                        $dev->setName('ThermalZone THM0_0');
                    }
                    $dev->setValue($value);
                    if (isset($buffer['CriticalTripPoint']) && (($maxvalue = ($buffer['CriticalTripPoint'] - 2732)/10) > 0)) {
                        $dev->setMax($maxvalue);
                    }
                    $this->mbinfo->setMbTemp($dev);
                }
            }
        } elseif (($mode == 'command') && (PSI_OS != 'WINNT') && !defined('PSI_EMU_HOSTNAME')) {
            $notwas = true;
            $thermalzones = glob('/sys/class/thermal/thermal_zone*/');
            if (is_array($thermalzones) && (count($thermalzones) > 0)) foreach ($thermalzones as $thermalzone) {
                $thermalzonetemp = $thermalzone.'temp';
                $temp = null;
                if (CommonFunctions::rfts($thermalzonetemp, $temp, 1, 4096, false) && !is_null($temp) && (($temp = trim($temp)) != "")) {
                    if ($temp >= 1000) {
                        $div = 1000;
                    } elseif ($temp >= 200) {
                        $div = 10;
                    } else {
                       $div = 1;
                    }
                    $temp = $temp / $div;

                    if ($temp > -40) {
                        $dev = new SensorDevice();
                        $dev->setValue($temp);

                        $temp_type = null;
                        if (CommonFunctions::rfts($thermalzone.'type', $temp_type, 1, 4096, false) && !is_null($temp_type) && (($temp_type = trim($temp_type)) != "")) {
                            $dev->setName($temp_type);
                        } else {
                            $dev->setName("ThermalZone");
                        }

                        $temp_max = null;
                        if (CommonFunctions::rfts($thermalzone.'trip_point_0_temp', $temp_max, 1, 4096, false) && !is_null($temp_max) && (($temp_max = trim($temp_max)) != "") && ($temp_max > -40)) {
                            $temp_max = $temp_max / $div;
                            if (($temp_max != 0) || ($temp != 0)) { // if non-zero values
                                $dev->setMax($temp_max);
                                $this->mbinfo->setMbTemp($dev);
                            }
                        } else {
                            $this->mbinfo->setMbTemp($dev);
                        }
                        $notwas = false;
                    }
                }
            }
            if ($notwas) {
                $thermalzones = glob('/proc/acpi/thermal_zone/TH*/temperature');
                if (is_array($thermalzones) && (count($thermalzones) > 0)) foreach ($thermalzones as $thermalzone) {
                    $temp = null;
                    if (CommonFunctions::rfts($thermalzone, $temp, 1, 4096, false) && !is_null($temp) && (($temp = trim($temp)) != "")) {
                        $dev = new SensorDevice();
                        if (preg_match("/^\/proc\/acpi\/thermal_zone\/(.+)\/temperature$/", $thermalzone, $name)) {
                           $dev->setName("ThermalZone ".$name[1]);
                        } else {
                            $dev->setName("ThermalZone");
                        }
                        $dev->setValue(trim(substr($temp, 23, 4)));
                        $this->mbinfo->setMbTemp($dev);
                    }
                }
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return Void
     */
    public function build()
    {
      $this->_temperature();
    }
}
