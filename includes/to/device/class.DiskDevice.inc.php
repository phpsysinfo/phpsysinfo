<?php
/**
 * DiskDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.DiskDevice.inc.php 252 2009-06-17 13:06:44Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * DiskDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class DiskDevice
{
    /**
     * name of the disk device
     *
     * @var String
     */
    private $_name = "";

    /**
     * type of the filesystem on the disk device
     *
     * @var String
     */
    private $_fsType = "";

    /**
     * diskspace that is free in bytes
     *
     * @var Integer
     */
    private $_free = 0;

    /**
     * diskspace that is used in bytes
     *
     * @var Integer
     */
    private $_used = 0;

    /**
     * total diskspace
     *
     * @var Integer
     */
    private $_total = 0;

    /**
     * mount point of the disk device if available
     *
     * @var String
     */
    private $_mountPoint = null;

    /**
     * additional options of the device, like mount options
     *
     * @var String
     */
    private $_options = null;

    /**
     * inodes usage in percent if available
     *
     * @var
     */
    private $_percentInodesUsed = null;

    /**
     * Returns PercentUsed calculated when function is called from internal values
     *
     * @see DiskDevice::$_total
     * @see DiskDevice::$_used
     *
     * @return Integer
     */
    public function getPercentUsed()
    {
        if ($this->_total > 0) {
            return ceil($this->_used / $this->_total * 100);
        } else {
            return 0;
        }
    }

    /**
     * Returns $_PercentInodesUsed.
     *
     * @see DiskDevice::$_PercentInodesUsed
     *
     * @return Integer
     */
    public function getPercentInodesUsed()
    {
        return $this->_percentInodesUsed;
    }

    /**
     * Sets $_PercentInodesUsed.
     *
     * @param Integer $percentInodesUsed inodes percent
     *
     * @see DiskDevice::$_PercentInodesUsed
     *
     * @return Void
     */
    public function setPercentInodesUsed($percentInodesUsed)
    {
        $this->_percentInodesUsed = $percentInodesUsed;
    }

    /**
     * Returns $_free.
     *
     * @see DiskDevice::$_free
     *
     * @return Integer
     */
    public function getFree()
    {
        return $this->_free;
    }

    /**
     * Sets $_free.
     *
     * @param Integer $free free bytes
     *
     * @see DiskDevice::$_free
     *
     * @return Void
     */
    public function setFree($free)
    {
        $this->_free = $free;
    }

    /**
     * Returns $_fsType.
     *
     * @see DiskDevice::$_fsType
     *
     * @return String
     */
    public function getFsType()
    {
        return $this->_fsType;
    }

    /**
     * Sets $_fsType.
     *
     * @param String $fsType filesystemtype
     *
     * @see DiskDevice::$_fsType
     *
     * @return Void
     */
    public function setFsType($fsType)
    {
        $this->_fsType = $fsType;
    }

    /**
     * Returns $_mountPoint.
     *
     * @see DiskDevice::$_mountPoint
     *
     * @return String
     */
    public function getMountPoint()
    {
        return $this->_mountPoint;
    }

    /**
     * Sets $_mountPoint.
     *
     * @param String $mountPoint mountpoint
     *
     * @see DiskDevice::$_mountPoint
     *
     * @return Void
     */
    public function setMountPoint($mountPoint)
    {
        $this->_mountPoint = $mountPoint;
    }

    /**
     * Returns $_name.
     *
     * @see DiskDevice::$_name
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
     * @see DiskDevice::$_name
     *
     * @return Void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns $_options.
     *
     * @see DiskDevice::$_options
     *
     * @return String
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets $_options.
     *
     * @param String $options additional options
     *
     * @see DiskDevice::$_options
     *
     * @return Void
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Returns $_total.
     *
     * @see DiskDevice::$_total
     *
     * @return Integer
     */
    public function getTotal()
    {
        return $this->_total;
    }

    /**
     * Sets $_total.
     *
     * @param Integer $total total bytes
     *
     * @see DiskDevice::$_total
     *
     * @return Void
     */
    public function setTotal($total)
    {
        $this->_total = $total;
    }

    /**
     * Returns $_used.
     *
     * @see DiskDevice::$_used
     *
     * @return Integer
     */
    public function getUsed()
    {
        return $this->_used;
    }

    /**
     * Sets $_used.
     *
     * @param Integer $used used bytes
     *
     * @see DiskDevice::$_used
     *
     * @return Void
     */
    public function setUsed($used)
    {
        $this->_used = $used;
    }
}
