<?php
/**
 * UPSDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.UPSDevice.inc.php 262 2009-06-22 10:48:33Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * UPSDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class UPSDevice
{
    /**
     * name of the ups
     *
     * @var string
     */
    private $_name = "";

    /**
     * model of the ups
     *
     * @var string
     */
    private $_model = "";

    /**
     * mode of the ups
     *
     * @var string
     */
    private $_mode = "";

    /**
     * last start time
     *
     * @var string
     */
    private $_startTime = "";

    /**
     * status of the ups
     *
     * @var string
     */
    private $_status = "";

    /**
     * temperature of the ups
     *
     * @var int
     */
    private $_temperatur = null;

    /**
     * outages count
     *
     * @var int
     */
    private $_outages = null;

    /**
     * date of last outtage
     *
     * @var string
     */
    private $_lastOutage = null;

    /**
     * date of last outage finish
     *
     * @var string
     */
    private $_lastOutageFinish = null;

    /**
     * line volt
     *
     * @var int
     */
    private $_lineVoltage = null;

    /**
     * line freq
     *
     * @var int
     */
    private $_lineFrequency = null;

    /**
     * current load of the ups in percent
     *
     * @var int
     */
    private $_load = null;

    /**
     * battery installation date
     *
     * @var string
     */
    private $_batteryDate = null;

    /**
     * current battery volt
     *
     * @var int
     */
    private $_batteryVoltage = null;

    /**
     * current charge in percent of the battery
     *
     * @var int
     */
    private $_batterCharge = null;

    /**
     * time left
     *
     * @var string
     */
    private $_timeLeft = null;

    /**
     * beeper enabled or disabled
     *
     * @var string
     */
    private $_beeperStatus = null;

    /**
     * Returns $_batterCharge.
     *
     * @see UPSDevice::$_batterCharge
     *
     * @return integer
     */
    public function getBatterCharge()
    {
        return $this->_batterCharge;
    }

    /**
     * Sets $_batterCharge.
     *
     * @param int $batterCharge battery charge
     *
     * @see UPSDevice::$_batterCharge
     *
     * @return void
     */
    public function setBatterCharge($batterCharge)
    {
        $this->_batterCharge = $batterCharge;
    }

    /**
     * Returns $_batteryDate.
     *
     * @see UPSDevice::$_batteryDate
     *
     * @return String
     */
    public function getBatteryDate()
    {
        return $this->_batteryDate;
    }

    /**
     * Sets $_batteryDate.
     *
     * @param object $batteryDate battery date
     *
     * @see UPSDevice::$_batteryDate
     *
     * @return void
     */
    public function setBatteryDate($batteryDate)
    {
        $this->_batteryDate = $batteryDate;
    }

    /**
     * Returns $_batteryVoltage.
     *
     * @see UPSDevice::$_batteryVoltage
     *
     * @return int
     */
    public function getBatteryVoltage()
    {
        return $this->_batteryVoltage;
    }

    /**
     * Sets $_batteryVoltage.
     *
     * @param int $batteryVoltage battery volt
     *
     * @see UPSDevice::$_batteryVoltage
     *
     * @return void
     */
    public function setBatteryVoltage($batteryVoltage)
    {
        $this->_batteryVoltage = $batteryVoltage;
    }

    /**
     * Returns $_lastOutage.
     *
     * @see UPSDevice::$_lastOutage
     *
     * @return String
     */
    public function getLastOutage()
    {
        return $this->_lastOutage;
    }

    /**
     * Sets $_lastOutage.
     *
     * @param String $lastOutage last Outage
     *
     * @see UPSDevice::$lastOutage
     *
     * @return void
     */
    public function setLastOutage($lastOutage)
    {
        $this->_lastOutage = $lastOutage;
    }

    /**
     * Returns $_lastOutageFinish.
     *
     * @see UPSDevice::$_lastOutageFinish
     *
     * @return String
     */
    public function getLastOutageFinish()
    {
        return $this->_lastOutageFinish;
    }

    /**
     * Sets $_lastOutageFinish.
     *
     * @param String $lastOutageFinish last outage finish
     *
     * @see UPSDevice::$_lastOutageFinish
     *
     * @return void
     */
    public function setLastOutageFinish($lastOutageFinish)
    {
        $this->_lastOutageFinish = $lastOutageFinish;
    }

    /**
     * Returns $_lineVoltage.
     *
     * @see UPSDevice::$_lineVoltage
     *
     * @return int
     */
    public function getLineVoltage()
    {
        return $this->_lineVoltage;
    }

    /**
     * Sets $_lineVoltage.
     *
     * @param int $lineVoltage line voltage
     *
     * @see UPSDevice::$_lineVoltage
     *
     * @return void
     */
    public function setLineVoltage($lineVoltage)
    {
        $this->_lineVoltage = $lineVoltage;
    }

    /**
     * Returns $_lineFrequency.
     *
     * @see UPSDevice::$_lineFrequency
     *
     * @return int
     */
    public function getLineFrequency()
    {
        return $this->_lineFrequency;
    }

    /**
     * Sets $_lineFrequency.
     *
     * @param int $lineFrequency line frequency
     *
     * @see UPSDevice::$_lineFrequency
     *
     * @return void
     */
    public function setLineFrequency($lineFrequency)
    {
        $this->_lineFrequency = $lineFrequency;
    }

    /**
     * Returns $_load.
     *
     * @see UPSDevice::$_load
     *
     * @return int
     */
    public function getLoad()
    {
        return $this->_load;
    }

    /**
     * Sets $_load.
     *
     * @param int $load current load
     *
     * @see UPSDevice::$_load
     *
     * @return void
     */
    public function setLoad($load)
    {
        $this->_load = $load;
    }

    /**
     * Returns $_mode.
     *
     * @see UPSDevice::$_mode
     *
     * @return String
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Sets $_mode.
     *
     * @param String $mode mode
     *
     * @see UPSDevice::$_mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    /**
     * Returns $_model.
     *
     * @see UPSDevice::$_model
     *
     * @return String
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Sets $_model.
     *
     * @param String $model model
     *
     * @see UPSDevice::$_model
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Returns $_name.
     *
     * @see UPSDevice::$_name
     *
     * @return String
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets $_name.
     *
     * @param String $name name
     *
     * @see UPSDevice::$_name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns $_outages.
     *
     * @see UPSDevice::$_outages
     *
     * @return int
     */
    public function getOutages()
    {
        return $this->_outages;
    }

    /**
     * Sets $_outages.
     *
     * @param int $outages outages count
     *
     * @see UPSDevice::$_outages
     *
     * @return void
     */
    public function setOutages($outages)
    {
        $this->_outages = $outages;
    }

    /**
     * Returns $_startTime.
     *
     * @see UPSDevice::$_startTime
     *
     * @return String
     */
    public function getStartTime()
    {
        return $this->_startTime;
    }

    /**
     * Sets $_startTime.
     *
     * @param String $startTime startTime
     *
     * @see UPSDevice::$_startTime
     *
     * @return void
     */
    public function setStartTime($startTime)
    {
        $this->_startTime = $startTime;
    }

    /**
     * Returns $_status.
     *
     * @see UPSDevice::$_status
     *
     * @return String
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Sets $_status.
     *
     * @param String $status status
     *
     * @see UPSDevice::$_status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * Returns $_temperatur.
     *
     * @see UPSDevice::$_temperatur
     *
     * @return int
     */
    public function getTemperatur()
    {
        return $this->_temperatur;
    }

    /**
     * Sets $_temperatur.
     *
     * @param int $temperatur temperature
     *
     * @see UPSDevice::$_temperatur
     *
     * @return void
     */
    public function setTemperatur($temperatur)
    {
        $this->_temperatur = $temperatur;
    }

    /**
     * Returns $_timeLeft.
     *
     * @see UPSDevice::$_timeLeft
     *
     * @return String
     */
    public function getTimeLeft()
    {
        return $this->_timeLeft;
    }

    /**
     * Sets $_timeLeft.
     *
     * @param String $timeLeft time left
     *
     * @see UPSDevice::$_timeLeft
     *
     * @return void
     */
    public function setTimeLeft($timeLeft)
    {
        $this->_timeLeft = $timeLeft;
    }

    /**
     * Returns $_beeperStatus.
     *
     * @see UPSDevice::$_beeperStatus
     *
     * @return String
     */
    public function getBeeperStatus()
    {
        return $this->_beeperStatus;
    }

    /**
     * Sets $_beeperStatus.
     *
     * @param String $beeperStatus beeper status
     *
     * @see UPSDevice::$_beeperStatus
     *
     * @return void
     */
    public function setBeeperStatus($beeperStatus)
    {
        $this->_beeperStatus = $beeperStatus;
    }
}
