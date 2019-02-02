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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
     * @var array
     */
    private $_mbTemp = array();

    /**
     * array with SensorDevices for fans
     *
     * @see SensorDevice
     *
     * @var array
     */
    private $_mbFan = array();

    /**
     * array with SensorDevices for voltages
     *
     * @see SensorDevice
     *
     * @var array
     */
    private $_mbVolt = array();

    /**
     * array with SensorDevices for power
     *
     * @see SensorDevice
     *
     * @var array
     */
    private $_mbPower = array();

    /**
     * array with SensorDevices for apmers
     *
     * @see SensorDevice
     *
     * @var array
     */
    private $_mbCurrent = array();

    /**
     * array with SensorDevices for other
     *
     * @see SensorDevice
     *
     * @var array
     */
    private $_mbOther = array();

    /**
     * Returns $_mbFan.
     *
     * @see System::$_mbFan
     *
     * @return array
     */
    public function getMbFan()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbFan, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbFan;
    }

    /**
     * Sets $_mbFan.
     *
     * @param SensorDevice $mbFan fan device
     *
     * @see System::$_mbFan
     *
     * @return void
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
     * @return array
     */
    public function getMbTemp()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbTemp, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbTemp;
    }

    /**
     * Sets $_mbTemp.
     *
     * @param SensorDevice $mbTemp temp device
     *
     * @see System::$_mbTemp
     *
     * @return void
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
     * @return array
     */
    public function getMbVolt()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbVolt, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbVolt;
    }

    /**
     * Sets $_mbVolt.
     *
     * @param SensorDevice $mbVolt voltage device
     *
     * @see System::$_mbVolt
     *
     * @return void
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
     * @return array
     */
    public function getMbPower()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbPower, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbPower;
    }

    /**
     * Sets $_mbPower.
     *
     * @param SensorDevice $mbPower power device
     *
     * @see System::$_mbPower
     *
     * @return void
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
     * @return array
     */
    public function getMbCurrent()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbCurrent, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbCurrent;
    }

    /**
     * Sets $_mbCurrent.
     *
     * @param SensorDevice $mbCurrent current device
     *
     * @see System::$_mbCurrent
     *
     * @return void
     */
    public function setMbCurrent($mbCurrent)
    {
        array_push($this->_mbCurrent, $mbCurrent);
    }

    /**
     * Returns $_mbOther.
     *
     * @see System::$_mbOther
     *
     * @return array
     */
    public function getMbOther()
    {
        if (defined('PSI_SORT_SENSORS_LIST') && PSI_SORT_SENSORS_LIST) {
            usort($this->_mbOther, array('CommonFunctions', 'name_natural_compare'));
        }

        return $this->_mbOther;
    }

    /**
     * Sets $_mbOther.
     *
     * @param SensorDevice $mbOther other device
     *
     * @see System::$_mbOther
     *
     * @return void
     */
    public function setMbOther($mbOther)
    {
        array_push($this->_mbOther, $mbOther);
    }
}
