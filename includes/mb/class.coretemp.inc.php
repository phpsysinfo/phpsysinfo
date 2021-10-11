<?php
/**
 * coretemp sensor class, getting hardware temperature information through sysctl on FreeBSD
 * or from /sys/devices/platform/coretemp. on Linux
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
class Coretemp extends Hwmon
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
            $hwpaths = glob("/sys/devices/platform/coretemp.*/", GLOB_NOSORT);
            if (is_array($hwpaths) && (count($hwpaths) > 0)) {
                $hwpaths2 = glob("/sys/devices/platform/coretemp.*/hwmon/hwmon*/", GLOB_NOSORT);
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
            $_wmi = CommonFunctions::initWMI('root\CIMv2', true);
            if ($_wmi) {
                $allCpus = CommonFunctions::getWMI($_wmi, 'Win32_Processor', array('DeviceID', 'CurrentVoltage'));
                if ($allCpus) foreach ($allCpus as $oneCpu) if (isset($oneCpu['CurrentVoltage']) && ($oneCpu['CurrentVoltage'] > 0)){
                    $dev = new SensorDevice();
                    $dev->setName($oneCpu['DeviceID']);
                    $dev->setValue($oneCpu['CurrentVoltage']/10);
                    $this->mbinfo->setMbVolt($dev);
                }
                $allMems = CommonFunctions::getWMI($_wmi, 'Win32_PhysicalMemory', array('ConfiguredVoltage', 'MinVoltage', 'MaxVoltage'));
                $counter = 0;
                if ($allMems) foreach ($allMems as $oneMem) if (isset($oneMem['ConfiguredVoltage']) && ($oneMem['ConfiguredVoltage'] > 0)) {
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
        }
        if ((PSI_OS != 'WINNT') && !defined('PSI_EMU_HOSTNAME')) {
            $buffer = '';
            if (defined('PSI_DMIDECODE_ACCESS') && (strtolower(PSI_DMIDECODE_ACCESS)==='data')) {
                CommonFunctions::rfts(PSI_APP_ROOT.'/data/dmidecode.tmp', $buffer);
            } elseif (CommonFunctions::_findProgram('dmidecode')) {
                CommonFunctions::executeProgram('dmidecode', '-t 17', $buffer, PSI_DEBUG);
            }
            if (!empty($buffer)) {
                $banks = preg_split('/^(?=Handle\s)/m', $buffer, -1, PREG_SPLIT_NO_EMPTY);
                $counter = 0;
                foreach ($banks as $bank) if (preg_match('/^Handle\s/', $bank)) {
                    $lines = preg_split("/\n/", $bank, -1, PREG_SPLIT_NO_EMPTY);
                    $mem = array();
                    foreach ($lines as $line) if (preg_match('/^\s+([^:]+):(.+)/', $line, $params)) {
                        if (preg_match('/^0x([A-F\d]+)/', $params2 = trim($params[2]), $buff)) {
                            $mem[trim($params[1])] = trim($buff[1]);
                        } elseif ($params2 != '') {
                            $mem[trim($params[1])] = $params2;
                        }
                    }
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
}
