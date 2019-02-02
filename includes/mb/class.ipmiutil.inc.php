<?php
/**
 * ipmiutil sensor class, getting information from ipmi-sensors
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
class IPMIutil extends Sensors
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
        switch (defined('PSI_SENSOR_IPMIUTIL_ACCESS')?strtolower(PSI_SENSOR_IPMIUTIL_ACCESS):'command') {
        case 'command':
            CommonFunctions::executeProgram('ipmiutil', 'sensor -stw', $lines);
            $this->_lines = preg_split("/\r?\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'data':
            if (CommonFunctions::rfts(PSI_APP_ROOT.'/data/ipmiutil.txt', $lines)) {
                $this->_lines = preg_split("/\r?\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_ipmiutil] ACCESS');
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
            if (isset($buffer[2]) && $buffer[2] == "Temperature"
               && $buffer[1] == "Full"
               && isset($buffer[6]) && preg_match("/^(\S+)\sC$/", $buffer[6], $value)
               && $buffer[5] !== "Init") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4]);
                $dev->setValue($value[1]);
                if (isset($buffer[7]) && $buffer[7] == "Thresholds") {
                    if ((isset($buffer[8]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMax($limits[1]);
                    }
                }
                if ($buffer[5] != "OK") $dev->setEvent($buffer[5]);
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
            if (isset($buffer[2]) && $buffer[2] == "Voltage"
               && $buffer[1] == "Full"
               && isset($buffer[6]) && preg_match("/^(\S+)\sV$/", $buffer[6], $value)
               && $buffer[5] !== "Init") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4]);
                $dev->setValue($value[1]);
                if (isset($buffer[7]) && $buffer[7] == "Thresholds") {
                    if ((isset($buffer[8]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMin($limits[1]);
                    }
                    if ((isset($buffer[8]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMax($limits[1]);
                    }
                }
                if ($buffer[5] != "OK") $dev->setEvent($buffer[5]);
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
            if (isset($buffer[2]) && $buffer[2] == "Fan"
               && $buffer[1] == "Full"
               && isset($buffer[6]) && preg_match("/^(\S+)\sRPM$/", $buffer[6], $value)
               && $buffer[5] !== "Init") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4]);
                $dev->setValue($value[1]);
                if (isset($buffer[7]) && $buffer[7] == "Thresholds") {
                    if ((isset($buffer[8]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMin($limits[1]);
                    } elseif ((isset($buffer[8]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        if ($limits[1]<$value[1]) {//max instead min issue
                            $dev->setMin($limits[1]);
                        }
                    }
                }
                if ($buffer[5] != "OK") $dev->setEvent($buffer[5]);
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
            if (isset($buffer[2]) && $buffer[2] == "Current"
               && $buffer[1] == "Full"
               && isset($buffer[6]) && preg_match("/^(\S+)\sW$/", $buffer[6], $value)
               && $buffer[5] !== "Init") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4]);
                $dev->setValue($value[1]);
                if (isset($buffer[7]) && $buffer[7] == "Thresholds") {
                    if ((isset($buffer[8]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMax($limits[1]);
                    }
                }
                if ($buffer[5] != "OK") $dev->setEvent($buffer[5]);
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
            if (isset($buffer[2]) && $buffer[2] == "Current"
               && $buffer[1] == "Full"
               && isset($buffer[6]) && preg_match("/^(\S+)\sA$/", $buffer[6], $value)
               && $buffer[5] !== "Init") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4]);
                $dev->setValue($value[1]);
                if (isset($buffer[7]) && $buffer[7] == "Thresholds") {
                    if ((isset($buffer[8]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^lo-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMin($limits[1]);
                    }
                    if ((isset($buffer[8]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[8], $limits))
                        ||(isset($buffer[9]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[9], $limits))
                        ||(isset($buffer[10]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[10], $limits))
                        ||(isset($buffer[11]) && preg_match("/^hi-crit\s(\S+)\s*$/", $buffer[11], $limits))) {
                        $dev->setMax($limits[1]);
                    }
                }
                if ($buffer[5] != "OK") $dev->setEvent($buffer[5]);
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
            if (isset($buffer[1]) && $buffer[1] == "Compact"
               && $buffer[5] !== "Init"
               && $buffer[5] !== "Unknown"
               && $buffer[5] !== "NotAvailable") {
                $dev = new SensorDevice();
                $dev->setName($buffer[4].' ('.$buffer[2].')');

                $buffer5s = preg_split("/\s+/", $buffer5 = $buffer[5]);
                if (isset($buffer5s[1])) {
                    $value = hexdec($buffer5s[0]) & 0xff;
                    if ($buffer5s[1] === 'DiscreteEvt') {
                        $dev->setValue('0x'.dechex($value));
                    } elseif (($buffer5s[1] === 'DiscreteUnit') && ($value > 0)) {
                        $dev->setValue('0x'.dechex($value - 1));
                    } else {
                        $dev->setValue($buffer5);
                    }
                } else {
                    $dev->setValue($buffer5);
                }
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
