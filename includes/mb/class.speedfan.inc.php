<?php
/**
 * speedfan sensor class, getting hardware information through SpeedFanGet
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2016 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SpeedFan extends Sensors
{
    /*
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = array();

    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_SPEEDFAN_ACCESS')?strtolower(PSI_SENSOR_SPEEDFAN_ACCESS):'command') {
        case 'command':
            if (CommonFunctions::executeProgram("SpeedFanGet.exe", "", $buffer, PSI_DEBUG) && (strlen($buffer) > 0)) {
                if (preg_match("/^Temperatures:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["temp"] = $out[1];
                }
                if (preg_match("/^Fans:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["fans"] = $out[1];
                }
                if (preg_match("/^Voltages:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["volt"] = $out[1];
                }
            }
            break;
        case 'data':
            if (CommonFunctions::rfts(PSI_APP_ROOT.'/data/speedfan.txt', $buffer) && (strlen($buffer) > 0)) {
                if (preg_match("/^Temperatures:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["temp"] = $out[1];
                }
                if (preg_match("/^Fans:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["fans"] = $out[1];
                }
                if (preg_match("/^Voltages:\s+(.+)$/m", $buffer, $out)) {
                    $this->_filecontent["volt"] = $out[1];
                }
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_speedfan] ACCESS');
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
        if (isset($this->_filecontent["temp"]) && (trim($this->_filecontent["temp"]) !== "")) {
            $values = preg_split("/ /", trim($this->_filecontent["temp"]));
            foreach ($values as $id=>$value) {
                $dev = new SensorDevice();
                $dev->setName("temp".$id);
                $dev->setValue($value);
                $this->mbinfo->setMbTemp($dev);
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
        if (isset($this->_filecontent["fans"]) && (trim($this->_filecontent["fans"]) !== "")) {
            $values = preg_split("/ /", trim($this->_filecontent["fans"]));
            foreach ($values as $id=>$value) {
                $dev = new SensorDevice();
                $dev->setName("fan".$id);
                $dev->setValue($value);
                $this->mbinfo->setMbFan($dev);
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
        if (isset($this->_filecontent["volt"]) && (trim($this->_filecontent["volt"]) !== "")) {
            $values = preg_split("/ /", trim($this->_filecontent["volt"]));
            foreach ($values as $id=>$value) {
                $dev = new SensorDevice();
                $dev->setName("in".$id);
                $dev->setValue($value);
                $this->mbinfo->setMbVolt($dev);
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
        $this->_fans();
        $this->_voltage();
    }
}
