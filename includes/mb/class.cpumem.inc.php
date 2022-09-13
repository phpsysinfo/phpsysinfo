<?php
/**
 * cpumem sensor class, getting hardware sensors information of CPU and memory
 *
 * PHP version 5
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
class CpuMem extends Hwmon
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
            $hwpaths = CommonFunctions::findglob("/sys/devices/platform/coretemp.*/", GLOB_NOSORT);
            if (is_array($hwpaths) && (count($hwpaths) > 0)) {
                $hwpaths2 = CommonFunctions::findglob("/sys/devices/platform/coretemp.*/hwmon/hwmon*/", GLOB_NOSORT);
                if (is_array($hwpaths2) && (count($hwpaths2) > 0)) {
                    $hwpaths = array_merge($hwpaths, $hwpaths2);
                }
                $totalh = count($hwpaths);
                for ($h = 0; $h < $totalh; $h++) {
                    $this->_temperature($hwpaths[$h]);
                }
            }
        } elseif (PSI_OS == 'FreeBSD') {
            $smp = 1;
            CommonFunctions::executeProgram('sysctl', '-n kern.smp.cpus', $smp);
            for ($i = 0; $i < $smp; $i++) {
                $temp = 0;
                if (CommonFunctions::executeProgram('sysctl', '-n dev.cpu.'.$i.'.temperature', $temp)) {
                    $temp = preg_replace('/,/', '.', preg_replace('/C/', '', $temp));
                    $dev = new SensorDevice();
                    $dev->setName("CPU ".($i + 1));
                    $dev->setValue($temp);
//                    $dev->setMax(70);
                    $this->mbinfo->setMbTemp($dev);
                }
            }
        } elseif ((PSI_OS == 'WINNT') || defined('PSI_EMU_HOSTNAME')) {
            $allCpus = WINNT::_get_Win32_Processor();
            foreach ($allCpus as $oneCpu) if (isset($oneCpu['CurrentVoltage']) && ($oneCpu['CurrentVoltage'] > 0)) {
                $dev = new SensorDevice();
                $dev->setName($oneCpu['DeviceID']);
                $dev->setValue($oneCpu['CurrentVoltage']/10);
                $this->mbinfo->setMbVolt($dev);
            }
            $allMems = WINNT::_get_Win32_PhysicalMemory();
            $counter = 0;
            foreach ($allMems as $oneMem) if (isset($oneMem['ConfiguredVoltage']) && ($oneMem['ConfiguredVoltage'] > 0)) {
                $dev = new SensorDevice();
                $dev->setName('Mem'.($counter++));
                $dev->setValue($oneMem['ConfiguredVoltage']/1000);
                if (isset($oneMem['MaxVoltage']) && ($oneMem['MaxVoltage'] > 0)) {
                    $dev->setMax($oneMem['MaxVoltage']/1000);
                }
                if (isset($oneMem['MinVoltage']) && ($oneMem['MinVoltage'] > 0)) {
                    $dev->setMin($oneMem['MinVoltage']/1000);
                }
                $this->mbinfo->setMbVolt($dev);
            }
        }
        if ((PSI_OS != 'WINNT') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT'))) {
            $dmimd = CommonFunctions::readdmimemdata();
            $counter = 0;
            foreach ($dmimd as $mem) {
                if (isset($mem['Size']) && preg_match('/^(\d+)\s(M|G)B$/', $mem['Size'], $size) && ($size[1] > 0)
                    && isset($mem['Configured Voltage']) && preg_match('/^([\d\.]+)\sV$/', $mem['Configured Voltage'], $voltage) && ($voltage[1] > 0)) {
                    $dev = new SensorDevice();
                    $dev->setName('Mem'.($counter++));
                    $dev->setValue($voltage[1]);
                    if (isset($mem['Minimum Voltage']) && preg_match('/^([\d\.]+)\sV$/', $mem['Minimum Voltage'], $minv) && ($minv[1]  > 0)) {
                        $dev->setMin($minv[1]);
                    }
                    if (isset($mem['Maximum Voltage']) && preg_match('/^([\d\.]+)\sV$/', $mem['Maximum Voltage'], $maxv) && ($maxv[1]  > 0)) {
                        $dev->setMax($maxv[1]);
                    }
                    $this->mbinfo->setMbVolt($dev);
                }
            }
        }
    }
}
