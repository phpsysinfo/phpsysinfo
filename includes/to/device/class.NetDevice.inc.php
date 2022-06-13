<?php
/**
 * NetDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.NetDevice.inc.php 547 2012-03-22 09:44:38Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * NetDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class NetDevice
{
    /**
     * name of the device
     *
     * @var string
     */
    private $_name = "";

    /**
     * transmitted bytes
     *
     * @var int
     */
    private $_txBytes = 0;

    /**
     * received bytes
     *
     * @var int
     */
    private $_rxBytes = 0;

    /**
     * counted error packages
     *
     * @var int
     */
    private $_errors = 0;

    /**
     * counted droped packages
     *
     * @var int
     */
    private $_drops = 0;

    /**
     * string with info
     *
     * @var string
     */
    private $_info = null;

    /**
     * transmitted bytes rate
     *
     * @var int
     */
    private $_txRate = null;

    /**
     * received bytes rate
     *
     * @var int
     */
    private $_rxRate = null;

    /**
     * Returns $_drops.
     *
     * @see NetDevice::$_drops
     *
     * @return int
     */
    public function getDrops()
    {
        return $this->_drops;
    }

    /**
     * Sets $_drops.
     *
     * @param int $drops dropped packages
     *
     * @see NetDevice::$_drops
     *
     * @return void
     */
    public function setDrops($drops)
    {
        $this->_drops = $drops;
    }

    /**
     * Returns $_errors.
     *
     * @see NetDevice::$_errors
     *
     * @return int
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Sets $_errors.
     *
     * @param int $errors error packages
     *
     * @see NetDevice::$_errors
     *
     * @return void
     */
    public function setErrors($errors)
    {
        $this->_errors = $errors;
    }

    /**
     * Returns $_name.
     *
     * @see NetDevice::$_name
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
     * @param String $name device name
     *
     * @see NetDevice::$_name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns $_rxBytes.
     *
     * @see NetDevice::$_rxBytes
     *
     * @return int
     */
    public function getRxBytes()
    {
        return $this->_rxBytes;
    }

    /**
     * Sets $_rxBytes.
     *
     * @param int $rxBytes received bytes
     *
     * @see NetDevice::$_rxBytes
     *
     * @return void
     */
    public function setRxBytes($rxBytes)
    {
        $this->_rxBytes = $rxBytes;
    }

    /**
     * Returns $_txBytes.
     *
     * @see NetDevice::$_txBytes
     *
     * @return int
     */
    public function getTxBytes()
    {
        return $this->_txBytes;
    }

    /**
     * Sets $_txBytes.
     *
     * @param int $txBytes transmitted bytes
     *
     * @see NetDevice::$_txBytes
     *
     * @return void
     */
    public function setTxBytes($txBytes)
    {
        $this->_txBytes = $txBytes;
    }

    /**
     * Returns $_info.
     *
     * @see NetDevice::$_info
     *
     * @return String
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     * Sets $_info.
     *
     * @param String $info info string
     *
     * @see NetDevice::$_info
     *
     * @return void
     */
    public function setInfo($info)
    {
        $this->_info = $info;
    }
    /**
     * Returns $_rxRate.
     *
     * @see NetDevice::$_rxRate
     *
     * @return int
     */
    public function getRxRate()
    {
        return $this->_rxRate;
    }

    /**
     * Sets $_rxRate.
     *
     * @param int $rxRate received bytes rate
     *
     * @see NetDevice::$_rxRate
     *
     * @return void
     */
    public function setRxRate($rxRate)
    {
        $this->_rxRate = $rxRate;
    }

    /**
     * Returns $_txRate.
     *
     * @see NetDevice::$_txRate
     *
     * @return int
     */
    public function getTxRate()
    {
        return $this->_txRate;
    }

    /**
     * Sets $_txRate.
     *
     * @param int $txRate transmitted bytes rate
     *
     * @see NetDevice::$_txRate
     *
     * @return void
     */
    public function setTxRate($txRate)
    {
        $this->_txRate = $txRate;
    }
}
