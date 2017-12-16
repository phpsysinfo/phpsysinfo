<?php
/**
 * thinkpad sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.thinkpad.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting hardware temperature information and fan speed from /sys/devices/platform/thinkpad_hwmon/
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    William Johansson <radar@radhuset.org>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Thinkpad extends Hwmon
{
    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return Void
     */
    public function build()
    {
        if (PSI_OS == 'Linux') {
            $hwpaths = glob("/sys/devices/platform/thinkpad_hwmon/", GLOB_NOSORT);
            if (($hwpaths !== false) && (count($hwpaths) == 1)) {
                $this->_temperature($hwpaths[0]);
                $this->_fans($hwpaths[0]);
            }
        }
    }
}
