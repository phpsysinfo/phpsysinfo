<?php
/**
 * MBInfo TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.MBInfo.inc.php 253 2009-06-17 13:07:50Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * MBInfo TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class MBInfo
{
    /**
     * array with SensorDevices for temperatures
     *
     * @see SensorDevice
     *
     * @var Array
     */
    private $_mbTemp = array();

    /**
     * array with SensorDevices for fans
     *
     * @see SensorDevice
     *
     * @var Array
     */
    private $_mbFan = array();

    /**
     * array with SensorDevices for voltages
     *
     * @see SensorDevice
     *
     * @var Array
     */
    private $_mbVolt = array();

    /**
     * array with SensorDevices for power
     *
     * @see SensorDevice
     *
     * @var Array
     */
    private $_mbPower = array();

    /**
     * array with SensorDevices for apmers
     *
     * @see SensorDevice
     *
     * @var Array
     */
    private $_mbCurrent = array();

    /**
     * Returns $_mbFan.
     *
     * @see System::$_mbFan
     *
     * @return Array
     */
    public function getMbFan()
    {
        return $this->_mbFan;
    }

    /**
     * Sets $_mbFan.
     *
     * @param SensorDevice $mbFan fan device
     *
     * @see System::$_mbFan
     *
     * @return Void
     */
    public function setMbFan($mbFan)
    {
        array_push($this->_mbFan, $mbFan);
    }

    /**
     * Returns $_mbTemp.
     *
     * @see System::$_mbTemp
     *
     * @return Array
     */
    public function getMbTemp()
    {
        return $this->_mbTemp;
    }

    /**
     * Sets $_mbTemp.
     *
     * @param Sensor $mbTemp temp device
     *
     * @see System::$_mbTemp
     *
     * @return Void
     */
    public function setMbTemp($mbTemp)
    {
        array_push($this->_mbTemp, $mbTemp);
    }

    /**
     * Returns $_mbVolt.
     *
     * @see System::$_mbVolt
     *
     * @return Array
     */
    public function getMbVolt()
    {
        return $this->_mbVolt;
    }

    /**
     * Sets $_mbVolt.
     *
     * @param Sensor $mbVolt voltage device
     *
     * @see System::$_mbVolt
     *
     * @return Void
     */
    public function setMbVolt($mbVolt)
    {
        array_push($this->_mbVolt, $mbVolt);
    }

    /**
     * Returns $_mbPower.
     *
     * @see System::$_mbPower
     *
     * @return Array
     */
    public function getMbPower()
    {
        return $this->_mbPower;
    }

    /**
     * Sets $_mbPower.
     *
     * @param Sensor $mbPower power device
     *
     * @see System::$_mbPower
     *
     * @return Void
     */
    public function setMbPower($mbPower)
    {
        array_push($this->_mbPower, $mbPower);
    }
    /**
     * Returns $_mbCurrent.
     *
     * @see System::$_mbCurrent
     *
     * @return Array
     */
    public function getMbCurrent()
    {
        return $this->_mbCurrent;
    }

    /**
     * Sets $_mbCurrent.
     *
     * @param Sensor $mbCurrent current device
     *
     * @see System::$_mbCurrent
     *
     * @return Void
     */
    public function setMbCurrent($mbCurrent)
    {
        array_push($this->_mbCurrent, $mbCurrent);
    }
}
