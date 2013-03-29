<?php
/**
 * Linux System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_OS
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
 * @package   PSI_OS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Linux extends OS
{
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
    private function _hostname()
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
    private function _ip()
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
        if (CommonFunctions::executeProgram('uname', '-r', $strBuf, PSI_DEBUG)) {
            $result = trim($strBuf);
            if (CommonFunctions::executeProgram('uname', '-v', $strBuf, PSI_DEBUG)) {
                if (preg_match('/SMP/', $strBuf)) {
                    $result .= ' (SMP)';
                }
            }
            if (CommonFunctions::executeProgram('uname', '-m', $strBuf, PSI_DEBUG)) {
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
    private function _uptime()
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
    private function _loadavg()
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
    private function _parseProcStat($cpuline)
    {
        $load = 0;
        $load2 = 0;
        $total = 0;
        $total2 = 0;
        if (CommonFunctions::rfts('/proc/stat', $buf)) {
            $lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                if (preg_match('/^'.$cpuline.' (.*)/', $line, $matches)) {
                    $ab = 0;
                    $ac = 0;
                    $ad = 0;
                    $ae = 0;
                    sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                    $load = $ab + $ac + $ad; // cpu.user + cpu.sys
                    $total = $ab + $ac + $ad + $ae; // cpu.total
                    break;
                }
            }
        }
        // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
        if(PSI_LOAD_BAR) {
            sleep(1);
        }
        if (CommonFunctions::rfts('/proc/stat', $buf)) {
            $lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                if (preg_match('/^'.$cpuline.' (.*)/', $line, $matches)) {
                    $ab = 0;
                    $ac = 0;
                    $ad = 0;
                    $ae = 0;
                    sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                    $load2 = $ab + $ac + $ad;
                    $total2 = $ab + $ac + $ad + $ae;
                    break;
                }
            }
        }
        if ($total > 0 && $total2 > 0 && $load > 0 && $load2 > 0 && $total2 != $total && $load2 != $load) {
            return (100 * ($load2 - $load)) / ($total2 - $total);
        }
        return 0;
    }
    /**
     * CPU information
     * All of the tags here are highly architecture dependant.
     *
     * @return void
     */
    private function _cpuinfo()
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
                            $notwas = false;
                            break;
                        case 'cpu0clktck':
                            $dev->setCpuSpeed(hexdec($arrBuff[1]) / 1000000); // Linux sparc64
                            break;
                        case 'l2 cache':
                        case 'cache size':
                            $dev->setCache(preg_replace("/[a-zA-Z]/", "", $arrBuff[1]) * 1024);
                            break;
                        case 'bogomips':
                        case 'cpu0bogo':
                            $dev->setBogomips($arrBuff[1]);
                            break;
                        case 'flags':
                            if(preg_match("/ vmx/",$arrBuff[1])) {
                                $dev->setVirt("vmx");
                            }
                            else if(preg_match("/ svm/",$arrBuff[1])) {
                                $dev->setVirt("svm");
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
                if (($arch === "5TE") && ($dev->getBogomips() != null))  {
                    $dev->setCpuSpeed($dev->getBogomips()); //BogoMIPS are not BogoMIPS on this CPU, it's the speed
                    $dev->setBogomips(null); // no BogoMIPS available, unset previously set BogoMIPS
                }

                if ($proc != null) {
                    if (!is_numeric($proc)) {
                        $proc = 0;
                    }
                    // arm specific code follows
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_max_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeed($buf / 1000);
                    }
                    // arm specific code ends
                    if(PSI_LOAD_BAR) {
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
    private function _network()
    {
        if (CommonFunctions::rfts('/proc/net/dev', $bufr)) {
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
                            else if (preg_match('/^\s+inet\saddr:(\S+)\s+P-t-P:(\S+)/i', $buf2, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask.+destination\s+(\S+)/i', $buf2, $ar_buf2)) {
                                    if ($ar_buf2[1] != $ar_buf2[2]) {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                    } else {
                                         $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                    }
                                 }
                            else if (preg_match('/^\s+inet\saddr:(\S+)/i', $buf2, $ar_buf2)
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
        }
    }
    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    private function _memory()
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
            if (CommonFunctions::rfts('/proc/swaps', $sbuf)) {
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
        if (defined('PSI_OS') && (PSI_OS == 'Android')) {
            if (CommonFunctions::executeProgram('df', '2>/dev/null ', $df, PSI_DEBUG)) {
                $df = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
                if (CommonFunctions::executeProgram('mount', '', $mount, PSI_DEBUG)) {
                    $mount = preg_split("/\n/", $mount, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($mount as $mount_line) {
                        $mount_buf = preg_split('/\s+/', $mount_line);
                        if (count($mount_buf) == 6){
                            $mount_parm[$mount_buf[1]]['fstype'] = $mount_buf[2];
                            if (PSI_SHOW_MOUNT_OPTION) $mount_parm[$mount_buf[1]]['options'] = $mount_buf[3];
                            $mount_parm[$mount_buf[1]]['mountdev'] = $mount_buf[0];
                        }
                    }
                    foreach ($df as $df_line) {
                        if (preg_match("/(\S*)(\s+)(([0-9]+)([KMGT])(\s+)([0-9]+)([KMGT])(\s+)([0-9]+)([KMGT])(\s+)([0-9]+)$)/", $df_line, $df_buf)) {
                            if (preg_match('/^\/mnt\/asec\/com\./', $df_buf[1])) break;

                            $dev = new DiskDevice();
                            if (PSI_SHOW_MOUNT_POINT) $dev->setMountPoint($df_buf[1]);
                            if ($df_buf[5] == 'K') $dev->setTotal($df_buf[4] * 1024);
                            elseif ($df_buf[5] == 'M') $dev->setTotal($df_buf[4] * 1024*1024);
                            elseif ($df_buf[5] == 'G') $dev->setTotal($df_buf[4] * 1024*1024*1024);
                            elseif ($df_buf[5] == 'T') $dev->setTotal($df_buf[4] * 1024*1024*1024*1024);

                            if ($df_buf[8] == 'K') $dev->setUsed($df_buf[7] * 1024);
                            elseif ($df_buf[8] == 'M') $dev->setUsed($df_buf[7] * 1024*1024);
                            elseif ($df_buf[8] == 'G') $dev->setUsed($df_buf[7] * 1024*1024*1024);
                            elseif ($df_buf[8] == 'T') $dev->setUsed($df_buf[7] * 1024*1024*1024*1024);

                            if ($df_buf[11] == 'K') $dev->setFree($df_buf[10] * 1024);
                            elseif ($df_buf[11] == 'M') $dev->setFree($df_buf[10] * 1024*1024);
                            elseif ($df_buf[11] == 'G') $dev->setFree($df_buf[10] * 1024*1024*1024);
                            elseif ($df_buf[11] == 'T') $dev->setFree($df_buf[10] * 1024*1024*1024*1024);

                            if(isset($mount_parm[$df_buf[1]])) {
                                $dev->setFsType($mount_parm[$df_buf[1]]['fstype']);
                                $dev->setName($mount_parm[$df_buf[1]]['mountdev']);

                                if (PSI_SHOW_MOUNT_OPTION) {
                                    if (PSI_SHOW_MOUNT_CREDENTIALS) {
                                        $dev->setOptions($mount_parm[$df_buf[1]]['options']);
                                    } else {
                                        $mpo=$mount_parm[$df_buf[1]]['options'];

                                        $mpo=preg_replace('/(^guest,)|(^guest$)|(,guest$)/i', '', $mpo);
                                        $mpo=preg_replace('/,guest,/i', ',', $mpo);

                                        $mpo=preg_replace('/(^user=[^,]*,)|(^user=[^,]*$)|(,user=[^,]*$)/i', '', $mpo);
                                        $mpo=preg_replace('/,user=[^,]*,/i', ',', $mpo);

                                        $mpo=preg_replace('/(^username=[^,]*,)|(^username=[^,]*$)|(,username=[^,]*$)/i', '', $mpo);
                                        $mpo=preg_replace('/,username=[^,]*,/i', ',', $mpo);

                                        $mpo=preg_replace('/(^password=[^,]*,)|(^password=[^,]*$)|(,password=[^,]*$)/i', '', $mpo);
                                        $mpo=preg_replace('/,password=[^,]*,/i', ',', $mpo);

                                        $dev->setOptions($mpo);
                                    }
                                }
                            }
                            $this->sys->setDiskDevices($dev);
                        }
                    }
                }
            }
        } else {
            $arrResult = Parser::df("-P 2>/dev/null");
            foreach ($arrResult as $dev) {
                $this->sys->setDiskDevices($dev);
            }
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
        if (defined('PSI_OS') && (PSI_OS == 'Android')) {
            $buf = "";
            if (CommonFunctions::rfts('/system/build.prop', $lines, 0, 4096, false)
                && preg_match('/^ro\.build\.version\.release=([^\n]+)/m', $lines, $ar_buf)) {
                    $buf = $ar_buf[1];
            }
            if (isset($list['Android']['Image'])) {
                $this->sys->setDistributionIcon($list['Android']['Image']);
            }
            if (isset($list['Android']['Name'])) {
                if ( is_null($buf) || (trim($buf) == "")) {
                    $this->sys->setDistribution($list['Android']['Name']);
                } else {
                    $this->sys->setDistribution($list['Android']['Name']." ".trim($buf));
                }
            } else {
                if ( is_null($buf) || (trim($buf) == "") ) {
                    $this->sys->setDistribution('Android');
                } else {
                    $this->sys->setDistribution(trim($buf));
                }
            }
        // We have the '2>/dev/null' because Ubuntu gives an error on this command which causes the distro to be unknown
        } else if (CommonFunctions::executeProgram('lsb_release', '-a 2>/dev/null', $distro_info, PSI_DEBUG) && (strlen(trim($distro_info)) > 0)) {
            $distro_tmp = preg_split("/\n/", $distro_info, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($distro_tmp as $info) {
                $info_tmp = preg_split('/:/', $info, 2);
                if ( isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "") &&
                     isset($distro_tmp[1]) && !is_null($distro_tmp[1]) && (trim($distro_tmp[1]) != "") ) {
                    $distro[$info_tmp[0]] = trim($info_tmp[1]);
                }
            }
            if (!isset($distro['Distributor ID']) && !isset($distro['Description'])) {
                if ( isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "") ) {
                    $this->sys->setDistribution(trim($distro_tmp[0]));
                    if ( preg_match('/^(\S+)\s*/', $distro_tmp[0], $id_buf)
                        && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                }
            } else {
                if (isset($distro['Description'])) {
                    $this->sys->setDistribution($distro['Description']);
                } else if (isset($distro['Distributor ID'])) {
                    $this->sys->setDistribution($distro['Distributor ID']);
                }
                if (isset($distro['Distributor ID']) && isset($list[$distro['Distributor ID']]['Image'])) {
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

            // Fall back in case 'lsb_release' does not exist
            foreach ($list as $section=>$distribution) {
                if (!isset($distribution['Files'])) {
                    continue;
                } else {
                    foreach (preg_split("/;/", $distribution['Files'], -1, PREG_SPLIT_NO_EMPTY) as $filename) {
                        if (file_exists($filename)) {
                            if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                                $buf = "";
                            }
                            if (isset($distribution['Image'])) {
                                $this->sys->setDistributionIcon($distribution['Image']);
                            }
                            if (isset($distribution['Name'])) {
                                if (($distribution['Name'] == 'Synology') || is_null($buf) || (trim($buf) == "")) {
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
                            break 2;
                        }
                    }
                }
            }
            // Otherwise, if no files found
            if ($this->sys->getDistribution() == "Linux") {
                if ( file_exists($filename="/etc/lsb-release")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRIB_ID="?([^"\n]*)"?/m', $buf, $id_buf) ) {
                    if (preg_match('/^DISTRIB_DESCRIPTION="?([^"\n]*)"?/m', $buf, $desc_buf)) {
                        $this->sys->setDistribution(trim($desc_buf[1]));
                    } else {
                        if (isset($list[trim($id_buf[1])]['Name'])) {
                            $dist = trim($list[trim($id_buf[1])]['Name']);
                        } else {
                            $dist = trim($id_buf[1]);
                        }
                        if (preg_match('/^DISTRIB_RELEASE="?([^"\n]*)"?/m', $buf, $vers_buf)) {
                            $this->sys->setDistribution(trim($dist." ".trim($vers_buf[1])));
                        } else {
                            $this->sys->setDistribution($dist);
                        }
                    }
                    if (isset($list[trim($id_buf[1])]['Image'])) {
                        $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                } else
                if ( file_exists($filename="/etc/DISTRO_SPECS")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRO_NAME=\'(.*)\'/m', $buf, $id_buf) ) {
                    if (isset($list[trim($id_buf[1])]['Name'])) {
                        $dist = trim($list[trim($id_buf[1])]['Name']);
                    } else {
                        $dist = trim($id_buf[1]);
                    }
                    if (preg_match('/^DISTRO_VERSION=(.*)/m', $buf, $vers_buf)) {
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
                } else
                if (file_exists($filename="/etc/redhat-release")) {
                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                        $buf = "";
                    }
                    if ( is_null($buf) || (trim($buf) == "") ) {
                        if (isset($list['RedHat']['Name'])) {
                            $this->sys->setDistribution(trim($list['RedHat']['Name']));
                        } else {
                            $this->sys->setDistribution('RedHat');
                        }
                        if (isset($list['RedHat']['Image'])) {
                            $this->sys->setDistributionIcon($list['RedHat']['Image']);
                        }
                    } else {
                        $this->sys->setDistribution(trim($buf));
                        if ( preg_match('/^(\S+)\s*/', $buf, $id_buf)
                           && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                        } else {
                            if (isset($list['RedHat']['Image'])) {
                                $this->sys->setDistributionIcon($list['RedHat']['Image']);
                            }
                        }
                    }
                } else
                if (file_exists($filename="/etc/debian_version")){
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
                } else
                if (file_exists($filename="/usr/bin/crux")){
                    if (!CommonFunctions::executeProgram("crux", '2>/dev/null', $buf, PSI_DEBUG)) {
                        $buf = "";
                    }
                    if (isset($list['CRUX']['Image'])) {
                        $this->sys->setDistributionIcon($list['CRUX']['Image']);
                    }
                    if (isset($list['CRUX']['Name'])) {
                        if ( is_null($buf) || (trim($buf) == "")) {
                            $this->sys->setDistribution($list['CRUX']['Name']);
                        } else {
                            $this->sys->setDistribution($list['CRUX']['Name']." ".trim($buf));
                        }
                    } else {
                        if ( is_null($buf) || (trim($buf) == "") ) {
                            $this->sys->setDistribution('CRUX');
                        } else {
                            $this->sys->setDistribution(trim($buf));
                        }
                    }
                } else
                if (file_exists($filename="/etc/system-release")){ //last chance
                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                        $buf = "";
                    }
                    if ( !is_null($buf) && (trim($buf) != "")) {
                        $this->sys->setDistribution(trim($buf));
                        if ( preg_match('/^(\S+)\s*/', $buf, $id_buf)
                            && isset($list[trim($id_buf[1])]['Image'])) {
                                $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
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
    function build()
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
?>
