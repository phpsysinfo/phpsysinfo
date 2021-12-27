<?php
/**
 * CpuDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.CpuDevice.inc.php 411 2010-12-28 22:32:52Z Jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * CpuDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class CpuDevice
{
    /**
     * model of the cpu
     *
     * @var string
     */
    private $_model = "";

    /**
     * cpu voltage
     *
     * @var Float
     */
    private $_voltage = 0;

    /**
     * speed of the cpu in hertz
     *
     * @var int
     */
    private $_cpuSpeed = 0;

    /**
     * max speed of the cpu in hertz
     *
     * @var int
     */
    private $_cpuSpeedMax = 0;

    /**
     * min speed of the cpu in hertz
     *
     * @var int
     */
    private $_cpuSpeedMin = 0;

    /**
     * cache size in bytes, if available
     *
     * @var int
     */
    private $_cache = null;

    /**
     * virtualization, if available
     *
     * @var string
     */
    private $_virt = null;

    /**
     * busspeed in hertz, if available
     *
     * @var int
     */
    private $_busSpeed = null;

    /**
     * bogomips of the cpu, if available
     *
     * @var int
     */
    private $_bogomips = null;

    /**
     * temperature of the cpu, if available
     *
     * @var int
     */
    private $_temp = null;

    /**
     * vendorid, if available
     *
     * @var string
     */
    private $_vendorid = null;

    /**
     * current load in percent of the cpu, if available
     *
     * @var int
     */
    private $_load = null;

    /**
     * Returns $_model.
     *
     * @see Cpu::$_model
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
     * @param String $model cpumodel
     *
     * @see Cpu::$_model
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Returns $_voltage.
     *
     * @see Cpu::$_voltage
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
     * @param int $voltage voltage
     *
     * @see Cpu::$_voltage
     *
     * @return void
     */
    public function setVoltage($voltage)
    {
        $this->_voltage = $voltage;
    }

    /**
     * Returns $_cpuSpeed.
     *
     * @see Cpu::$_cpuSpeed
     *
     * @return int
     */
    public function getCpuSpeed()
    {
        return $this->_cpuSpeed;
    }

    /**
     * Sets $_cpuSpeed.
     *
     * @param int $cpuSpeed cpuspeed
     *
     * @see Cpu::$_cpuSpeed
     *
     * @return void
     */
    public function setCpuSpeed($cpuSpeed)
    {
        $this->_cpuSpeed = $cpuSpeed;
    }

    /**
     * Returns $_cpuSpeedMax.
     *
     * @see Cpu::$_cpuSpeedMAx
     *
     * @return int
     */
    public function getCpuSpeedMax()
    {
        return $this->_cpuSpeedMax;
    }

    /**
     * Sets $_cpuSpeedMax.
     *
     * @param int $cpuSpeedMax cpuspeedmax
     *
     * @see Cpu::$_cpuSpeedMax
     *
     * @return void
     */
    public function setCpuSpeedMax($cpuSpeedMax)
    {
        $this->_cpuSpeedMax = $cpuSpeedMax;
    }

    /**
     * Returns $_cpuSpeedMin.
     *
     * @see Cpu::$_cpuSpeedMin
     *
     * @return int
     */
    public function getCpuSpeedMin()
    {
        return $this->_cpuSpeedMin;
    }

    /**
     * Sets $_cpuSpeedMin.
     *
     * @param int $cpuSpeedMin cpuspeedmin
     *
     * @see Cpu::$_cpuSpeedMin
     *
     * @return void
     */
    public function setCpuSpeedMin($cpuSpeedMin)
    {
        $this->_cpuSpeedMin = $cpuSpeedMin;
    }

    /**
     * Returns $_cache.
     *
     * @see Cpu::$_cache
     *
     * @return int
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Sets $_cache.
     *
     * @param int $cache cache size
     *
     * @see Cpu::$_cache
     *
     * @return void
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Returns $_virt.
     *
     * @see Cpu::$_virt
     *
     * @return String
     */
    public function getVirt()
    {
        return $this->_virt;
    }

    /**
     * Sets $_virt.
     *
     * @param string $virt
     *
     * @see Cpu::$_virt
     *
     * @return void
     */
    public function setVirt($virt)
    {
        $this->_virt = $virt;
    }

    /**
     * Returns $_busSpeed.
     *
     * @see Cpu::$_busSpeed
     *
     * @return int
     */
    public function getBusSpeed()
    {
        return $this->_busSpeed;
    }

    /**
     * Sets $_busSpeed.
     *
     * @param int $busSpeed busspeed
     *
     * @see Cpu::$_busSpeed
     *
     * @return void
     */
    public function setBusSpeed($busSpeed)
    {
        $this->_busSpeed = $busSpeed;
    }

    /**
     * Returns $_bogomips.
     *
     * @see Cpu::$_bogomips
     *
     * @return int
     */
    public function getBogomips()
    {
        return $this->_bogomips;
    }

    /**
     * Sets $_bogomips.
     *
     * @param int $bogomips bogompis
     *
     * @see Cpu::$_bogomips
     *
     * @return void
     */
    public function setBogomips($bogomips)
    {
        $this->_bogomips = $bogomips;
    }

    /**
     * Returns $_temp.
     *
     * @see Cpu::$_temp
     *
     * @return int
     */
/*
    public function getTemp()
    {
        return $this->_temp;
    }
*/

    /**
     * Sets $_temp.
     *
     * @param int $temp temperature
     *
     * @see Cpu::$_temp
     *
     * @return void
     */
/*
    public function setTemp($temp)
    {
        $this->_temp = $temp;
    }
*/

    /**
     * Returns $_vendorid.
     *
     * @see Cpu::$_vendorid
     *
     * @return String
     */
    public function getVendorId()
    {
        return $this->_vendorid;
    }

    /**
     * Sets $_vendorid.
     *
     * @param string $vendorid
     *
     * @see Cpu::$_vendorid
     *
     * @return void
     */
    public function setVendorId($vendorid)
    {
        $this->_vendorid = trim(preg_replace('/[\s!]/', '', $vendorid));
    }

    /**
     * Returns $_load.
     *
     * @see CpuDevice::$_load
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
     * @param int $load load percent
     *
     * @see CpuDevice::$_load
     *
     * @return void
     */
    public function setLoad($load)
    {
        $this->_load = $load;
    }
}
