<?php
/**
 * Open Hardware Monitor sensor class, getting information from Open Hardware Monitor
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
        try {
            // initialize the wmi object
            $objLocator = new COM('WbemScripting.SWbemLocator');
            $_wmi = $objLocator->ConnectServer('', 'root\OpenHardwareMonitor');
        } catch (Exception $e) {
            $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for OpenHardwareMonitor data.");
        }
        if ($_wmi) {
            $tmpbuf = CommonFunctions::getWMI($_wmi, 'Sensor', array('Parent', 'Name', 'SensorType', 'Value'));
            if ($tmpbuf) foreach ($tmpbuf as $buffer) {
                if (!isset($this->_buf[$buffer['SensorType']]) || !isset($this->_buf[$buffer['SensorType']][$buffer['Parent'].' '.$buffer['Name']])) { // avoid duplicates
                    $this->_buf[$buffer['SensorType']][$buffer['Parent'].' '.$buffer['Name']] = $buffer['Value'];
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
        if (isset($this->_buf['Temperature'])) foreach ($this->_buf['Temperature'] as $name=>$value) {
            $dev = new SensorDevice();
            $dev->setName($name);
            $dev->setValue($value);
            $this->mbinfo->setMbTemp($dev);
        }
    }

    /**
     * get voltage information
     *
     * @return void
     */
    private function _voltage()
    {
        if (isset($this->_buf['Voltage'])) foreach ($this->_buf['Voltage'] as $name=>$value) {
            $dev = new SensorDevice();
            $dev->setName($name);
            $dev->setValue($value);
            $this->mbinfo->setMbVolt($dev);
        }
    }

    /**
     * get fan information
     *
     * @return void
     */
    private function _fans()
    {
        if (isset($this->_buf['Fan'])) foreach ($this->_buf['Fan'] as $name=>$value) {
            $dev = new SensorDevice();
            $dev->setName($name);
            $dev->setValue($value);
            $this->mbinfo->setMbFan($dev);
        }
    }

    /**
     * get power information
     *
     * @return void
     */
    private function _power()
    {
        if (isset($this->_buf['Power'])) foreach ($this->_buf['Power'] as $name=>$value) {
            $dev = new SensorDevice();
            $dev->setName($name);
            $dev->setValue($value);
            $this->mbinfo->setMbPower($dev);
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
