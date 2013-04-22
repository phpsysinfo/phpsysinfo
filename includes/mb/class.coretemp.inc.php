<?php
/**
 * coretemp sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.coretemp.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting hardware temperature information through sysctl
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    William Johansson <radar@radhuset.org>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Coretemp extends Sensors
{
    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        $smp = 1;
        CommonFunctions::executeProgram('sysctl', '-n kern.smp.cpus', $smp);
        for ($i = 0; $i < $smp; $i++) {
            $temp = 0;
            if (CommonFunctions::executeProgram('sysctl', '-n dev.cpu.'.$i.'.temperature', $temp)) {
                $dev = new SensorDevice();
                $dev->setName("CPU ".($i + 1));
                $dev->setValue($temp);
                $dev->setMax(70);
                $this->mbinfo->setMbTemp($dev);
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_temperature();
    }
}
