<?php
/**
 * freeipmi sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.freeipmi.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from ipmi-sensors
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
     * fill the private content var through tcp or file access
     */
    public function __construct()
    {
        parent::__construct();
        switch (strtolower(PSI_SENSOR_ACCESS)) {
        case 'command':
            CommonFunctions::executeProgram('ipmi-sensors', '--output-sensor-thresholds', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'file':
            if (CommonFunctions::rfts(APP_ROOT.'/data/freeipmi.txt', $lines)) {
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_ACCESS');
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
                if ($buffer[9] != "N/A") $dev->setMax($buffer[9]);
                if ($buffer[11] != "'OK'") $dev->setEvent(trim($buffer[11], "'"));
                $this->mbinfo->setMbCurrent($dev);
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
    }
}
