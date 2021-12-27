<?php
/**
 * HWDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.HWDevice.inc.php 255 2009-06-17 13:39:41Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * HWDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class HWDevice
{
    /**
     * name of the device
     *
     * @var string
     */
    private $_name = "";

    /**
     * capacity of the device, if not available it will be null
     *
     * @var int
     */
    private $_capacity = null;

    /**
     * manufacturer of the device, if not available it will be null
     *
     * @var int
     */
    private $_manufacturer = null;

    /**
     * product of the device, if not available it will be null
     *
     * @var int
     */
    private $_product = null;

    /**
     * serial number of the device, if not available it will be null
     *
     * @var string
     */
    private $_serial = null;

    /**
     * speed of the device, if not available it will be null
     *
     * @var Float
     */
    private $_speed = null;

    /**
     * voltage of the device, if not available it will be null
     *
     * @var Float
     */
    private $_voltage = null;

    /**
     * count of the device
     *
     * @var int
     */
    private $_count = 1;

    /**
     * compare a given device with the internal one
     *
     * @param HWDevice $dev device that should be compared
     *
     * @return boolean
     */
    public function equals(HWDevice $dev)
    {
        if ($dev->getName() === $this->_name
           && $dev->getCapacity() === $this->_capacity
           && $dev->getManufacturer() === $this->_manufacturer
           && $dev->getProduct() === $this->_product
           && $dev->getSerial() === $this->_serial
           && $dev->getSpeed() === $this->_speed) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns $_name.
     *
     * @see HWDevice::$_name
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
     * @see HWDevice::$_name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns $_manufacturer.
     *
     * @see HWDevice::$_manufacturer
     *
     * @return String
     */
    public function getManufacturer()
    {
        return $this->_manufacturer;
    }

    /**
     * Sets $_manufacturer.
     *
     * @param String $manufacturer manufacturer name
     *
     * @see HWDevice::$_manufacturer
     *
     * @return void
     */
    public function setManufacturer($manufacturer)
    {
        $this->_manufacturer = $manufacturer;
    }

    /**
     * Returns $_product.
     *
     * @see HWDevice::$_product
     *
     * @return String
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Sets $_product.
     *
     * @param String $product product name
     *
     * @see HWDevice::$_product
     *
     * @return void
     */
    public function setProduct($product)
    {
        $this->_product = $product;
    }

    /**
     * Returns $_serial.
     *
     * @see HWDevice::$_serial
     *
     * @return String
     */
    public function getSerial()
    {
        return $this->_serial;
    }

    /**
     * Sets $_serial.
     *
     * @param String $serial serial number
     *
     * @see HWDevice::$_serial
     *
     * @return void
     */
    public function setSerial($serial)
    {
        $this->_serial = $serial;
    }

    /**
     * Returns $_speed.
     *
     * @see HWDevice::$_speed
     *
     * @return Float
     */
    public function getSpeed()
    {
        return $this->_speed;
    }

    /**
     * Sets $_speed.
     *
     * @param Float $speed speed
     *
     * @see HWDevice::$_speed
     *
     * @return void
     */
    public function setSpeed($speed)
    {
        $this->_speed = $speed;
    }

    /**
     * Returns $_voltage.
     *
     * @see HWDevice::$_voltage
     *
     * @return Float
     */
    public function getVoltage()
    {
        return $this->_voltage;
    }

    /**
     * Sets $_voltage.
     *
     * @param Float $voltage voltage
     *
     * @see HWDevice::$_voltage
     *
     * @return void
     */
    public function setVoltage($voltage)
    {
        $this->_voltage = $voltage;
    }

    /**
     * Returns $_capacity.
     *
     * @see HWDevice::$_capacity
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->_capacity;
    }

    /**
     * Sets $_capacity.
     *
     * @param int $capacity device capacity
     *
     * @see HWDevice::$_capacity
     *
     * @return void
     */
    public function setCapacity($capacity)
    {
        $this->_capacity = $capacity;
    }

    /**
     * Returns $_count.
     *
     * @see HWDevice::$_count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * Sets $_count.
     *
     * @param int $count device count
     *
     * @see HWDevice::$_count
     *
     * @return void
     */
    public function setCount($count)
    {
        $this->_count = $count;
    }
}
