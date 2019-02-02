<?php
/**
 * ipmitool sensor class, getting information from ipmitool
 *
 * PHP version 5
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
            CommonFunctions::rfts(PSI_APP_ROOT.'/data/ipmitool.txt', $lines);
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_ipmitool] ACCESS');
            break;
        }
        if (trim($lines) !== "") {
            if (preg_match("/^Sensor ID\s+/", $lines)) { //new data format ('ipmitool sensor -v')
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
            } else {
                $lines = preg_split("/\r?\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                if (count($lines)>0) {
                    $buffer = preg_split("/\s*\|\s*/", $lines[0]);
                    if (count($buffer)>8) { //old data format ('ipmitool sensor')
                        foreach ($lines as $line) {
                            $buffer = preg_split("/\s*\|\s*/", $line);
                            if (count($buffer)>8) {
                                $sens = array();
                                $sens['Sensor'] = $buffer[0];
                                switch ($buffer[2]) {
                                case 'degrees C':
                                    $sens['Value'] = $buffer[1];
                                    $sens['Unit'] = $buffer[2];
                                    $sens['Upper Critical'] = $buffer[8];
                                    $sens['Sensor Type (Threshold)'] = 'Temperature';
                                    break;
                                case 'Volts':
                                    $sens['Value'] = $buffer[1];
                                    $sens['Unit'] = $buffer[2];
                                    $sens['Lower Critical'] = $buffer[5];
                                    $sens['Upper Critical'] = $buffer[8];
                                    $sens['Sensor Type (Threshold)'] = 'Voltage';
                                    break;
                                case 'RPM':
                                    $sens['Value'] = $buffer[1];
                                    $sens['Unit'] = $buffer[2];
                                    $sens['Lower Critical'] = $buffer[5];
                                    $sens['Upper Critical'] = $buffer[8];
                                    $sens['Sensor Type (Threshold)'] = 'Fan';
                                    break;
                                case 'Watts':
                                    $sens['Value'] = $buffer[1];
                                    $sens['Unit'] = $buffer[2];
                                    $sens['Upper Critical'] = $buffer[8];
                                    $sens['Sensor Type (Threshold)'] = 'Current';
                                    break;
                                case 'Amps':
                                    $sens['Value'] = $buffer[1];
                                    $sens['Unit'] = $buffer[2];
                                    $sens['Lower Critical'] = $buffer[5];
                                    $sens['Upper Critical'] = $buffer[8];
                                    $sens['Sensor Type (Threshold)'] = 'Current';
                                    break;
                                case 'discrete':
                                    if (($buffer[1]==='0x0') || ($buffer[1]==='0x1')) {
                                        $sens['State'] = $buffer[1];
                                        $sens['Sensor Type (Discrete)'] = '';
                                        $sens['State'] = $buffer[1];
                                    }
                                    break;
                                }
                                $this->_buf[] = $sens;
                            }
                        }
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
                ||(isset($sensor['Sensor Type (Analog)']) && ($sensor['Sensor Type (Analog)'] == 'Current')))
               && isset($sensor['Unit']) && ($sensor['Unit'] == 'Amps')
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
                if ($sensor['Sensor Type (Discrete)']!=='') {
                    $dev->setName($sensor['Sensor'].' ('.$sensor['Sensor Type (Discrete)'].')');
                } else {
                    $dev->setName($sensor['Sensor']);
                }
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
