<?php
/**
 * speedfan sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.qtssnmp.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting hardware temperature information through snmpwalk
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    William Johansson <radar@radhuset.org>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
        if (CommonFunctions::executeProgram("SpeedFanGet.exe", "", $buffer, PSI_DEBUG, false) && (strlen($buffer) > 0)) {
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
                $dev->setName("Temp".$id);
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
                $dev->setName("Fan".$id);
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
                $dev->setName("In".$id);
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
