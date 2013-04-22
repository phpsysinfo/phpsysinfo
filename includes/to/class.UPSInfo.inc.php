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
 * @version   SVN: $Id: class.UPSInfo.inc.php 329 2009-09-07 11:21:44Z bigmichi1 $
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
class UPSInfo
{
    /**
     * array with upsdivices
     *
     * @see UPSDevice
     *
     * @var Array
     */
    private $_upsDevices = array();

    /**
     * Returns $_upsDevices.
     *
     * @see UPSInfo::$_upsDevices
     *
     * @return Array
     */
    public function getUpsDevices()
    {
        return $this->_upsDevices;
    }

    /**
     * Sets $_upsDevices.
     *
     * @param UPSDevice $upsDevices upsdevice
     *
     * @see UPSInfo::$_upsDevices
     *
     * @return Void
     */
    public function setUpsDevices($upsDevices)
    {
        array_push($this->_upsDevices, $upsDevices);
    }
}
