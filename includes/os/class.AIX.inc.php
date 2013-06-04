<?php
/**
 * IBM AIX System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI AIX OS class
 * @author    Krzysztof Paz (kpaz@gazeta.pl) based on HPUX of Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2011 Krzysztof Paz
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.AIX.inc.php 287 2009-06-26 12:11:59Z Krzysztof Paz, IBM POLSKA
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
* IBM AIX sysinfo class
* get all the required information from IBM AIX system
*
* @category  PHP
* @package   PSI AIX OS class
* @author    Krzysztof Paz (kpaz@gazeta.pl) based on Michael Cramer <BigMichi1@users.sourceforge.net>
* @copyright 2011 Krzysztof Paz
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
* @version   Release: 3.0
* @link      http://phpsysinfo.sourceforge.net
*/
class AIX extends OS
{

    private $myprtconf = array();

    /**
     * Virtual Host Name
     * @return void
     */
    private function _hostname()
    {
        /*   if (PSI_USE_VHOST === true) {
               $this->sys->setHostname(getenv('SERVER_NAME'));
           } else {
               if (CommonFunctions::executeProgram('hostname', '', $ret)) {
                   $this->sys->setHostname($ret);
               }
           } */
        $this->sys->setHostname(getenv('SERVER_NAME'));

    }

    /**
     * IP of the Virtual Host Name
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
     * IBM AIX Version
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::executeProgram('oslevel', '', $ret1) && CommonFunctions::executeProgram('oslevel', '-s', $ret2)) {
            $this->sys->setKernel($ret1 . '   (' . $ret2 . ')');
        }
    }

    /**
     * UpTime
     * time the system is running
     * @return void
     */
    private function _uptime()
    {
        if (CommonFunctions::executeProgram('uptime', '', $buf)) {
            if (preg_match("/up (\d+) days,\s*(\d+):(\d+),/", $buf, $ar_buf) || preg_match("/up (\d+) day,\s*(\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[3];
                $hours = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60);
            }
        }
    }

    /**
     * Number of Users
     * @return void
     */
    private function _users()
    {
        if (CommonFunctions::executeProgram('who', '| wc -l', $buf, PSI_DEBUG)) {
            $this->sys->setUsers($buf);
        }
    }

    /**
     * Processor Load
     * optionally create a loadbar
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
     * @return void
     */
    private function _cpuinfo()
    {
        $dev = new CpuDevice();
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep Type', $cpudev);
        $dev->setModel($cpudev);
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt | grep Speed | awk \'{print $4}\'', $cpuspeed);
        $dev->setCpuSpeed($cpuspeed);
        //$dev->setCache('512000'); //-don't know howto guess cache size
        $this->sys->setCpus($dev);
    }

    /**
     * PCI devices
     * @return void
     */
    private function _pci()
    {
        // FIXME
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep PCI', $bufr);
        $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $dev = new HWDevice();
            $dev->setName($line);
            $this->sys->setPciDevices($dev);
        }
    }

    /**
     * IDE devices
     * @return void
     */
    private function _ide()
    {
        // FIXME
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep IDE', $bufr);
        $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $dev = new HWDevice();
            $dev->setName($line);
            $this->sys->setIdeDevices($dev);
            //$dev->setCapacity(trim($line???) * 512 / 1024);
        }
    }

    /**
     * SCSI devices
     * @return void
     */
    private function _scsi()
    {
        // FIXME
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep SCSI', $bufr);
        $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $dev = new HWDevice();
            $dev->setName($line);
            $this->sys->setScsiDevices($dev);
        }
    }

    /**
     * USB devices
     * @return void
     */
    private function _usb()
    {
        // FIXME
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep USB', $bufr);
        $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $dev = new HWDevice();
            $dev->setName($line);
            $this->sys->setUsbDevices($dev);
        }
    }

    /**
     * Network devices
     * includes also rx/tx bytes
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
                    //$dev->setDrops($ar_buf[8]);
                    $this->sys->setNetDevices($dev);
                }
            }
        }
    }

    /**
     * Physical memory information and Swap Space information
     * @return void
     */
    private function _memory()
    {
        CommonFunctions::executeProgram('cat', '/tmp/webprtconf.txt |grep Good|awk \'{print $4}\'', $mems);
        $this->sys->setMemTotal($mems*1024*1024);
        //FIXME
        $mems = 0;
        $this->sys->setMemUsed($mems);
        $this->sys->setMemFree($mems);
        $this->sys->setMemApplication($mems);
        $this->sys->setMemBuffer($mems);
        $this->sys->setMemCache($mems);

        /*
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
        */
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
        $this->sys->setDistribution('IBM AIX');
        $this->sys->setDistributionIcon('AIX.png');
    }

    /**
     * IBM AIX INFORMATIONs by K.PAZ
     * @return void
     */
    private function _myaixdata()
    {
        CommonFunctions::executeProgram('prtconf', '> /tmp/webprtconf.txt', $confret);
        CommonFunctions::rfts('/tmp/webprtconf.txt', $bufr);
        $this->myprtconf = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
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
        $this->_myaixdata();
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
