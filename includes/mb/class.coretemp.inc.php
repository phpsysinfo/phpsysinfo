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
        if (PSI_OS == 'Linux') {
           $hwpaths = glob("/sys/devices/platform/coretemp.*/", GLOB_NOSORT);
           if (($totalh = count($hwpaths)) > 0) {
               $buf = "";
               for ($h = 0; $h < $totalh; $h++) {
                   $tempsensor = glob($hwpaths[$h]."temp*_input", GLOB_NOSORT);
                   if (($total = count($tempsensor)) > 0) {
                        $buf = "";
                        for ($i = 0; $i < $total; $i++) if (CommonFunctions::rfts($tempsensor[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                            $dev = new SensorDevice();
                            $dev->setValue(trim($buf)/1000);
                            $label = preg_replace("/_input$/", "_label", $tempsensor[$i]);
                            $crit = preg_replace("/_input$/", "_crit", $tempsensor[$i]);
                            $max = preg_replace("/_input$/", "_max", $tempsensor[$i]);
                            $crit_alarm = preg_replace("/_input$/", "_crit_alarm", $tempsensor[$i]);
                            if (CommonFunctions::fileexists($label) && CommonFunctions::rfts($label, $buf, 1, 4096, false) && (trim($buf) != "")) {
                                $dev->setName(trim($buf));
                            } else {
                                $labelname = trim(preg_replace("/_input$/", "",  pathinfo($tempsensor[$i], PATHINFO_BASENAME)));
                                if ($labelname !== "") {
                                    $dev->setName($labelname);
                                } else {
                                    $dev->setName('unknown');
                                }
                            }
                            if (CommonFunctions::fileexists($crit) && CommonFunctions::rfts($crit, $buf, 1, 4096, false) && (trim($buf) != "")) {
                                $dev->setMax(trim($buf)/1000);
                                if (CommonFunctions::fileexists($crit_alarm) && CommonFunctions::rfts($crit_alarm, $buf, 1, 4096, false) && (trim($buf) === "1")) {
                                    $dev->setEvent("Critical Alarm");
                                }
                            } elseif (CommonFunctions::fileexists($max) && CommonFunctions::rfts($max, $buf, 1, 4096, false) && (trim($buf) != "")) {
                                $dev->setMax(trim($buf)/1000);
                            }
                            $this->mbinfo->setMbTemp($dev);
                        }
                    }
                }
            }
        } else {
            $smp = 1;
            CommonFunctions::executeProgram('sysctl', '-n kern.smp.cpus', $smp);
            for ($i = 0; $i < $smp; $i++) {
                $temp = 0;
                if (CommonFunctions::executeProgram('sysctl', '-n dev.cpu.'.$i.'.temperature', $temp)) {
                    $temp = preg_replace('/C/', '', $temp);
                    $dev = new SensorDevice();
                    $dev->setName("CPU ".($i + 1));
                    $dev->setValue($temp);
//                    $dev->setMax(70);
                    $this->mbinfo->setMbTemp($dev);
                }
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
