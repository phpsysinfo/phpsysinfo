<?php
/**
 * Basic OS Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI sensors class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.sensors.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Basic OS functions for all OS classes
 *
 * @category  PHP
 * @package   PSI sensors class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class Sensors implements PSI_Interface_Sensor
{
    /**
     * object for error handling
     *
     * @var Error
     */
    protected $error;

    /**
     * object for the information
     *
     * @var MBInfo
     */
    protected $mbinfo;

    /**
     * build the global Error object
     */
    public function __construct()
    {
        $this->error = PSI_Error::singleton();
        $this->mbinfo = new MBInfo();
    }

    /**
     * get the filled or unfilled (with default values) MBInfo object
     *
     * @see PSI_Interface_Sensor::getMBInfo()
     *
     * @return MBInfo
     */
    final public function getMBInfo()
    {
        $this->build();

        return $this->mbinfo;
    }
}
