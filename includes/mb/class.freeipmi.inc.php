<?php
/**
 * freeipmi sensor class, getting information from ipmi-sensors
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
class FreeIPMI extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_lines = array();

    /**
     * fill the private content var through command or data access
     */
    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_FREEIPMI_ACCESS')?strtolower(PSI_SENSOR_FREEIPMI_ACCESS):'command') {
        case 'command':
            CommonFunctions::executeProgram('ipmi-sensors', '--output-sensor-thresholds', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'data':
            if (CommonFunctions::rfts(PSI_APP_ROOT.'/data/freeipmi.txt', $lines)) {
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_freeipmi] ACCESS');
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
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Temperature" && $buffer[11] != "N/A" && $buffer[4] == "C") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1]);
                $dev->setValue($buffer[3]);
                if ($buffer[9] != "N/A") $dev->setMax($buffer[9]);
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbTemp($dev);
            }
        }
    }

    /**
     * get voltage information
     *
     * @return void
     */
    private function _voltage()
    {
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Voltage" && $buffer[11] != "N/A" && $buffer[4] == "V") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1]);
                $dev->setValue($buffer[3]);
                if ($buffer[6] != "N/A") $dev->setMin($buffer[6]);
                if ($buffer[9] != "N/A") $dev->setMax($buffer[9]);
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbVolt($dev);
            }
        }
    }

    /**
     * get fan information
     *
     * @return void
     */
    private function _fans()
    {
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Fan" && $buffer[11] != "N/A" && $buffer[4] == "RPM") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1]);
                $dev->setValue($buffer[3]);
                if ($buffer[6] != "N/A") {
                    $dev->setMin($buffer[6]);
                } elseif (($buffer[9] != "N/A") && ($buffer[9]<$buffer[3])) { //max instead min issue
                    $dev->setMin($buffer[9]);
                }
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbFan($dev);
            }
        }
    }

    /**
     * get power information
     *
     * @return void
     */
    private function _power()
    {
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Current" && $buffer[11] != "N/A" && $buffer[4] == "W") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1]);
                $dev->setValue($buffer[3]);
                if ($buffer[9] != "N/A") $dev->setMax($buffer[9]);
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbPower($dev);
            }
        }
    }

    /**
     * get current information
     *
     * @return void
     */
    private function _current()
    {
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Current" && $buffer[11] != "N/A" && $buffer[4] == "A") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1]);
                $dev->setValue($buffer[3]);
                if ($buffer[6] != "N/A") $dev->setMin($buffer[6]);
                if ($buffer[9] != "N/A") $dev->setMax($buffer[9]);
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbCurrent($dev);
            }
        }
    }

    /**
     * get other information
     *
     * @return void
     */
    private function _other()
    {
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
             if ($buffer[4] == "N/A"
                && $buffer[2] != "OEM Reserved" && $buffer[11] != "N/A") {
                $dev = new SensorDevice();
                $dev->setName($buffer[1].' ('.$buffer[2].')');
                $dev->setValue(trim($buffer[11], '\''));
                $this->mbinfo->setMbOther($dev);
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
        $this->_voltage();
        $this->_fans();
        $this->_power();
        $this->_current();
        $this->_other();
    }
}
