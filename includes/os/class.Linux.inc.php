<?php
/**
 * Linux System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Linux OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Linux.inc.php 712 2012-12-05 14:09:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Linux sysinfo class
 * get all the required information from Linux system
 *
 * @category  PHP
 * @package   PSI Linux OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Linux extends OS
{
    /**
     * Assoc array of all CPUs loads.
     */
    protected $_cpu_loads;

    /**
     * call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hostname
     *
     * @return void
     */
    protected function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setHostname(getenv('SERVER_NAME'));
        } else {
            if (CommonFunctions::rfts('/proc/sys/kernel/hostname', $result, 1)) {
                $result = trim($result);
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    $this->sys->setHostname(gethostbyaddr($ip));
                }
            }
        }
    }

    /**
     * IP
     *
     * @return void
     */
    protected function _ip()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setIp(gethostbyname($this->sys->getHostname()));
        } else {
            if (!isset($_SERVER['SERVER_ADDR']) || !($result = $_SERVER['SERVER_ADDR'])) {
                $this->sys->setIp(gethostbyname($this->sys->getHostname()));
            } else {
                $this->sys->setIp($result);
            }
        }
    }

    /**
     * Kernel Version
     *
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::executeProgram($uname="uptrack-uname", '-r', $strBuf, false) || // show effective kernel if ksplice uptrack is installed
            CommonFunctions::executeProgram($uname="uname", '-r', $strBuf, PSI_DEBUG)) {
            $result = trim($strBuf);
            if (CommonFunctions::executeProgram($uname, '-v', $strBuf, PSI_DEBUG)) {
                if (preg_match('/SMP/', $strBuf)) {
                    $result .= ' (SMP)';
                }
            }
            if (CommonFunctions::executeProgram($uname, '-m', $strBuf, PSI_DEBUG)) {
                $result .= ' '.trim($strBuf);
            }
            $this->sys->setKernel($result);
        } else {
            if (CommonFunctions::rfts('/proc/version', $strBuf, 1)) {
                if (preg_match('/version (.*?) /', $strBuf, $ar_buf)) {
                    $result = $ar_buf[1];
                    if (preg_match('/SMP/', $strBuf)) {
                        $result .= ' (SMP)';
                    }
                    $this->sys->setKernel($result);
                }
            }
        }
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    protected function _uptime()
    {
        CommonFunctions::rfts('/proc/uptime', $buf, 1);
        $ar_buf = preg_split('/ /', $buf);
        $this->sys->setUptime(trim($ar_buf[0]));
    }

    /**
     * Number of Users
     *
     * @return void
     */
    private function _users()
    {
        if (CommonFunctions::executeProgram('who', '', $strBuf, PSI_DEBUG)) {
            if (strlen(trim($strBuf)) > 0) {
                $lines = preg_split('/\n/', $strBuf);
                $this->sys->setUsers(count($lines));
            }
        }
    }

    /**
     * Processor Load
     * optionally create a loadbar
     *
     * @return void
     */
    protected function _loadavg()
    {
        if (CommonFunctions::rfts('/proc/loadavg', $buf)) {
            $result = preg_split("/\s/", $buf, 4);
            // don't need the extra values, only first three
            unset($result[3]);
            $this->sys->setLoad(implode(' ', $result));
        }
        if (PSI_LOAD_BAR) {
            $this->sys->setLoadPercent($this->_parseProcStat('cpu'));
        }
    }

    /**
     * fill the load for a individual cpu, through parsing /proc/stat for the specified cpu
     *
     * @param String $cpuline cpu for which load should be meassured
     *
     * @return Integer
     */
    protected function _parseProcStat($cpuline)
    {
        if (is_null($this->_cpu_loads)) {
            $this->_cpu_loads = array();

            if (CommonFunctions::rfts('/proc/stat', $buf)) {
                if (preg_match_all('/^(cpu[0-9]*) (.*)/m', $buf, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $line) {
                        $cpu = $line[1];
                        $buf2 = $line[2];

                        $this->_cpu_loads[$cpu] = array();

                        $ab = 0;
                        $ac = 0;
                        $ad = 0;
                        $ae = 0;
                        sscanf($buf2, "%Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                        $this->_cpu_loads[$cpu]['load'] = $ab + $ac + $ad; // cpu.user + cpu.sys
                        $this->_cpu_loads[$cpu]['total'] = $ab + $ac + $ad + $ae; // cpu.total
                    }
                }
            }
            // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
            if (PSI_LOAD_BAR) {
                sleep(1);
            }
            if (CommonFunctions::rfts('/proc/stat', $buf)) {
                if (preg_match_all('/^(cpu[0-9]*) (.*)/m', $buf, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $line) {
                        $cpu = $line[1];
                        $buf2 = $line[2];

                        $ab = 0;
                        $ac = 0;
                        $ad = 0;
                        $ae = 0;
                        sscanf($buf2, "%Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                        $load2 = $ab + $ac + $ad; // cpu.user + cpu.sys
                        $total2 = $ab + $ac + $ad + $ae; // cpu.total
                        $total = $this->_cpu_loads[$cpu]['total'];
                        $load = $this->_cpu_loads[$cpu]['load'];
                        $this->_cpu_loads[$cpu] = 0;
                        if ($total > 0 && $total2 > 0 && $load > 0 && $load2 > 0 && $total2 != $total && $load2 != $load) {
                            $this->_cpu_loads[$cpu] = (100 * ($load2 - $load)) / ($total2 - $total);
                        }
                    }
                }
            }
        }

        
        if (isset($this->_cpu_loads[$cpuline])) {
            return $this->_cpu_loads[$cpuline];
        }
        return 0;
    }

    /**
     * CPU information
     * All of the tags here are highly architecture dependant.
     *
     * @return void
     */
    protected function _cpuinfo()
    {
        if (CommonFunctions::rfts('/proc/cpuinfo', $bufr)) {
            $processors = preg_split('/\s?\n\s?\n/', trim($bufr));
            $procname = null;
            foreach ($processors as $processor) {
                $proc = null;
                $arch = null;
                $dev = new CpuDevice();
                $details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($details as $detail) {
                    $arrBuff = preg_split('/\s*:\s*/', trim($detail));
                    if (count($arrBuff) == 2) {
                        switch (strtolower($arrBuff[0])) {
                        case 'processor':
                            $proc = trim($arrBuff[1]);
                            if (is_numeric($proc)) {
                                if (strlen($procname)>0) {
                                    $dev->setModel($procname);
                                }
                            } else {
                                $procname = $proc;
                                $dev->setModel($procname);
                            }
                            break;
                        case 'model name':
                        case 'cpu model':
                        case 'cpu':
                            $dev->setModel($arrBuff[1]);
                            break;
                        case 'cpu mhz':
                        case 'clock':
                            if ($arrBuff[1] > 0) { //openSUSE fix
                                $dev->setCpuSpeed($arrBuff[1]);
                            }
                            break;
                        case 'cycle frequency [hz]':
                            $dev->setCpuSpeed($arrBuff[1] / 1000000);
                            break;
                        case 'cpu0clktck':
                            $dev->setCpuSpeed(hexdec($arrBuff[1]) / 1000000); // Linux sparc64
                            break;
                        case 'l2 cache':
                        case 'cache size':
                            $dev->setCache(preg_replace("/[a-zA-Z]/", "", $arrBuff[1]) * 1024);
                            break;
                        case 'initial bogomips':
                        case 'bogomips':
                        case 'cpu0bogo':
                            $dev->setBogomips($arrBuff[1]);
                            break;
                        case 'flags':
                            if (preg_match("/ vmx/",$arrBuff[1])) {
                                $dev->setVirt("vmx");
                            } elseif (preg_match("/ svm/",$arrBuff[1])) {
                                $dev->setVirt("svm");
                            } elseif (preg_match("/ hypervisor/",$arrBuff[1])) {
                                $dev->setVirt("hypervisor");
                            }
                            break;
                        case 'i size':
                        case 'd size':
                            if ($dev->getCache() === null) {
                                $dev->setCache($arrBuff[1] * 1024);
                            } else {
                                $dev->setCache($dev->getCache() + ($arrBuff[1] * 1024));
                            }
                            break;
                        case 'cpu architecture':
                            $arch = trim($arrBuff[1]);
                            break;
                        }
                    }
                }
                // sparc64 specific code follows
                // This adds the ability to display the cache that a CPU has
                // Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
                // Modified by Tom Weustink <freshy98@gmx.net> in 2004
                $sparclist = array('SUNW,UltraSPARC@0,0', 'SUNW,UltraSPARC-II@0,0', 'SUNW,UltraSPARC@1c,0', 'SUNW,UltraSPARC-IIi@1c,0', 'SUNW,UltraSPARC-II@1c,0', 'SUNW,UltraSPARC-IIe@0,0');
                foreach ($sparclist as $name) {
                    if (CommonFunctions::rfts('/proc/openprom/'.$name.'/ecache-size', $buf, 1, 32, false)) {
                        $dev->setCache(base_convert($buf, 16, 10));
                    }
                }
                // sparc64 specific code ends

                // XScale detection code
                if (($arch === "5TE") && ($dev->getBogomips() != null)) {
                    $dev->setCpuSpeed($dev->getBogomips()); //BogoMIPS are not BogoMIPS on this CPU, it's the speed
                    $dev->setBogomips(null); // no BogoMIPS available, unset previously set BogoMIPS
                }

                if ($proc != null) {
                    if (!is_numeric($proc)) {
                        $proc = 0;
                    }
                    // variable speed processors specific code follows
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_cur_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeed($buf / 1000);
                    } elseif (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/scaling_cur_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeed($buf / 1000);
                    }
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_max_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeedMax($buf / 1000);
                    }
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_min_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeedMin($buf / 1000);
                    }
                    // variable speed processors specific code ends
                    if (PSI_LOAD_BAR) {
                            $dev->setLoad($this->_parseProcStat('cpu'.$proc));
                    }

                    if (CommonFunctions::rfts('/proc/acpi/thermal_zone/THRM/temperature', $buf, 1, 4096, false)) {
                        $dev->setTemp(substr($buf, 25, 2));
                    }
                    if ($dev->getModel() === "") {
                        $dev->setModel("unknown");
                    }
                    $this->sys->setCpus($dev);
                }
            }
        }
    }

    /**
     * PCI devices
     *
     * @return void
     */
    private function _pci()
    {
        if (!$arrResults = Parser::lspci()) {
            if (CommonFunctions::rfts('/proc/pci', $strBuf, 0, 4096, false)) {
                $booDevice = false;
                $arrBuf = preg_split("/\n/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($arrBuf as $strLine) {
                    if (preg_match('/Bus/', $strLine)) {
                        $booDevice = true;
                        continue;
                    }
                    if ($booDevice) {
                        list($strKey, $strValue) = preg_split('/: /', $strLine, 2);
                        if (!preg_match('/bridge/i', $strKey) && !preg_match('/USB/i ', $strKey)) {
                            $dev = new HWDevice();
                            $dev->setName(preg_replace('/\([^\)]+\)\.$/', '', trim($strValue)));
                            $this->sys->setPciDevices($dev);
                        }
                        $booDevice = false;
                    }
                }
            }
        } else {
            foreach ($arrResults as $dev) {
                $this->sys->setPciDevices($dev);
            }
        }
    }

    /**
     * IDE devices
     *
     * @return void
     */
    private function _ide()
    {
        $bufd = CommonFunctions::gdc('/proc/ide', false);
        foreach ($bufd as $file) {
            if (preg_match('/^hd/', $file)) {
                $dev = new HWDevice();
                $dev->setName(trim($file));
                if (CommonFunctions::rfts("/proc/ide/".$file."/media", $buf, 1)) {
                    if (trim($buf) == 'disk') {
                        if (CommonFunctions::rfts("/proc/ide/".$file."/capacity", $buf, 1, 4096, false) || CommonFunctions::rfts("/sys/block/".$file."/size", $buf, 1, 4096, false)) {
                            $dev->setCapacity(trim($buf) * 512 / 1024);
                        }
                    }
                }
                if (CommonFunctions::rfts("/proc/ide/".$file."/model", $buf, 1)) {
                    $dev->setName($dev->getName().": ".trim($buf));
                }
                $this->sys->setIdeDevices($dev);
            }
        }
    }

    /**
     * SCSI devices
     *
     * @return void
     */
    private function _scsi()
    {
        $get_type = false;
        $device = null;
        if (CommonFunctions::executeProgram('lsscsi', '-c', $bufr, PSI_DEBUG) || CommonFunctions::rfts('/proc/scsi/scsi', $bufr, 0, 4096, PSI_DEBUG)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/Vendor: (.*) Model: (.*) Rev: (.*)/i', $buf, $devices)) {
                    $get_type = true;
                    $device = $devices;
                    continue;
                }
                if ($get_type) {
                    preg_match('/Type:\s+(\S+)/i', $buf, $dev_type);
                    $dev = new HWDevice();
                    $dev->setName($device[1].' '.$device[2].' ('.$dev_type[1].')');
                    $this->sys->setScsiDevices($dev);
                    $get_type = false;
                }
            }
        }
    }

    /**
     * USB devices
     *
     * @return array
     */
    private function _usb()
    {
        $devnum = -1;
        if (!CommonFunctions::executeProgram('lsusb', '', $bufr, PSI_DEBUG)) {
            if (CommonFunctions::rfts('/proc/bus/usb/devices', $bufr, 0, 4096, false)) {
                $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($bufe as $buf) {
                    if (preg_match('/^T/', $buf)) {
                        $devnum += 1;
                        $results[$devnum] = "";
                    } elseif (preg_match('/^S:/', $buf)) {
                        list($key, $value) = preg_split('/: /', $buf, 2);
                        list($key, $value2) = preg_split('/=/', $value, 2);
                        if (trim($key) != "SerialNumber") {
                            $results[$devnum] .= " ".trim($value2);
                        }
                    }
                }
                foreach ($results as $var) {
                    $dev = new HWDevice();
                    $dev->setName($var);
                    $this->sys->setUsbDevices($dev);
                }
            }
        } else {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                $device = preg_split("/ /", $buf, 7);
                if (isset($device[6]) && trim($device[6]) != "") {
                    $dev = new HWDevice();
                    $dev->setName(trim($device[6]));
                    $this->sys->setUsbDevices($dev);
                }
            }
        }
    }

    /**
     * Network devices
     * includes also rx/tx bytes
     *
     * @return void
     */
    protected function _network()
    {
        if (CommonFunctions::rfts('/proc/net/dev', $bufr, 0, 4096, PSI_DEBUG)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/:/', $buf)) {
                    list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
                    $stats = preg_split('/\s+/', trim($stats_list));
                    $dev = new NetDevice();
                    $dev->setName(trim($dev_name));
                    $dev->setRxBytes($stats[0]);
                    $dev->setTxBytes($stats[8]);
                    $dev->setErrors($stats[2] + $stats[10]);
                    $dev->setDrops($stats[3] + $stats[11]);
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS) && (CommonFunctions::executeProgram('ifconfig', trim($dev_name).' 2>/dev/null', $bufr2, PSI_DEBUG))) {
                        $bufe2 = preg_split("/\n/", $bufr2, -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($bufe2 as $buf2) {
//                            if (preg_match('/^'.trim($dev_name).'\s+Link\sencap:Ethernet\s+HWaddr\s(\S+)/i', $buf2, $ar_buf2)
                            if (preg_match('/\s+encap:Ethernet\s+HWaddr\s(\S+)/i', $buf2, $ar_buf2)
                             || preg_match('/^\s+ether\s+(\S+)\s+txqueuelen/i', $buf2, $ar_buf2))
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').preg_replace('/:/', '-', $ar_buf2[1]));
                            elseif (preg_match('/^\s+inet\saddr:(\S+)\s+P-t-P:(\S+)/i', $buf2, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask.+destination\s+(\S+)/i', $buf2, $ar_buf2)) {
                                    if ($ar_buf2[1] != $ar_buf2[2]) {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                    } else {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                    }
                                 } elseif (preg_match('/^\s+inet\saddr:(\S+)/i', $buf2, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $buf2, $ar_buf2)
                                  || preg_match('/^'.trim($dev_name).':\s+ip\s+(\S+)\s+mask/i', $buf2, $ar_buf2)
                                  || preg_match('/^\s+inet6\saddr:\s([^\/]+)(.+)\s+Scope:[GH]/i', $buf2, $ar_buf2)
                                  || preg_match('/^\s+inet6\s+(\S+)\s+prefixlen(.+)((<global>)|(<host>))/i', $buf2, $ar_buf2))
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                        }
                    }
                    $this->sys->setNetDevices($dev);
                }
            }
        } elseif (CommonFunctions::executeProgram('ifconfig', '', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $notwas = true;
            foreach ($lines as $line) {
                if (preg_match("/^([^\s:]+)/", $line, $ar_buf)) {
                    if (!$notwas) {
                        $dev->setErrors($errors);
                        $dev->setDrops($drops);
                        $this->sys->setNetDevices($dev);
                    }
                    $errors = 0;
                    $drops = 0;
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[1]);
                    $notwas = false;
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                        if (preg_match('/^'.$ar_buf[1].'\s+Link\sencap:Ethernet\s+HWaddr\s(\S+)/i', $line, $ar_buf2))
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').preg_replace('/:/', '-', $ar_buf2[1]));
                        elseif (preg_match('/^'.$ar_buf[1].':\s+ip\s+(\S+)\s+mask/i', $line, $ar_buf2))
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                    }
                } else {
                    if (!$notwas) {
                        if (preg_match('/\sRX bytes:(\d+)\s/i', $line, $ar_buf2)) {
                            $dev->setRxBytes($ar_buf2[1]);
                        }
                        if (preg_match('/\sTX bytes:(\d+)\s/i', $line, $ar_buf2)) {
                            $dev->setTxBytes($ar_buf2[1]);
                        }

                        if (preg_match('/\sRX packets:\d+\serrors:(\d+)\sdropped:(\d+)/i', $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[2];
                        } elseif (preg_match('/\sTX packets:\d+\serrors:(\d+)\sdropped:(\d+)/i', $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[2];
                        }

                        if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            if (preg_match('/\s+encap:Ethernet\s+HWaddr\s(\S+)/i', $line, $ar_buf2)
                             || preg_match('/^\s+ether\s+(\S+)\s+txqueuelen/i', $line, $ar_buf2))
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').preg_replace('/:/', '-', $ar_buf2[1]));
                            elseif (preg_match('/^\s+inet\saddr:(\S+)\s+P-t-P:(\S+)/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask.+destination\s+(\S+)/i', $line, $ar_buf2)) {
                                    if ($ar_buf2[1] != $ar_buf2[2]) {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                    } else {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                    }
                                 } elseif (preg_match('/^\s+inet\saddr:(\S+)/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet6\saddr:\s([^\/]+)(.+)\s+Scope:[GH]/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet6\s+(\S+)\s+prefixlen(.+)((<global>)|(<host>))/i', $line, $ar_buf2))
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);

                        }
                    }
                }
            }
            if (!$notwas) {
                $dev->setErrors($errors);
                $dev->setDrops($drops);
                $this->sys->setNetDevices($dev);
            }
        }
    }

    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    protected function _memory()
    {
        if (CommonFunctions::rfts('/proc/meminfo', $mbuf)) {
            $bufe = preg_split("/\n/", $mbuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemTotal($ar_buf[1] * 1024);
                } elseif (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemFree($ar_buf[1] * 1024);
                } elseif (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemCache($ar_buf[1] * 1024);
                } elseif (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemBuffer($ar_buf[1] * 1024);
                }
            }
            $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
            // values for splitting memory usage
            if ($this->sys->getMemCache() !== null && $this->sys->getMemBuffer() !== null) {
                $this->sys->setMemApplication($this->sys->getMemUsed() - $this->sys->getMemCache() - $this->sys->getMemBuffer());
            }
            if (CommonFunctions::rfts('/proc/swaps', $sbuf, 0, 4096, false)) {
                $swaps = preg_split("/\n/", $sbuf, -1, PREG_SPLIT_NO_EMPTY);
                unset($swaps[0]);
                foreach ($swaps as $swap) {
                    $ar_buf = preg_split('/\s+/', $swap, 5);
                    $dev = new DiskDevice();
                    $dev->setMountPoint($ar_buf[0]);
                    $dev->setName("SWAP");
                    $dev->setTotal($ar_buf[2] * 1024);
                    $dev->setUsed($ar_buf[3] * 1024);
                    $dev->setFree($dev->getTotal() - $dev->getUsed());
                    $this->sys->setSwapDevices($dev);
                }
            }
        }
    }

    /**
     * filesystem information
     *
     * @return void
     */
    private function _filesystems()
    {
        $arrResult = Parser::df("-P 2>/dev/null");
        foreach ($arrResult as $dev) {
            $this->sys->setDiskDevices($dev);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    private function _distro()
    {
        $this->sys->setDistribution("Linux");
        $list = @parse_ini_file(APP_ROOT."/data/distros.ini", true);
        if (!$list) {
            return;
        }
        // We have the '2>/dev/null' because Ubuntu gives an error on this command which causes the distro to be unknown
        if (CommonFunctions::executeProgram('lsb_release', '-a 2>/dev/null', $distro_info, PSI_DEBUG) && (strlen(trim($distro_info)) > 0)) {
            $distro_tmp = preg_split("/\n/", $distro_info, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($distro_tmp as $info) {
                $info_tmp = preg_split('/:/', $info, 2);
                if ( isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "") &&
                     isset($distro_tmp[1]) && !is_null($distro_tmp[1]) && (trim($distro_tmp[1]) != "") ) {
                    $distro[trim($info_tmp[0])] = trim($info_tmp[1]);
                }
            }
            if (!isset($distro['Distributor ID']) && !isset($distro['Description'])) { // Systems like StartOS
                if ( isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "") ) {
                    $this->sys->setDistribution(trim($distro_tmp[0]));
                    if ( preg_match('/^(\S+)\s*/', $distro_tmp[0], $id_buf)
                        && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                }
            } else {
                if (isset($distro['Description'])
                   && preg_match('/^NAME=\s*(.+)\s*$/', $distro['Description'], $name_tmp)) {
                   $distro['Description'] = $name_tmp[1];
                }
                if (isset($distro['Description'])
                   && ($distro['Description'] != "n/a")
                   && !isset($distro['Distributor ID']) ) {
                    $this->sys->setDistribution($distro['Description']);
                } elseif (isset($distro['Description'])
                   && ($distro['Description'] != "n/a")
                   && isset($distro['Distributor ID'])
                   && ($distro['Distributor ID'] != "n/a")
                   && ($distro['Description'] != $distro['Distributor ID'])) {
                   $this->sys->setDistribution($distro['Description']);
                } elseif (isset($distro['Distributor ID']) && ($distro['Distributor ID'] != "n/a")) {
                    $this->sys->setDistribution($distro['Distributor ID']);
                    if (isset($distro['Release']) && ($distro['Release'] != "n/a")) {
                        $this->sys->setDistribution($this->sys->getDistribution()." ".$distro['Release']);
                    }
                    if (isset($distro['Codename']) && ($distro['Codename'] != "n/a")) {
                        $this->sys->setDistribution($this->sys->getDistribution()." (".$distro['Codename'].")");
                    }
                }
                if (isset($distro['Distributor ID']) && ($distro['Distributor ID'] != "n/a") && isset($list[$distro['Distributor ID']]['Image'])) {
                    $this->sys->setDistributionIcon($list[$distro['Distributor ID']]['Image']);
                }
            }
        } else {
            /* default error handler */
            if (function_exists('errorHandlerPsi')) {
                restore_error_handler();
            }
            /* fatal errors only */
            $old_err_rep = error_reporting();
            error_reporting(E_ERROR);

            // Fall back in case 'lsb_release' does not exist but exist /etc/lsb-release
            if (CommonFunctions::fileexists($filename="/etc/lsb-release")
               && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
               && preg_match('/^DISTRIB_ID="?([^"\n]+)"?/m', $buf, $id_buf) ) {
                if ( preg_match('/^DISTRIB_DESCRIPTION="?([^"\n]+)"?/m', $buf, $desc_buf)
                   && (trim($desc_buf[1])!=trim($id_buf[1])) ) {
                    $this->sys->setDistribution(trim($desc_buf[1]));
                } else {
                    if (isset($list[trim($id_buf[1])]['Name'])) {
                        $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                    } else {
                        $this->sys->setDistribution(trim($id_buf[1]));
                    }
                    if (preg_match('/^DISTRIB_RELEASE="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                    }
                    if (preg_match('/^DISTRIB_CODENAME="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution($this->sys->getDistribution()." (".trim($vers_buf[1]).")");
                    }
                }
                if (isset($list[trim($id_buf[1])]['Image'])) {
                    $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                }
            } else { // otherwise find files specific for distribution
                foreach ($list as $section=>$distribution) {
                    if (!isset($distribution['Files'])) {
                        continue;
                    } else {
                        foreach (preg_split("/;/", $distribution['Files'], -1, PREG_SPLIT_NO_EMPTY) as $filename) {
                            if (CommonFunctions::fileexists($filename)) {
                                $distro = $distribution;
                                if (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="detection")) {
                                    $buf = "";
                                } elseif (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="execute")) {
                                    if (!CommonFunctions::executeProgram($filename, '2>/dev/null', $buf, PSI_DEBUG)) {
                                        $buf = "";
                                    }
                                } else {
                                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                                        $buf = "";
                                    } elseif (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="analyse")) {
                                        if ( preg_match('/^(\S+)\s*/', preg_replace('/^Red\s+/', 'Red', $buf), $id_buf)
                                           && isset($list[trim($id_buf[1])]['Image'])) {
                                            $distro = $list[trim($id_buf[1])];
                                        }
                                    }
                                }
                                if (isset($distro['Image'])) {
                                    $this->sys->setDistributionIcon($distro['Image']);
                                }
                                if (isset($distribution['Name'])) {
                                    if ( is_null($buf) || (trim($buf) == "") ) {
                                        $this->sys->setDistribution($distribution['Name']);
                                    } else {
                                        $this->sys->setDistribution($distribution['Name']." ".trim($buf));
                                    }
                                } else {
                                    if ( is_null($buf) || (trim($buf) == "") ) {
                                        $this->sys->setDistribution($section);
                                    } else {
                                        $this->sys->setDistribution(trim($buf));
                                    }
                                }
                                if (isset($distribution['Files2'])) {
                                    foreach (preg_split("/;/", $distribution['Files2'], -1, PREG_SPLIT_NO_EMPTY) as $filename2) {
                                        if (CommonFunctions::fileexists($filename2) && CommonFunctions::rfts($filename2, $buf, 0, 4096, false)) {
                                            if (preg_match('/^majorversion="?([^"\n]+)"?/m', $buf, $maj_buf)
                                               && preg_match('/^minorversion="?([^"\n]+)"?/m', $buf, $min_buf)) {
                                                $distr2=$maj_buf[1].'.'.$min_buf[1];
                                                if (preg_match('/^buildphase="?([^"\n]+)"?/m', $buf, $pha_buf) && ($pha_buf[1]!=="0")) {
                                                    $distr2.='.'.$pha_buf[1];
                                                }
                                                if (preg_match('/^buildnumber="?([^"\n]+)"?/m', $buf, $num_buf)) {
                                                    $distr2.='-'.$num_buf[1];
                                                }
                                                if (preg_match('/^builddate="?([^"\n]+)"?/m', $buf, $dat_buf)) {
                                                    $distr2.=' ('.$dat_buf[1].')';
                                                }
                                                $this->sys->setDistribution($this->sys->getDistribution()." ".$distr2);
                                            } else {
                                                $distr2=trim(substr($buf, 0, strpos($buf, "\n")));
                                                if ( !is_null($distr2) && ($distr2 != "") ) {
                                                    $this->sys->setDistribution($this->sys->getDistribution()." ".$distr2);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }
                                break 2;
                            }
                        }
                    }
                }
            }
            // if the distribution is still unknown
            if ($this->sys->getDistribution() == "Linux") {
                if ( CommonFunctions::fileexists($filename="/etc/DISTRO_SPECS")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRO_NAME=\'(.+)\'/m', $buf, $id_buf) ) {
                    if (isset($list[trim($id_buf[1])]['Name'])) {
                        $dist = trim($list[trim($id_buf[1])]['Name']);
                    } else {
                        $dist = trim($id_buf[1]);
                    }
                    if (preg_match('/^DISTRO_VERSION=(.+)/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution(trim($dist." ".trim($vers_buf[1])));
                    } else {
                        $this->sys->setDistribution($dist);
                    }
                    if (isset($list[trim($id_buf[1])]['Image'])) {
                        $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    } else {
                        if (isset($list['Puppy']['Image'])) {
                            $this->sys->setDistributionIcon($list['Puppy']['Image']);
                        }
                    }
                } elseif ( ( CommonFunctions::fileexists($filename="/etc/distro-release")
                        && CommonFunctions::rfts($filename, $buf, 1, 4096, false)
                        && !is_null($buf) && (trim($buf) != "") )
                    || ( CommonFunctions::fileexists($filename="/etc/system-release")
                        && CommonFunctions::rfts($filename, $buf, 1, 4096, false)
                        && !is_null($buf) && (trim($buf) != "") ) ) {
                    $this->sys->setDistribution(trim($buf));
                    if ( preg_match('/^(\S+)\s*/', preg_replace('/^Red\s+/', 'Red', $buf), $id_buf)
                        && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                } elseif ( CommonFunctions::fileexists($filename="/etc/solydxk/info")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRIB_ID="?([^"\n]+)"?/m', $buf, $id_buf) ) {
                    if ( preg_match('/^DESCRIPTION="?([^"\n]+)"?/m', $buf, $desc_buf)
                       && (trim($desc_buf[1])!=trim($id_buf[1])) ) {
                        $this->sys->setDistribution(trim($desc_buf[1]));
                    } else {
                        if (isset($list[trim($id_buf[1])]['Name'])) {
                            $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                        } else {
                            $this->sys->setDistribution(trim($id_buf[1]));
                        }
                        if (preg_match('/^RELEASE="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                            $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                        }
                        if (preg_match('/^CODENAME="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                            $this->sys->setDistribution($this->sys->getDistribution()." (".trim($vers_buf[1]).")");
                        }
                    }
                    if (isset($list[trim($id_buf[1])]['Image'])) {
                        $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    } else {
                        $this->sys->setDistributionIcon($list['SolydXK']['Image']);
                    }
                } elseif ( CommonFunctions::fileexists($filename="/etc/os-release")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && ( preg_match('/^TAILS_VERSION_ID="?([^"\n]+)"?/m', $buf, $tid_buf)
                   || preg_match('/^NAME="?([^"\n]+)"?/m', $buf, $id_buf) ) ) {
                    if ( preg_match('/^TAILS_VERSION_ID="?([^"\n]+)"?/m', $buf, $tid_buf) ) {
                        if (preg_match('/^TAILS_PRODUCT_NAME="?([^"\n]+)"?/m', $buf, $desc_buf)) {
                            $this->sys->setDistribution(trim($desc_buf[1])." ".trim($tid_buf[1]));
                        } else {
                            if (isset($list['Tails']['Name'])) {
                                $this->sys->setDistribution(trim($list['Tails']['Name'])." ".trim($tid_buf[1]));
                            } else {
                                $this->sys->setDistribution('Tails'." ".trim($tid_buf[1]));
                            }
                        }
                        $this->sys->setDistributionIcon($list['Tails']['Image']);
                    } else {
                        if (preg_match('/^PRETTY_NAME="?([^"\n]+)"?/m', $buf, $desc_buf)
                           && !preg_match('/\$/', $desc_buf[1])) { //if is not defined by variable
                            $this->sys->setDistribution(trim($desc_buf[1]));
                        } else {
                            if (isset($list[trim($id_buf[1])]['Name'])) {
                                $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                            } else {
                                $this->sys->setDistribution(trim($id_buf[1]));
                            }
                            if (preg_match('/^VERSION="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                                $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                            } elseif (preg_match('/^VERSION_ID="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                                $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                            }
                        }
                        if (isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                        }
                    }
                } elseif (CommonFunctions::fileexists($filename="/etc/debian_version")) {
                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                        $buf = "";
                    }
                    if (isset($list['Debian']['Image'])) {
                        $this->sys->setDistributionIcon($list['Debian']['Image']);
                    }
                    if (isset($list['Debian']['Name'])) {
                        if ( is_null($buf) || (trim($buf) == "")) {
                            $this->sys->setDistribution($list['Debian']['Name']);
                        } else {
                            $this->sys->setDistribution($list['Debian']['Name']." ".trim($buf));
                        }
                    } else {
                        if ( is_null($buf) || (trim($buf) == "") ) {
                            $this->sys->setDistribution('Debian');
                        } else {
                            $this->sys->setDistribution(trim($buf));
                        }
                    }
                }
            }
            /* restore error level */
            error_reporting($old_err_rep);
            /* restore error handler */
            if (function_exists('errorHandlerPsi')) {
                set_error_handler('errorHandlerPsi');
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_OS::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_distro();
        $this->_hostname();
        $this->_ip();
        $this->_kernel();
        $this->_uptime();
        $this->_users();
        $this->_cpuinfo();
        $this->_pci();
        $this->_ide();
        $this->_scsi();
        $this->_usb();
        $this->_network();
        $this->_memory();
        $this->_filesystems();
        $this->_loadavg();
    }
}
