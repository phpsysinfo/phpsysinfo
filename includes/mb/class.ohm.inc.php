<?php
/**
 * Open Hardware Monitor sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.ohm.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from Open Hardware Monitor
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class OHM extends Sensors
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
        $_wmi = null;
        // don't set this params for local connection, it will not work
        $strHostname = '';
        $strUser = '';
        $strPassword = '';
        try {
            // initialize the wmi object
            $objLocator = new COM('WbemScripting.SWbemLocator');
            if ($strHostname == "") {
                $_wmi = $objLocator->ConnectServer($strHostname, 'root\OpenHardwareMonitor');

            } else {
                $_wmi = $objLocator->ConnectServer($strHostname, 'root\OpenHardwareMonitor', $strHostname.'\\'.$strUser, $strPassword);
            }
        } catch (Exception $e) {
            $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for OpenHardwareMonitor data.");
        }
        if ($_wmi) {
            $this->_buf = CommonFunctions::getWMI($_wmi, 'Sensor', array('Parent', 'Name', 'SensorType', 'Value'));
        }
     }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        if ($this->_buf) foreach ($this->_buf as $buffer) {
            if ($buffer['SensorType'] == "Temperature") {
                $dev = new SensorDevice();
                $dev->setName($buffer['Parent'].' '.$buffer['Name']);
                $dev->setValue($buffer['Value']);
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
        if ($this->_buf) foreach ($this->_buf as $buffer) {
            if ($buffer['SensorType'] == "Voltage") {
                $dev = new SensorDevice();
                $dev->setName($buffer['Parent'].' '.$buffer['Name']);
                $dev->setValue($buffer['Value']);
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
        if ($this->_buf) foreach ($this->_buf as $buffer) {
            if ($buffer['SensorType'] == "Fan") {
                $dev = new SensorDevice();
                $dev->setName($buffer['Parent'].' '.$buffer['Name']);
                $dev->setValue($buffer['Value']);
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
        if ($this->_buf) foreach ($this->_buf as $buffer) {
            if ($buffer['SensorType'] == "Power") {
                $dev = new SensorDevice();
                $dev->setName($buffer['Parent'].' '.$buffer['Name']);
                $dev->setValue($buffer['Value']);
                $this->mbinfo->setMbPower($dev);
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
    }
}
