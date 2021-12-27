<?php
/**
 * thinkpad sensor class, getting hardware temperature information and fan speed from /sys/devices/platform/thinkpad_hwmon/
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2017 phpSysInfo
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
     * @return void
     */
    public function build()
    {
        if ((PSI_OS == 'Linux') && !defined('PSI_EMU_HOSTNAME')) {
            $hwpaths = CommonFunctions::findglob("/sys/devices/platform/thinkpad_hwmon/", GLOB_NOSORT);
            if (is_array($hwpaths) && (count($hwpaths) == 1)) {
                $hwpaths2 = CommonFunctions::findglob("/sys/devices/platform/thinkpad_hwmon/hwmon/hwmon*/", GLOB_NOSORT);
                if (is_array($hwpaths2) && (count($hwpaths2) > 0)) {
                    $hwpaths = array_merge($hwpaths, $hwpaths2);
                }
                $totalh = count($hwpaths);
                for ($h = 0; $h < $totalh; $h++) {
                    $this->_temperature($hwpaths[$h]);
                    $this->_fans($hwpaths[$h]);
                }
            }
        }
    }
}
