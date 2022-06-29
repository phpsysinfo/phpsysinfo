<?php
/**
 * SSH Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI SSH class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.SSH.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * SSH sysinfo class
 * get all the required information from SSH
 *
 * @category  PHP
 * @package   PSI SSH class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2022 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SSH extends GNU
{
    /**
     * content of the system status
     *
     * @var string
     */
    private $_sysstatus = null;

    /**
     * content of the system performance status
     *
     * @var string
     */
    private $_sysperformance = null;

    /**
     * content of the sys ver systeminfo
     *
     * @var string
     */
    private $_sysversysteminfo = null;

    /**
     * content of the show status
     *
     * @var string
     */
    private $_showstatus = null;

    /**
     * OS type
     *
     * @var string
     */
    private $_ostype = null;

    /**
     * check system type
     */
    public function __construct($blockname = false)
    {
        parent::__construct($blockname);
        $this->_ostype = $this->sys->getOS();
        switch ($this->_ostype) {
        case '4.2BSD':
        case 'AIX':
        case 'Darwin':
        case 'DragonFly':
        case 'FreeBSD':
        case 'HI-UX/MPP':
        case 'Haiku':
        case 'Minix':
        case 'NetBSD':
        case 'OpenBSD':
        case 'QNX':
        case 'SunOS':
            $this->error->addError("__construct()", "OS ".$this->_ostype. " is not supported via SSH");
            break;
        case 'GNU':
        case 'Linux':
            break;
        default:
            if ($this->getSystemStatus() !== '') {
                $this->_ostype = 'FortiOS';
                $this->sys->setOS('Linux');
            } elseif ($this->getSysVerSysteminfo() !== '') {
                $this->_ostype = 'DrayOS';
                $this->sys->setOS('DrayOS');
            }
        }
    }

    /**
     * get os specific encoding
     *
     * @see PSI_Interface_OS::getEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
//        if (($this->_ostype === 'FortiOS') || ($this->_ostype === 'DrayOS') || ($this->_ostype === 'SSH')) {
//            return 'UTF-8';
//        }
        //return null;
    }

    /**
     * get os specific language
     *
     * @see PSI_Interface_OS::getLanguage()
     *
     * @return string
     */
    public function getLanguage()
    {
        //return null;
    }

    private function getSystemStatus()
    {
        if ($this->_sysstatus === null) {
            if (CommonFunctions::executeProgram('get' ,'system status', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $this->_sysstatus = substr($resulte, strlen($resulto[1][0]));
            } else {
                $this->_sysstatus =  '';
            }
        }

        return $this->_sysstatus;
    }

    private function getSystemPerformance()
    {
        if ($this->_sysperformance === null) {
            if (CommonFunctions::executeProgram('get', 'system performance status', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $this->_sysperformance = substr($resulte, strlen($resulto[1][0]));
            } else {
                $this->_sysperformance =  '';
            }
        }

        return $this->_sysperformance;
    }

    private function getSysVerSysteminfo()
    {
        if ($this->_sysversysteminfo === null) {
            if (CommonFunctions::executeProgram('sys' ,'ver systeminfo', $resulte, false, PSI_EXEC_TIMEOUT_INT, '>') && ($resulte !== "")
               && preg_match('/([\s\S]+> sys ver systeminfo)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $this->_sysversysteminfo = substr($resulte, strlen($resulto[1][0]));
            } else {
                $this->_sysversysteminfo =  '';
            }
        }

        return $this->_sysversysteminfo;
    }

    private function getShowStatus()
    {
        if ($this->_showstatus === null) {
            if (CommonFunctions::executeProgram('show' ,'status', $resulte, false, PSI_EXEC_TIMEOUT_INT, '>') && ($resulte !== "")
               && preg_match('/([\s\S]+> show status)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $this->_showstatus = substr($resulte, strlen($resulto[1][0]));
            } else {
                $this->_showstatus =  '';
            }
        }

        return $this->_showstatus;
    }
    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    protected function _memory($mbuf = null, $sbuf = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (CommonFunctions::executeProgram('get', 'hardware memory', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_memory(substr($resulte, strlen($resulto[1][0])));
            }
            break;
        case 'DrayOS':
            if (($sysstat = $this->getSysVerSysteminfo()) !== '') {
                $machine= '';
                if (preg_match("/ Total memory usage : \d+ % \((\d+)K\/(\d+)K\)/", $sysstat, $buf)) {
                    $this->sys->setMemTotal($buf[2]*1024);
                    $this->sys->setMemUsed($buf[1]*1024);
                    $this->sys->setMemFree(($buf[2]-$buf[1])*1024);
                }
            }
            break;
        case 'GNU':
        case 'Linux':
            if (!CommonFunctions::executeProgram('cat', '/proc/meminfo', $mbuf, false) || ($mbuf === "")) {
                $mbuf = null;
            }
            if (!CommonFunctions::executeProgram('cat', '/proc/swaps', $sbuf, false) || ($sbuf === "")) {
                $sbuf = null;
            }
            if (($mbuf !== null) || ($sbuf !== null)) {
                parent::_memory($mbuf, $sbuf);
            }
        }

    }

    /**
     * USB devices
     *
     * @return void
     */
    protected function _usb($bufu = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            $bufr = '';
            if (CommonFunctions::executeProgram('fnsysctl', 'cat /proc/bus/usb/devices', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $resulti = substr($resulte, strlen($resulto[1][0]));
                if (preg_match('/(\n.*[\$#])$/', $resulti, $resulto, PREG_OFFSET_CAPTURE)) {
                    $bufr = substr($resulti, 0, $resulto[1][1]);
                    if (count(preg_split('/\n/', $bufr, -1, PREG_SPLIT_NO_EMPTY)) >= 2) {
                        parent::_usb($bufr);
                    }
                }
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_usb();
        }
    }

    /**
     * Network devices
     * includes also rx/tx bytes
     *
     * @return void
     */
    protected function _network($bufr = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            $bufr = '';
            if (CommonFunctions::executeProgram('fnsysctl', 'ifconfig', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $resulti = substr($resulte, strlen($resulto[1][0]));
                if (preg_match('/(\n.*[\$#])$/', $resulti, $resulto, PREG_OFFSET_CAPTURE)) {
                    $bufr = substr($resulti, 0, $resulto[1][1]);
                    if (count(preg_split('/\n/', $bufr, -1, PREG_SPLIT_NO_EMPTY)) < 2) {
                        $bufr = '';
                    }
                }
            }

            if ($bufr !== '') {
                parent::_network($bufr);
            } else {
                $netdevs = array();
                if (CommonFunctions::executeProgram('diagnose', 'ip address list', $resulte, false) && ($resulte !== "")
                   && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                    $strBuf = substr($resulte, strlen($resulto[1][0]));
                    $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) if (preg_match('/^IP=(\S+)->.+ devname=(\S+)$/', $line, $buf)) {
                        if (!isset($netdevs[$buf[2]])) {
                            $netdevs[$buf[2]] = $buf[1];
                        } else {
                            $netdevs[$buf[2]] .= ';'.$buf[1];
                        }
                    }
                }
                if (CommonFunctions::executeProgram('diagnose', 'ipv6 address list', $resulte, false) && ($resulte !== "")
                   && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                    $strBuf = substr($resulte, strlen($resulto[1][0]));
                    $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) if (preg_match('/ devname=(\S+) .+ addr=(\S+)/', $line, $buf)) {
                        if (!preg_match('/^fe80::/i', $buf[2])) {
                            if (!isset($netdevs[$buf[1]])) {
                                $netdevs[$buf[1]] = $buf[2];
                            } else {
                                $netdevs[$buf[1]] .= ';'.$buf[2];
                            }
                        }
                    }
                }

                foreach ($netdevs as $netname=>$netinfo) {
                    if (!preg_match('/^vsys_/i', $netname)) {
                        $dev = new NetDevice();
//                        if ($netname === 'root') {
//                            $dev->setName('lo');
//                        } else {
                            $dev->setName($netname);
//                        }
                        $this->sys->setNetDevices($dev);
                        $dev->setInfo($netinfo);
                    }
                }
            }
            break;
        case 'DrayOS':
            $macarray = array();
            if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS) && (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR)) {
                if (CommonFunctions::executeProgram('sys' ,'iface', $resulte, false, PSI_EXEC_TIMEOUT_INT, '>') && ($resulte !== "")
                   && preg_match('/([\s\S]+> sys iface)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                    $lines = preg_split("/\n/", substr($resulte, strlen($resulto[1][0])), -1, PREG_SPLIT_NO_EMPTY);
                    $ipaddr = 'LAN';
                    foreach ($lines as $line) {
                        if (preg_match("/^IP Address:\s+([\d\.]+)\s/", trim($line), $ar_buf)) {
                            if ($ipaddr === false) {
                                $ipaddr = $ar_buf[1];
                            }
                        } elseif (preg_match("/^MAC:\s+([\d\-A-F]+)/", trim($line), $ar_buf)) {
                            if ($ipaddr !== '0.0.0.0') {
                                $macarray[$ipaddr] = $ar_buf[1];
                            }
                            $ipaddr = false;
                        }
                    }
                }
            }

            $lantxrate = false;
            $lanrxrate = false;
            if (defined('PSI_SHOW_NETWORK_ACTIVE_SPEED') && PSI_SHOW_NETWORK_ACTIVE_SPEED) {
                if ((($bufr = $this->getShowStatus()) !== '') && preg_match('/IP Address:[\d\.]+[ ]+Tx Rate:(\d+)[ ]+Rx Rate:(\d+)/m', $bufr, $ar_buf)) {
                    $lantxrate = $ar_buf[1];
                    $lanrxrate = $ar_buf[2];
                }
            }

            $notwaslan = true;
            if (CommonFunctions::executeProgram('show' ,'lan', $resulte, false, PSI_EXEC_TIMEOUT_INT, '>') && ($resulte !== "")
               && preg_match('/([\s\S]+> show lan)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $lines = preg_split("/\n/", substr($resulte, strlen($resulto[1][0])), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    if (preg_match("/^\[V\](\S+)\s+([\d\.]+)\s/", trim($line), $ar_buf)) {
                        $dev = new NetDevice();
                        $dev->setName($ar_buf[1]);
                        if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            $dev->setInfo($ar_buf[2]);
                            if (isset($macarray['LAN'])) {
                                $dev->setInfo($macarray['LAN'].';'.$ar_buf[2]);
                            } else {
                                $dev->setInfo($ar_buf[2]);
                            }
                        }
                        if ($lantxrate !== false) {
                            $dev->setTxRate($lantxrate);
                        }
                        if ($lanrxrate !== false) {
                            $dev->setRxRate($lanrxrate);
                        }
                        $this->sys->setNetDevices($dev);
                        if (preg_match('/^LAN/', $ar_buf[1])) {
                            $notwaslan = false;
                        }
                    }
                }
            }
            if (($bufr = $this->getShowStatus()) !== '') {
                $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
                $last = false;
                $dev = null;
                foreach ($lines as $line) {
                    if (preg_match("/^(.+) Status/", trim($line), $ar_buf)) {
                        if (($last !== false) && (($last !== 'LAN') || $notwaslan)) {
                            $this->sys->setNetDevices($dev);
                        }
                        $dev = new NetDevice();
                        $last = preg_replace('/\s+/', '', $ar_buf[1]);
                        $dev->setName($last);
                    } else {
                        if ($last !== false) {
                            if (preg_match('/ IP:([\d\.]+)[ ]+GW/', $line, $ar_buf) || preg_match('/IP Address:([\d\.]+)[ ]+Tx/', $line, $ar_buf)) {
                                if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                                    if ($last === 'LAN') {
                                        if (isset($macarray['LAN'])) {
                                            $dev->setInfo($macarray['LAN'].';'.$ar_buf[1]);
                                        }
                                        if ($lantxrate !== false) {
                                            $dev->setTxRate($lantxrate);
                                        }
                                        if ($lanrxrate !== false) {
                                            $dev->setRxRate($lanrxrate);
                                        }
                                    } elseif (isset($macarray[$ar_buf[1]])) {
                                        $dev->setInfo($macarray[$ar_buf[1]].';'.$ar_buf[1]);
                                    } else {
                                       $dev->setInfo($ar_buf[1]);
                                    }
                                }
                            } elseif (preg_match('/TX Packets:\d+[ ]+TX Rate\(bps\):(\d+)[ ]+RX Packets:\d+[ ]+RX Rate\(bps\):(\d+)/', $line, $ar_buf)) {
                                if (defined('PSI_SHOW_NETWORK_ACTIVE_SPEED') && PSI_SHOW_NETWORK_ACTIVE_SPEED) {
                                    $dev->setTxRate($ar_buf[1]);
                                    $dev->setRxRate($ar_buf[2]);
                                }
                            }
                        }
                    }
                }
                if (($last !== false) && (($last !== 'LAN') || $notwaslan)) {
                    $this->sys->setNetDevices($dev);
                }
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_network();
        }
    }

    /**
     * CPU information
     * All of the tags here are highly architecture dependant.
     *
     * @return void
     */
    protected function _cpuinfo($bufr = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (CommonFunctions::executeProgram('get', 'hardware cpu', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_cpuinfo(substr($resulte, strlen($resulto[1][0])));
            }
            break;
        case 'DrayOS':
            if (preg_match_all("/CPU(\d+) speed:[ ]*(\d+) MHz/m", $sysinfo = $this->getSysVerSysteminfo(), $bufarr)) {
                foreach ($bufarr[1] as $index=>$nr) {
                    $dev = new CpuDevice();
                    $dev->setModel('CPU'.$nr);
                    $dev->setCpuSpeed($bufarr[2][$index]);
                    if (PSI_LOAD_BAR) {
                        $dev->setLoad($this->_parseProcStat('cpu'.$nr));
                    }
                    $this->sys->setCpus($dev);
                }
//                $this->_cpu_loads['cpu'] = $buf[1];
//                if (preg_match("/CPU1 speed/", $sysinfo)) {
//                    $this->_cpu_loads['cpu0'] = $buf[1];
//                }
            }
            break;
        case 'GNU':
        case 'Linux':
            if (CommonFunctions::executeProgram('cat', '/proc/cpuinfo', $resulte, false) && ($resulte !== "")) {
                parent::_cpuinfo($resulte);
            }
        }
    }

    /**
     * Machine
     *
     * @return void
     */
    protected function _machine()
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (($sysstat = $this->getSystemStatus()) !== '') {
                $machine= '';
                if (preg_match("/^Version: (\S+) v/", $sysstat, $buf)) {
                    $machine = $buf[1];
                }
                if (preg_match("/\nSystem Part-Number: (\S+)\n/", $sysstat, $buf)) {
                    $machine .= ' '.$buf[1];
                }
                if (preg_match("/\nBIOS version: (\S+)\n/", $sysstat, $buf)) {
                    if (trim($machine) !== '') {
                        $machine .= ', BIOS '.$buf[1];
                    } else {
                        $machine = 'BIOS '.$buf[1];
                    }
                }
                $machine = trim($machine);

                if ($machine !== '') {
                    $this->sys->setMachine($machine);
                }
            }
            break;
        case 'DrayOS':
            if (($sysstat = $this->getSysVerSysteminfo()) !== '') {
                $machine= '';
                if (preg_match("/[\r\n]Router Model: (\S+) /", $sysstat, $buf)) {
                    $machine = $buf[1];
                }
                if (preg_match("/[\r\n]Revision: (.+)[\r\n]/", $sysstat, $buf)) {
                    $machine .= ' '.$buf[1];
                }
                $machine = trim($machine);

                if ($machine !== '') {
                    $this->sys->setMachine($machine);
                }
            }
            break;

        case 'GNU':
        case 'Linux':
            parent::_machine();
        }
    }

    /**
     * Hostname
     *
     * @return void
     */
    protected function _hostname()
    {
        switch ($this->_ostype) {
        case 'FortiOS':
//            $hostname = PSI_EMU_HOSTNAME;
            if (preg_match("/\nHostname: ([^\n]+)\n/", $this->getSystemStatus(), $buf)) {
                $this->sys->setHostname(trim($buf[1]));
//                $hostname = trim($buf[1]);
            }

//            $ip = gethostbyname($hostname);
//            if ($ip != $hostname) {
//                $this->sys->setHostname(gethostbyaddr($ip));
//            } else {
//                $this->sys->setHostname($hostname);
//            }
            break;
        case 'DrayOS':
            if (preg_match("/[\r\n]Router Name: ([^\n\r]+)[\r\n]/", $this->getSysVerSysteminfo(), $buf)) {
                $this->sys->setHostname(trim($buf[1]));
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_hostname();
        }

    }

    /**
     * filesystem information
     *
     * @return void
     */
    protected function _filesystems()
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (CommonFunctions::executeProgram('fnsysctl', 'df -k', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $resulti = substr($resulte, $resulto[1][1]);
                $df = preg_split("/\n/", $resulti, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($df as $df_line) {
                    $df_buf1 = preg_split("/(\%\s)/", $df_line, 3);
                    if (count($df_buf1) != 2) {
                        continue;
                    }
                    if (preg_match("/(.*)(\s+)(([0-9]+)(\s+)([0-9]+)(\s+)([\-0-9]+)(\s+)([0-9]+)$)/", $df_buf1[0], $df_buf2)) {
                        $df_buf = array($df_buf2[1], $df_buf2[4], $df_buf2[6], $df_buf2[8], $df_buf2[10], $df_buf1[1]);
                        if (count($df_buf) == 6) {
                            $df_buf[5] = trim($df_buf[5]);
                            $dev = new DiskDevice();
                            $dev->setName(trim($df_buf[0]));
                            if ($df_buf[2] < 0) {
                                $dev->setTotal($df_buf[3] * 1024);
                                $dev->setUsed($df_buf[3] * 1024);
                            } else {
                                $dev->setTotal($df_buf[1] * 1024);
                                $dev->setUsed($df_buf[2] * 1024);
                                if ($df_buf[3]>0) {
                                    $dev->setFree($df_buf[3] * 1024);
                                }
                            }
                            if (PSI_SHOW_MOUNT_POINT) $dev->setMountPoint($df_buf[5]);
                            $dev->setFsType('unknown');
                            $this->sys->setDiskDevices($dev);
                        }
                    }
                }
            }
            break;
        case 'DrayOS':
            if (CommonFunctions::executeProgram('nand' ,'usage', $resulte, false, PSI_EXEC_TIMEOUT_INT, '>') && ($resulte !== "")
               && preg_match('/([\s\S]+> nand usage)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $df = substr($resulte, strlen($resulto[1][0]));

                if (preg_match('/Usecfg/', $df)) { // fix for Vigor2135ac v4.4.2
                    $df = preg_replace("/(cfg|bin)/", "\n$1", substr($resulte, strlen($resulto[1][0])));
                    $percent = '';
                } else {
                    $percent = '%';
                }

                $df = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($df as $df_line) {
                    if (preg_match("/^(\S+)[ ]+(\d+)[ ]+(\d+)[ ]+(\d+)[ ]+(\d+)".$percent."/", trim($df_line), $df_buf)) {
                        $dev = new DiskDevice();
                        $dev->setName($df_buf[1]);
                        $dev->setTotal($df_buf[2]);
                        $dev->setUsed($df_buf[3]);
                        $dev->setFree($df_buf[4]);
                        $dev->setFsType('NAND');
                        $this->sys->setDiskDevices($dev);
                    }
                }
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_filesystems();
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function _distro()
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (preg_match("/^Version: \S+ (v[^\n]+)\n/", $this->getSystemStatus(), $buf)) {
                $this->sys->setDistribution('FortiOS '.trim($buf[1]));
            }
            $this->sys->setDistributionIcon('FortiOS.png');
            break;
        case 'DrayOS':
            if (preg_match("/ Version: ([^\n]+)\n/", $this->getSysVerSysteminfo(), $buf)) {
                $this->sys->setDistribution('DrayOS '.trim($buf[1]));
            }
            $this->sys->setDistributionIcon('DrayOS.png');
            break;
        case 'GNU':
        case 'Linux':
            parent::_distro();
        }
//        if ($this->_ostype !== null) {
//            $this->sys->setDistributionIcon($this->_ostype);
//        }
    }

    /**
     * fill the load for a individual cpu, through parsing /proc/stat for the specified cpu
     *
     * @param String $cpuline cpu for which load should be meassured
     *
     * @return int
     */
    protected function _parseProcStat($cpuline)
    {
        if ($this->_cpu_loads === null) {
            $this->_cpu_loads = array();
            switch ($this->_ostype) {
            case 'FortiOS':
                if (($strBuf = $this->getSystemPerformance()) !== '') {
                    $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) if (preg_match('/^CPU(\d*) states: \d+% user \d+% system \d+% nice (\d+)% idle /', $line, $buf)) {
                        $this->_cpu_loads['cpu'.$buf[1]] = 100-$buf[2];
                    }
                }
                break;
            case 'DrayOS':
                if (preg_match("/CPU usage :[ ]*(\d+) %/", $sysinfo = $this->getSysVerSysteminfo(), $buf)) {
                    $this->_cpu_loads['cpu'] = $buf[1];
                    if (preg_match("/CPU1 speed/", $sysinfo) && !preg_match("/CPU2 speed/", $sysinfo)) { //only one cpu
                        $this->_cpu_loads['cpu1'] = $buf[1];
                    }
                }
            }
        }
        if (isset($this->_cpu_loads[$cpuline])) {
            return $this->_cpu_loads[$cpuline];
        } else {
            return null;
        }
     }

    /**
     * Processor Load
     * optionally create a loadbar
     *
     * @return void
     */
    protected function _loadavg($bufr = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (CommonFunctions::executeProgram('fnsysctl', 'cat /proc/loadavg', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_loadavg(substr($resulte, strlen($resulto[1][0])));
            }
            break;
        case 'DrayOS':
            if (PSI_LOAD_BAR) {
                $this->sys->setLoadPercent($this->_parseProcStat('cpu'));
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_loadavg();
        }
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    protected function _uptime($bufu = null)
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (preg_match("/\nUptime: ([^\n]+)\n/", $this->getSystemPerformance(), $buf)) {
                parent::_uptime('up '.trim($buf[1]));
            }
            break;
        case 'DrayOS':
            if (preg_match("/System Uptime:([\d:]+)/", $this->getShowStatus(), $buf)) {
                parent::_uptime('up '.trim($buf[1]));
            }
            break;
        case 'GNU':
        case 'Linux':
            if (CommonFunctions::executeProgram('cat', '/proc/uptime', $resulte, false) && ($resulte !== "")) {
                $ar_buf = preg_split('/ /', $resulte);
                $this->sys->setUptime(trim($ar_buf[0]));
            } else {
                parent::_uptime();
            }
        }
    }

    /**
     * Kernel Version
     *
     * @return void
     */
    protected function _kernel()
    {
        switch ($this->_ostype) {
        case 'FortiOS':
            if (CommonFunctions::executeProgram('fnsysctl', 'cat /proc/version', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $strBuf = substr($resulte, $resulto[1][1]);
                if (preg_match('/version\s+(\S+)/', $strBuf, $ar_buf)) {
                    $verBuf = $ar_buf[1];
                    if (preg_match('/ SMP /', $strBuf)) {
                        $verBuf .= ' (SMP)';
                    }
                    $this->sys->setKernel($verBuf);
                }
            }
            break;
        case 'GNU':
        case 'Linux':
            parent::_kernel();
        }
    }

    /**
     * get the information
     *
     * @return void
     */
    public function build()
    {
        $this->error->addWarning("The SSH version of phpSysInfo is a work in progress, some things currently don't work");
        switch ($this->_ostype) {
        case 'FortiOS':
            if (!$this->blockname || $this->blockname==='vitals') {
                $this->_distro();
                $this->_hostname();
                $this->_kernel();
                $this->_uptime();
//                $this->_users();
                $this->_loadavg();
//                $this->_processes();
            }
            if (!$this->blockname || $this->blockname==='hardware') {
                $this->_machine();
                $this->_cpuinfo();
                //$this->_virtualizer();
//                $this->_pci();
                $this->_usb();
//                $this->_i2c();
            }
            if (!$this->blockname || $this->blockname==='memory') {
                $this->_memory();
            }
            if (!$this->blockname || $this->blockname==='filesystem') {
                $this->_filesystems();
            }
            if (!$this->blockname || $this->blockname==='network') {
                $this->_network();
            }
            break;
        case 'DrayOS':
            if (!$this->blockname || $this->blockname==='vitals') {
                $this->_distro();
                $this->_hostname();
//                $this->_kernel();
                $this->_uptime();
////                $this->_users();
                $this->_loadavg();
////                $this->_processes();
            }
            if (!$this->blockname || $this->blockname==='hardware') {
                $this->_machine();
                $this->_cpuinfo();
//                //$this->_virtualizer();
////                $this->_pci();
//                $this->_usb();
////                $this->_i2c();
            }
            if (!$this->blockname || $this->blockname==='memory') {
                $this->_memory();
            }
            if (!$this->blockname || $this->blockname==='filesystem') {
                $this->_filesystems();
            }
            if (!$this->blockname || $this->blockname==='network') {
                $this->_network();
            }
            break;

        case 'GNU':
        case 'Linux':
            parent::build();
        }
    }
}
