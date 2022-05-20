<?php
/**
 * hwmon sensor class, getting hardware sensors information from /sys/class/hwmon/hwmon
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2016 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Hwmon extends Sensors
{
    /**
     * get temperature information
     *
     * @param  string $hwpath
     * @return void
     */
    protected function _temperature($hwpath)
    {
       $sensor = CommonFunctions::findglob($hwpath."temp*_input", GLOB_NOSORT);
       if (is_array($sensor) && (($total = count($sensor)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (($buf = CommonFunctions::rolv($sensor[$i]))!==null) {
                $dev = new SensorDevice();
                $dev->setValue($buf/1000);
                if (($buf = CommonFunctions::rolv($sensor[$i], "/\/[^\/]*_input$/", "/name"))!==null) {
                   $name = " (".$buf.")";
                } else {
                   $name = "";
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_label"))!==null) {
                    $dev->setName($buf.$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if (($name == " (drivetemp)") && (count($buf = CommonFunctions::gdc($hwpath . "device/block", false)))) {
                        $labelname = "/dev/" . $buf[0];
                        if (($buf = CommonFunctions::rolv($hwpath . "device/model"))!==null) {
                            $labelname .= " (".$buf.")";
                            $name = "";
                        }
                    }
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_crit"))!==null) {
                    $dev->setMax($buf/1000);
                    if (CommonFunctions::rolv($sensor[$i], "/_input$/", "_crit_alarm")==="1") {
                        $dev->setEvent("Critical Alarm");
                    }
                } elseif (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_max"))!==null) {
                    $dev->setMax($buf/1000);
                }
                $this->mbinfo->setMbTemp($dev);
            }
        }
    }

    /**
     * get voltage information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _voltage($hwpath)
    {
       $sensor = CommonFunctions::findglob($hwpath."in*_input", GLOB_NOSORT);
       if (is_array($sensor) && (($total = count($sensor)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (($buf = CommonFunctions::rolv($sensor[$i]))!==null) {
                $dev = new SensorDevice();
                $dev->setValue($buf/1000);
                if (($buf = CommonFunctions::rolv($sensor[$i], "/\/[^\/]*_input$/", "/name"))!==null) {
                   $name = " (".$buf.")";
                } else {
                   $name = "";
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_label"))!==null) {
                    $dev->setName($buf.$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_max"))!==null) {
                    $dev->setMax($buf/1000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_min"))!==null) {
                    $dev->setMin($buf/1000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_alarm"))==="1") {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbVolt($dev);
            }
        }
    }

    /**
     * get fan information
     *
     * @param  string $hwpath
     * @return void
     */
    protected function _fans($hwpath)
    {
       $sensor = CommonFunctions::findglob($hwpath."fan*_input", GLOB_NOSORT);
       if (is_array($sensor) && (($total = count($sensor)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (($buf = CommonFunctions::rolv($sensor[$i]))!==null) {
                $dev = new SensorDevice();
                $dev->setValue($buf);
                if (($buf = CommonFunctions::rolv($sensor[$i], "/\/[^\/]*_input$/", "/name"))!==null) {
                   $name = " (".$buf.")";
                } else {
                   $name = "";
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_label"))!==null) {
                    $dev->setName($buf.$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_full_speed"))!==null) {
                    $dev->setMax($buf);
                } elseif (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_max"))!==null) {
                    $dev->setMax($buf);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_min"))!==null) {
                    $dev->setMin($buf);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_alarm"))==="1") {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbFan($dev);
            }
        }
    }

    /**
     * get power information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _power($hwpath)
    {
       $sensor = CommonFunctions::findglob($hwpath."power*_input", GLOB_NOSORT);
       if (is_array($sensor) && (($total = count($sensor)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (($buf = CommonFunctions::rolv($sensor[$i]))!==null) {
                $dev = new SensorDevice();
                $dev->setValue($buf/1000000);
                if (($buf = CommonFunctions::rolv($sensor[$i], "/\/[^\/]*_input$/", "/name"))!==null) {
                   $name = " (".$buf.")";
                } else {
                   $name = "";
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_label"))!==null) {
                    $dev->setName($buf.$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_max"))!==null) {
                    $dev->setMax($buf/1000000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_min"))!==null) {
                    $dev->setMin($buf/1000000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_alarm"))==="1") {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbPower($dev);
            }
        }
    }

    /**
     * get current information
     *
     * @param  string $hwpath
     * @return void
     */
    private function _current($hwpath)
    {
       $sensor = CommonFunctions::findglob($hwpath."curr*_input", GLOB_NOSORT);
       if (is_array($sensor) && (($total = count($sensor)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) if (($buf = CommonFunctions::rolv($sensor[$i]))!==null) {
                $dev = new SensorDevice();
                $dev->setValue($buf/1000);
                if (($buf = CommonFunctions::rolv($sensor[$i], "/\/[^\/]*_input$/", "/name"))!==null) {
                   $name = " (".$buf.")";
                } else {
                   $name = "";
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_label"))!==null) {
                    $dev->setName($buf.$name);
                } else {
                    $labelname = trim(preg_replace("/_input$/", "", pathinfo($sensor[$i], PATHINFO_BASENAME)));
                    if ($labelname !== "") {
                        $dev->setName($labelname.$name);
                    } else {
                        $dev->setName('unknown'.$name);
                    }
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_max"))!==null) {
                    $dev->setMax($buf/1000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_min"))!==null) {
                    $dev->setMin($buf/1000);
                }
                if (($buf = CommonFunctions::rolv($sensor[$i], "/_input$/", "_alarm"))==="1") {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbCurrent($dev);
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return void
     */
    public function build()
    {
        if ((PSI_OS == 'Linux') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT'))) {
            $hwpaths = CommonFunctions::findglob("/sys/class/hwmon/hwmon*/", GLOB_NOSORT);
            if (is_array($hwpaths) && (count($hwpaths) > 0)) {
                $hwpaths2 = CommonFunctions::findglob("/sys/class/hwmon/hwmon*/device/", GLOB_NOSORT);
                if (is_array($hwpaths2) && (count($hwpaths2) > 0)) {
                    $hwpaths = array_merge($hwpaths, $hwpaths2);
                }
                $totalh = count($hwpaths);
                for ($h = 0; $h < $totalh; $h++) {
                    $this->_temperature($hwpaths[$h]);
                    $this->_voltage($hwpaths[$h]);
                    $this->_fans($hwpaths[$h]);
                    $this->_power($hwpaths[$h]);
                    $this->_current($hwpaths[$h]);
                }
            }
        }
    }
}
