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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class DiskDevice
{
    /**
     * name of the disk device
     *
     * @var string
     */
    private $_name = "";

    /**
     * type of the filesystem on the disk device
     *
     * @var string
     */
    private $_fsType = "";

    /**
     * diskspace that is free in bytes
     *
     * @var int
     */
    private $_free = 0;

    /**
     * diskspace that is used in bytes
     *
     * @var int
     */
    private $_used = 0;

    /**
     * total diskspace
     *
     * @var int
     */
    private $_total = 0;

    /**
     * mount point of the disk device if available
     *
     * @var string
     */
    private $_mountPoint = null;

    /**
     * additional options of the device, like mount options
     *
     * @var string
     */
    private $_options = null;

    /**
     * inodes usage in percent if available
     *
     * @var int
     */
    private $_percentInodesUsed = null;

    /**
     * ignore mode
     *
     * @var int
     */
    private $_ignore = 0;

    /**
     * Returns PercentUsed calculated when function is called from internal values
     *
     * @see DiskDevice::$_total
     * @see DiskDevice::$_used
     *
     * @return int
     */
    public function getPercentUsed()
    {
        if ($this->_total > 0) {
            return 100 - min(floor($this->_free / $this->_total * 100), 100);
        } else {
            return 0;
        }
    }

    /**
     * Returns $_PercentInodesUsed.
     *
     * @see DiskDevice::$_PercentInodesUsed
     *
     * @return int
     */
    public function getPercentInodesUsed()
    {
        return $this->_percentInodesUsed;
    }

    /**
     * Sets $_PercentInodesUsed.
     *
     * @param int $percentInodesUsed inodes percent
     *
     * @see DiskDevice::$_PercentInodesUsed
     *
     * @return void
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
     * @return int
     */
    public function getFree()
    {
        return $this->_free;
    }

    /**
     * Sets $_free.
     *
     * @param int $free free bytes
     *
     * @see DiskDevice::$_free
     *
     * @return void
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
     * @return string
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
     * @return void
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
     * @return string
     */
    public function getMountPoint()
    {
        return $this->_mountPoint;
    }

    /**
     * Sets $_mountPoint.
     *
     * @param string $mountPoint mountpoint
     *
     * @see DiskDevice::$_mountPoint
     *
     * @return void
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
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets $_name.
     *
     * @param string $name device name
     *
     * @see DiskDevice::$_name
     *
     * @return void
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
     * @return string
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets $_options.
     *
     * @param string $options additional options
     *
     * @see DiskDevice::$_options
     *
     * @return void
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
     * @return int
     */
    public function getTotal()
    {
        return $this->_total;
    }

    /**
     * Sets $_total.
     *
     * @param int $total total bytes
     *
     * @see DiskDevice::$_total
     *
     * @return void
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
     * @return int
     */
    public function getUsed()
    {
        return $this->_used;
    }

    /**
     * Sets $_used.
     *
     * @param int $used used bytes
     *
     * @see DiskDevice::$_used
     *
     * @return void
     */
    public function setUsed($used)
    {
        $this->_used = $used;
    }

    /**
     * Returns $_ignore.
     *
     * @see DiskDevice::$_ignore
     *
     * @return int
     */
    public function getIgnore()
    {
        return $this->_ignore;
    }

    /**
     * Sets $_ignore.
     *
     * @see DiskDevice::$_ignore
     *
     * @return void
     */
    public function setIgnore($ignore)
    {
        $this->_ignore = $ignore;
    }
}
