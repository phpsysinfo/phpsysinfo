<?php
/**
 * HP-UX System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI HPUX OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.HPUX.inc.php 596 2012-07-05 19:37:48Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * HP-UX sysinfo class
 * get all the required information from HP-UX system
 *
 * @category  PHP
 * @package   PSI HPUX OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class HPUX extends OS
{
    /**
     * Virtual Host Name
     *
     * @return void
     */
    private function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setHostname(getenv('SERVER_NAME'));
        } else {
            if (CommonFunctions::executeProgram('hostname', '', $ret)) {
                $this->sys->setHostname($ret);
            }
        }
    }

    /**
     * IP of the Virtual Host Name
     *
     *  @return void
     */
    private function _ip()
    {
        if (PSI_USE_VHOST === true) {
            $this->sys->setIp(gethostbyname($this->sys->getHostname()));
        } else {
            if (!($result = getenv('SERVER_ADDR'))) {
                $this->sys->setIp(gethostbyname($this->sys->getHostname()));
            } else {
                $this->sys->setIp($result);
            }
        }
    }

    /**
     * HP-UX Version
     *
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::executeProgram('uname', '-srvm', $ret)) {
            $this->sys->setKernel($ret);
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
        if (CommonFunctions::executeProgram('uptime', '', $buf)) {
            if (preg_match("/up (\d+) days,\s*(\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[3];
                $hours = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60);
            }
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    private function _users()
    {
        if (CommonFunctions::executeProgram('who', '-q', $ret)) {
            $who = preg_split('/=/', $ret, -1, PREG_SPLIT_NO_EMPTY);
            $this->sys->setUsers($who[1]);
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
        if (CommonFunctions::executeProgram('uptime', '', $buf)) {
            if (preg_match("/average: (.*), (.*), (.*)$/", $buf, $ar_buf)) {
                $this->sys->setLoad($ar_buf[1].' '.$ar_buf[2].' '.$ar_buf[3]);
            }
        }
    }

    /**
     * CPU information
     * All of the tags here are highly architecture dependant
     *
     * @return void
     */
    private function _cpuinfo()
    {
        if (CommonFunctions::rfts('/proc/cpuinfo', $bufr)) {
            $processors = preg_split('/\s?\n\s?\n/', trim($bufr));
            foreach ($processors as $processor) {
                $dev = new CpuDevice();
                $details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($details as $detail) {
                    $arrBuff = preg_split('/\s+:\s+/', trim($detail));
                    if (count($arrBuff) == 2) {
                        switch (strtolower($arrBuff[0])) {
                        case 'model name':
                        case 'cpu':
                            $dev->setModel($arrBuff[1]);
                            break;
                        case 'cpu mhz':
                        case 'clock':
                            $dev->setCpuSpeed($arrBuff[1]);
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
                        case 'bogomips':
                        case 'cpu0bogo':
                            $dev->setBogomips($arrBuff[1]);
                            break;
                        }
                    }
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
        if (CommonFunctions::rfts('/proc/pci', $bufr)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/Bus/', $buf)) {
                    $device = true;
                    continue;
                }
                if ($device) {
                    list($key, $value) = preg_split('/: /', $buf, 2);
                    if (!preg_match('/bridge/i', $key) && !preg_match('/USB/i', $key)) {
                        $dev = new HWDevice();
                        $dev->setName(preg_replace('/\([^\)]+\)\.$/', '', trim($value)));
                        $this->sys->setPciDevices($dev);
                    }
                    $device = false;
                }
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
                        if (CommonFunctions::rfts("/proc/ide/".$file."/capacity", $buf, 1, 4096, false)) {
                            $dev->setCapacity(trim($buf) * 512 / 1024);
                        }
                    }
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
        if (CommonFunctions::rfts('/proc/scsi/scsi', $bufr, 0, 4096, PSI_DEBUG)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/Vendor: (.*) Model: (.*) Rev: (.*)/i', $buf, $dev)) {
                    $get_type = true;
                    continue;
                }
                if ($get_type) {
                    preg_match('/Type:\s+(\S+)/i', $buf, $dev_type);
                    $dev = new HWDevice();
                    $dev->setName($dev[1].' '.$dev[2].' ('.$dev_type[1].')');
                    $this->sys->setScsiDevices($dev);
                    $get_type = false;
                }
            }
        }
    }

    /**
     * USB devices
     *
     * @return void
     */
    private function _usb()
    {
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
    }

    /**
     * Network devices
     * includes also rx/tx bytes
     *
     * @return void
     */
    private function _network()
    {
        if (CommonFunctions::executeProgram('netstat', '-ni | tail -n +2', $netstat)) {
            $lines = preg_split("/\n/", $netstat, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $ar_buf = preg_split("/\s+/", $line);
                if (! empty($ar_buf[0]) && ! empty($ar_buf[3])) {
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[0]);
                    $dev->setRxBytes($ar_buf[4]);
                    $dev->setTxBytes($ar_buf[6]);
                    $dev->setErrors($ar_buf[5] + $ar_buf[7]);
                    $dev->setDrops($ar_buf[8]);
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
        if (CommonFunctions::rfts('/proc/meminfo', $bufr)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/Mem:\s+(.*)$/', $buf, $ar_buf)) {
                    $ar_buf = preg_split('/\s+/', $ar_buf[1], 6);
                    $this->sys->setMemTotal($ar_buf[0]);
                    $this->sys->setMemUsed($ar_buf[1]);
                    $this->sys->setMemFree($ar_buf[2]);
                    $this->sys->setMemApplication($ar_buf[3]);
                    $this->sys->setMemBuffer($ar_buf[4]);
                    $this->sys->setMemCache($ar_buf[5]);
                }
                // Get info on individual swap files
                if (CommonFunctions::rfts('/proc/swaps', $swaps)) {
                    $swapdevs = preg_split("/\n/", $swaps, -1, PREG_SPLIT_NO_EMPTY);
                    for ($i = 1, $max = (sizeof($swapdevs) - 1); $i < $max; $i++) {
                        $ar_buf = preg_split('/\s+/', $swapdevs[$i], 6);
                        $dev = new DiskDevice();
                        $dev->setMountPoint($ar_buf[0]);
                        $dev->setName("SWAP");
                        $dev->setFsType('swap');
                        $dev->setTotal($ar_buf[2] * 1024);
                        $dev->setUsed($ar_buf[3] * 1024);
                        $dev->setFree($dev->getTotal() - $dev->getUsed());
                        $this->sys->setSwapDevices($dev);
                    }
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
        if (CommonFunctions::executeProgram('df', '-kP', $df, PSI_DEBUG)) {
            $mounts = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
            if (CommonFunctions::executeProgram('mount', '-v', $s, PSI_DEBUG)) {
                $lines = preg_split("/\n/", $s, -1, PREG_SPLIT_NO_EMPTY);
                while (list(, $line) = each($lines)) {
                    $a = preg_split('/ /', $line, -1, PREG_SPLIT_NO_EMPTY);
                    $fsdev[$a[0]] = $a[4];
                }
            }
            foreach ($mounts as $mount) {
                $ar_buf = preg_split("/\s+/", $mount, 6);
                $dev = new DiskDevice();
                $dev->setName($ar_buf[0]);
                $dev->setTotal($ar_buf[1] * 1024);
                $dev->setUsed($ar_buf[2] * 1024);
                $dev->setFree($ar_buf[3] * 1024);
                $dev->setMountPoint($ar_buf[5]);
                if (isset($fsdev[$ar_buf[0]])) {
                    $dev->setFsType($fsdev[$ar_buf[0]]);
                }
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
        $this->sys->setDistribution('HP-UX');
        $this->sys->setDistributionIcon('HPUX.png');
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
        $this->_loadavg();
        $this->_cpuinfo();
        $this->_pci();
        $this->_ide();
        $this->_scsi();
        $this->_usb();
        $this->_network();
        $this->_memory();
        $this->_filesystems();
    }
}
