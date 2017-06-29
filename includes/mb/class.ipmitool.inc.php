<?php
/**
 * ipmitool sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.ipmitool.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from ipmitool
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class IPMItool extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_buf = array();

    /**
     * fill the private content var through command or data access
     */
    public function __construct()
    {
        parent::__construct();
        $lines = "";
        switch (defined('PSI_SENSOR_IPMITOOL_ACCESS')?strtolower(PSI_SENSOR_IPMITOOL_ACCESS):'command') {
        case 'command':
            CommonFunctions::executeProgram('ipmitool', 'sensor -v', $lines);
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT.'/data/ipmitool.txt', $lines);
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_IPMITOOL_ACCESS');
            break;
        }
        if (trim($lines) !== "") {
            $lines = preg_replace("/\n?Unable to read sensor/", "\nUnable to read sensor", $lines);
            $sensors = preg_split("/Sensor ID\s+/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sensors as $sensor) {
                if (preg_match("/^:\s*(.+)\s\((0x[a-f\d]+)\)\r?\n/", $sensor, $name) && (($name1 = trim($name[1])) !== "")) {
                    $sensorvalues = preg_split("/\r?\n/", $sensor, -1, PREG_SPLIT_NO_EMPTY);
                    unset($sensorvalues[0]); //skip first
                    $sens = array();
                    $was = false;
                    foreach ($sensorvalues as $sensorvalue) {
                        if (preg_match("/^\s+\[(.+)\]$/", $sensorvalue, $buffer) && (($buffer1 = trim($buffer[1])) !== "")) {
                            if (isset($sens['State'])) {
                                $sens['State'] .= ', '.$buffer1;
                            } else {
                                $sens['State'] = $buffer1;
                            }
                            $was = true;
                        } elseif (preg_match("/^([^:]+):(.+)$/", $sensorvalue, $buffer)
                                && (($buffer1 = trim($buffer[1])) !== "")
                                && (($buffer2 = trim($buffer[2])) !== "")) {
                            $sens[$buffer1] = $buffer2;
                            $was = true;
                        }
                    }
                    if ($was  && !isset($sens['Unable to read sensor'])) {
                        $sens['Sensor'] = $name1;
                        if (isset($sens['Sensor Reading'])
                            && preg_match("/^([\d\.]+)\s+\([^\)]*\)\s+(.+)$/", $sens['Sensor Reading'], $buffer)
                            && (($buffer2 = trim($buffer[2])) !== "")) {
                            $sens['Value'] = $buffer[1];
                            $sens['Unit'] = $buffer2;
                        }
                        $this->_buf[intval($name[2], 0)] = $sens;
                    }
                }
            }
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        foreach ($this->_buf as $sensor) {
            if (((isset($sensor['Sensor Type (Threshold)']) && ($sensor['Sensor Type (Threshold)'] == 'Temperature'))
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Temperature')))
               && isset($sensor['Unit']) && ($sensor['Unit'] == 'degrees C')
               && isset($sensor['Value'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor']);
                $dev->setValue($sensor['Value']);
                if (isset($sensor['Upper Critical']) && (($max = $sensor['Upper Critical']) != "na")) {
                    $dev->setMax($max);
                }
                if (isset($sensor['Status']) && (($status = $sensor['Status']) != "ok")) {
                    $dev->setEvent($status);
                }
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
        foreach ($this->_buf as $sensor) {
            if (((isset($sensor['Sensor Type (Threshold)']) && ($sensor['Sensor Type (Threshold)'] == 'Voltage'))
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Voltage')))
               && isset($sensor['Unit']) && ($sensor['Unit'] == 'Volts')
               && isset($sensor['Value'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor']);
                $dev->setValue($sensor['Value']);
                if (isset($sensor['Upper Critical']) && (($max = $sensor['Upper Critical']) != "na")) {
                    $dev->setMax($max);
                }
                if (isset($sensor['Lower Critical']) && (($min = $sensor['Lower Critical']) != "na")) {
                    $dev->setMin($min);
                }
                if (isset($sensor['Status']) && (($status = $sensor['Status']) != "ok")) {
                    $dev->setEvent($status);
                }
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
        foreach ($this->_buf as $sensor) {
            if (((isset($sensor['Sensor Type (Threshold)']) && ($sensor['Sensor Type (Threshold)'] == 'Fan'))
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Fan')))
               && isset($sensor['Unit']) && ($sensor['Unit'] == 'RPM')
               && isset($sensor['Value'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor']);
                $dev->setValue($value = $sensor['Value']);
                if (isset($sensor['Lower Critical']) && (($min = $sensor['Lower Critical']) != "na")) {
                    $dev->setMin($min);
                } elseif (isset($sensor['Upper Critical']) && (($max = $sensor['Upper Critical']) != "na")
                          && ($max < $value)) { // max instead min issue
                    $dev->setMin($max);
                }
                if (isset($sensor['Status']) && (($status = $sensor['Status']) != "ok")) {
                    $dev->setEvent($status);
                }
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
        foreach ($this->_buf as $sensor) {
            if (((isset($sensor['Sensor Type (Threshold)']) && ($sensor['Sensor Type (Threshold)'] == 'Current'))
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Current')))
               && isset($sensor['Unit']) && ($sensor['Unit'] == 'Watts')
               && isset($sensor['Value'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor']);
                $dev->setValue($sensor['Value']);
                if (isset($sensor['Upper Critical']) && (($max = $sensor['Upper Critical']) != "na")) {
                    $dev->setMax($max);
                }
                if (isset($sensor['Status']) && (($status = $sensor['Status']) != "ok")) {
                    $dev->setEvent($status);
                }
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
        foreach ($this->_buf as $sensor) {
            if (((isset($sensor['Sensor Type (Threshold)']) && ($sensor['Sensor Type (Threshold)'] == 'Current'))
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Current')))               && isset($sensor['Unit']) && ($sensor['Unit'] == 'Amps')
               && isset($sensor['Value'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor']);
                $dev->setValue($sensor['Value']);
                if (isset($sensor['Upper Critical']) && (($max = $sensor['Upper Critical']) != "na")) {
                    $dev->setMax($max);
                }
                if (isset($sensor['Lower Critical']) && (($min = $sensor['Lower Critical']) != "na")) {
                    $dev->setMin($min);
                }
                if (isset($sensor['Status']) && (($status = $sensor['Status']) != "ok")) {
                    $dev->setEvent($status);
                }
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
        foreach ($this->_buf as $sensor) {
            if (isset($sensor['Sensor Type (Discrete)'])) {
                $dev = new SensorDevice();
                $dev->setName($sensor['Sensor'].' ('.$sensor['Sensor Type (Discrete)'].')');
                if (isset($sensor['State'])) {
                    $dev->setValue($sensor['State']);
                } else {
                    $dev->setValue('0x0');
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
