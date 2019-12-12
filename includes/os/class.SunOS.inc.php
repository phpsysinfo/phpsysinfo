<?php
/**
 * SunOS System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI SunOS OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.SunOS.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * SunOS sysinfo class
 * get all the required information from SunOS systems
 *
 * @category  PHP
 * @package   PSI SunOS OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SunOS extends OS
{

    /**
     * content of prtconf -v
     *
     * @var array
     */
    private $_prtconf = null;

    /**
     * Execute prtconf -v and save ass array
     *
     * @return array
     */
    protected function prtconf()
    {
        if ($this->_prtconf === null) {
            $this->_prtconf = array();
            if (CommonFunctions::executeProgram('prtconf', '-v', $buf, PSI_DEBUG) && ($buf!="")) {
                $blocks = preg_split( '/\n(?=    \S)/', $buf, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($blocks) && (count($blocks)>2)) {
                    array_shift($blocks);
                    foreach ($blocks as $block) {
                        if (preg_match('/^    (\S+) /',$block, $ar_buf)) {
                            $group = trim($ar_buf[1], ',');
                            $grouparr = array();
                            $blocks1 = preg_split( '/\n(?=        \S)/', $block, -1, PREG_SPLIT_NO_EMPTY);
                            if (!empty($blocks1) && count($blocks1)) {
                                array_shift($blocks1);
                                foreach ($blocks1 as $block1) {
                                    if (!preg_match('/^        name=\'([^\']+)\'/',$block1)
                                       && preg_match('/^        (\S+) /',$block1, $ar_buf)) {
                                        $device = trim($ar_buf[1], ',');
                                        $devicearr = array();
                                        $blocks2 = preg_split( '/\n(?=            \S)/', $block1, -1, PREG_SPLIT_NO_EMPTY);
                                        if (!empty($blocks2) && count($blocks2)) {
                                            array_shift($blocks2);
                                            foreach ($blocks2 as $block2) {
                                                if (!preg_match('/^            name=\'([^\']+)\'/',$block2)
                                                   && preg_match('/^            (\S+) /',$block2, $ar_buf)) {
                                                    $subdev = trim($ar_buf[1], ',');
                                                    $subdevarr = array();
                                                    $blocks3 = preg_split( '/\n(?=                \S)/', $block2, -1, PREG_SPLIT_NO_EMPTY);
                                                    if (!empty($blocks3) && count($blocks3)) {
                                                        array_shift($blocks3);
                                                        foreach ($blocks3 as $block3) {
                                                            if (preg_match('/^                name=\'([^\']+)\' [\s\S]+ value=\'?([^\']+)\'?/m',$block3, $ar_buf)) {
                                                                if ($subdev==='Hardware') {
                                                                    $subdevarr[$ar_buf[1]] = $ar_buf[2];
                                                                    $subdevarr['device'] = $device;
                                                                }
                                                            }
                                                        }
                                                        if (count($subdevarr)) {
                                                            $devicearr = $subdevarr;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        if (count($devicearr)) {
                                            $grouparr[$device][] = $devicearr;
                                        }
                                    }
                                }
                            }
                            if (count($grouparr)) {
                                $this->_prtconf[$group][] = $grouparr;
                            }
                        }
                    }
                }
            }
        }

        return $this->_prtconf;
    }

    /**
     * Extract kernel values via kstat() interface
     *
     * @param string $key key for kstat programm
     *
     * @return string
     */
    private function _kstat($key)
    {
        if (CommonFunctions::executeProgram('kstat', '-p d '.$key, $m, PSI_DEBUG) && ($m!=="")) {
            list($key, $value) = preg_split("/\t/", $m, 2);

            return trim($value);
        } else {
            return '';
        }
    }

    /**
     * Virtual Host Name
     *
     * @return void
     */
    private function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            if (CommonFunctions::readenv('SERVER_NAME', $hnm)) $this->sys->setHostname($hnm);
        } else {
            if (CommonFunctions::executeProgram('uname', '-n', $result, PSI_DEBUG)) {
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    $this->sys->setHostname(gethostbyaddr($ip));
                }
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
        if (CommonFunctions::executeProgram('uname', '-s', $os, PSI_DEBUG) && ($os!="")) {
            if (CommonFunctions::executeProgram('uname', '-r', $version, PSI_DEBUG) && ($version!="")) {
                $os.=' '.$version;
            }
            if (CommonFunctions::executeProgram('uname', '-v', $subversion, PSI_DEBUG) && ($subversion!="")) {
                $os.=' ('.$subversion.')';
            }
            if (CommonFunctions::executeProgram('uname', '-i', $platform, PSI_DEBUG) && ($platform!="")) {
                $os.=' '.$platform;
            }
            $this->sys->setKernel($os);
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
        $this->sys->setUptime(time() - $this->_kstat('unix:0:system_misc:boot_time'));
    }

    /**
     * Processor Load
     * optionally create a loadbar
     *
     * @return void
     */
    private function _loadavg()
    {
        $load1 = $this->_kstat('unix:0:system_misc:avenrun_1min');
        $load5 = $this->_kstat('unix:0:system_misc:avenrun_5min');
        $load15 = $this->_kstat('unix:0:system_misc:avenrun_15min');
        $this->sys->setLoad(round($load1 / 256, 2).' '.round($load5 / 256, 2).' '.round($load15 / 256, 2));
    }

    /**
     * CPU information
     *
     * @return void
     */
    private function _cpuinfo()
    {
        if (CommonFunctions::executeProgram('kstat', '-p d cpu_info:*:cpu_info*:core_id', $m, PSI_DEBUG) && ($m!=="")) {
            $cpuc = count(preg_split('/\n/', $m, -1, PREG_SPLIT_NO_EMPTY));
            for ($cpu=0; $cpu < $cpuc; $cpu++) {
                $dev = new CpuDevice();
                if (($buf = $this->_kstat('cpu_info:'.$cpu.':cpu_info'.$cpu.':current_clock_Hz')) !== "") {
                    $dev->setCpuSpeed($buf/1000000);
                } elseif (($buf = $this->_kstat('cpu_info:'.$cpu.':cpu_info'.$cpu.':clock_MHz')) !== "") {
                    $dev->setCpuSpeed($buf);
                }
                if (($buf = $this->_kstat('cpu_info:'.$cpu.':cpu_info'.$cpu.':supported_frequencies_Hz')) !== "") {
                    $cpuarr = preg_split('/:/', $buf, -1, PREG_SPLIT_NO_EMPTY);
                    if (($cpuarrc=count($cpuarr))>1) {
                        $dev->setCpuSpeedMin($cpuarr[0]/1000000);
                        $dev->setCpuSpeedMax($cpuarr[$cpuarrc-1]/1000000);
                    }
                }
                if (($buf  =$this->_kstat('cpu_info:'.$cpu.':cpu_info'.$cpu.':brand')) !== "") {
                    $dev->setModel($buf);
                } elseif (($buf  =$this->_kstat('cpu_info:'.$cpu.':cpu_info'.$cpu.':cpu_type')) !== "") {
                    $dev->setModel($buf);
                } elseif (CommonFunctions::executeProgram('uname', '-p', $buf, PSI_DEBUG) && ($buf!="")) {
                    $dev->setModel($buf);
                } elseif (CommonFunctions::executeProgram('uname', '-i', $buf, PSI_DEBUG) && ($buf!="")) {
                    $dev->setModel($buf);
                }
                $this->sys->setCpus($dev);
            }
         }
    }

    /**
     * PCI devices
     *
     * @return void
     */
    protected function _pci()
    {
        $prtconf = $this->prtconf();
        if ((count($prtconf)>1) && isset($prtconf['pci'])) {
            foreach ($prtconf['pci'] as $prt) {
                foreach ($prt as $pci) {
                    foreach ($pci as $pcidev) {
                        if (isset($pcidev['device'])) {
                            $dev = new HWDevice();
                            if (isset($pcidev['model'])) {
                                $name = $pcidev['model'];
                            } else {
                                $name = $pcidev['device'];
                            }
                            if (isset($pcidev['device-name'])) {
                                $name .= ': '.$pcidev['device-name'];
                            }
                            $dev->setName($name);

                            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                                if (isset($pcidev['subsystem-name']) && ($pcidev['subsystem-name']!=='unknown subsystem')) {
                                    $dev->setProduct($pcidev['subsystem-name']);
                                }
                                if (isset($pcidev['vendor-name'])) {
                                    $dev->setManufacturer($pcidev['vendor-name']);
                                }
                            }

                            $this->sys->setPciDevices($dev);
                        }
                    }
                }
            }
        }
    }

    /**
     * Network devices
     *
     * @return void
     */
    private function _network()
    {
        if (CommonFunctions::executeProgram('netstat', '-ni | awk \'(NF ==10){print;}\'', $netstat, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $netstat, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $ar_buf = preg_split("/\s+/", $line);
                if (!empty($ar_buf[0]) && $ar_buf[0] !== 'Name') {
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[0]);
                    $results[$ar_buf[0]]['errs'] = $ar_buf[5] + $ar_buf[7];
                    if (preg_match('/^(\D+)(\d+)$/', $ar_buf[0], $intf)) {
                        $prefix = $intf[1].':'.$intf[2].':'.$intf[1].$intf[2].':';
                    } elseif (preg_match('/^(\D.*)(\d+)$/', $ar_buf[0], $intf)) {
                        $prefix = $intf[1].':'.$intf[2].':mac:';
                    } else {
                        $prefix = "";
                    }
                    if ($prefix !== "") {
                        $cnt = $this->_kstat($prefix.'drop');
                        if ($cnt > 0) {
                            $dev->setDrops($cnt);
                        }
                        $cnt = $this->_kstat($prefix.'obytes64');
                        if ($cnt > 0) {
                            $dev->setTxBytes($cnt);
                        }
                        $cnt = $this->_kstat($prefix.'rbytes64');
                        if ($cnt > 0) {
                            $dev->setRxBytes($cnt);
                        }
                    }
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                        if (CommonFunctions::executeProgram('ifconfig', $ar_buf[0], $bufr2, PSI_DEBUG) && ($bufr2!=="")) {
                            $bufe2 = preg_split("/\n/", $bufr2, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($bufe2 as $buf2) {
                                if (preg_match('/^\s+ether\s+(\S+)/i', $buf2, $ar_buf2)) {
                                    if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').preg_replace('/:/', '-', strtoupper($ar_buf2[1])));
                                } elseif (preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $buf2, $ar_buf2)) {
                                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                }
                            }
                        }
                        if (CommonFunctions::executeProgram('ifconfig', $ar_buf[0].' inet6', $bufr2, PSI_DEBUG) && ($bufr2!=="")) {
                            $bufe2 = preg_split("/\n/", $bufr2, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($bufe2 as $buf2) {
                                if (preg_match('/^\s+inet6\s+([^\s\/]+)/i', $buf2, $ar_buf2)
                                   && ($ar_buf2[1]!="::") && !preg_match('/^fe80::/i', $ar_buf2[1]))
                                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ar_buf2[1]));
                            }
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
        $pagesize = $this->_kstat('unix:0:seg_cache:slab_size');
        $this->sys->setMemTotal($this->_kstat('unix:0:system_pages:pagestotal') * $pagesize);
        $this->sys->setMemUsed($this->_kstat('unix:0:system_pages:pageslocked') * $pagesize);
        $this->sys->setMemFree($this->_kstat('unix:0:system_pages:pagesfree') * $pagesize);
        $dev = new DiskDevice();
        $dev->setName('SWAP');
        $dev->setFsType('swap');
        $dev->setMountPoint('SWAP');
        $dev->setTotal($this->_kstat('unix:0:vminfo:swap_avail') / 1024);
        $dev->setUsed($this->_kstat('unix:0:vminfo:swap_alloc') / 1024);
        $dev->setFree($this->_kstat('unix:0:vminfo:swap_free') / 1024);
        $this->sys->setSwapDevices($dev);
    }

    /**
     * filesystem information
     *
     * @return void
     */
    private function _filesystems()
    {
        if (CommonFunctions::executeProgram('df', '-k', $df, PSI_DEBUG)) {
            $df = preg_replace('/\n\s/m', ' ', $df);
            $mounts = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($mounts as $mount) {
                $ar_buf = preg_split('/\s+/', $mount, 6);
                if (!empty($ar_buf[0]) && $ar_buf[0] !== 'Filesystem') {
                    $dev = new DiskDevice();
                    $dev->setName($ar_buf[0]);
                    $dev->setTotal($ar_buf[1] * 1024);
                    $dev->setUsed($ar_buf[2] * 1024);
                    $dev->setFree($ar_buf[3] * 1024);
                    $dev->setMountPoint($ar_buf[5]);
                    if (CommonFunctions::executeProgram('df', '-n', $dftypes, PSI_DEBUG)) {
                        $mounttypes = preg_split("/\n/", $dftypes, -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($mounttypes as $type) {
                            $ty_buf = preg_split('/:/', $type, 2);
                            if (trim($ty_buf[0]) == $dev->getMountPoint()) {
                                $dev->setFsType($ty_buf[1]);
                                break;
                            }
                        }
                    } elseif (CommonFunctions::executeProgram('df', '-T', $dftypes, PSI_DEBUG)) {
                        $dftypes = preg_replace('/\n\s/m', ' ', $dftypes);
                        $mounttypes = preg_split("/\n/", $dftypes, -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($mounttypes as $type) {
                            $ty_buf = preg_split("/\s+/", $type, 3);
                            if ($ty_buf[0] == $dev->getName()) {
                                $dev->setFsType($ty_buf[1]);
                                break;
                            }
                        }
                    }
                    $this->sys->setDiskDevices($dev);
                }
            }
        }
    }

    /**
     * Distribution Icon
     *
     * @return void
     */
    private function _distro()
    {
        if (CommonFunctions::rfts('/etc/release', $buf, 1, 4096, false) && (trim($buf)!="")) {
            $this->sys->setDistribution(trim($buf));
            $list = @parse_ini_file(PSI_APP_ROOT."/data/distros.ini", true);
            if ($list && preg_match('/^(\S+)\s*/', preg_replace('/^Open\s+/', 'Open', preg_replace('/^Oracle\s+/', 'Oracle', trim($buf))), $id_buf) && isset($list[$distid=(trim($id_buf[1].' SunOS'))]['Image'])) {
                $this->sys->setDistributionIcon($list[$distid]['Image']);
                if (isset($list[trim($distid)]['Name'])) {
                    $this->sys->setDistribution(trim($list[$distid]['Name']).' '.$this->sys->getDistribution());
                }
            } else {
                $this->sys->setDistributionIcon('SunOS.png');
            }
        } else {
            $this->sys->setDistribution('SunOS');
            $this->sys->setDistributionIcon('SunOS.png');
        }
    }

    /**
     * Processes
     *
     * @return void
     */
    protected function _processes()
    {
        if (CommonFunctions::executeProgram('ps', 'aux', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $processes['*'] = 0;
            foreach ($lines as $line) {
                if (preg_match("/^\S+\s+\d+\s+\S+\s+\S+\s+\d+\s+\d+\s+\S+\s+(\w)/", $line, $ar_buf)) {
                    $processes['*']++;
                    $state = $ar_buf[1];
                    if ($state == 'O') $state = 'R'; //linux format
                    elseif ($state == 'W') $state = 'D';
                    elseif ($state == 'D') $state = 'd'; //invalid
                    if (isset($processes[$state])) {
                        $processes[$state]++;
                    } else {
                        $processes[$state] = 1;
                    }
                }
            }
            if ($processes['*'] > 0) {
                $this->sys->setProcesses($processes);
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
        $this->error->addError("WARN", "The SunOS version of phpSysInfo is a work in progress, some things currently don't work");
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_distro();
            $this->_hostname();
            $this->_kernel();
            $this->_uptime();
            $this->_users();
            $this->_loadavg();
            $this->_processes();
        }
        if (!$this->blockname || $this->blockname==='hardware') {
            $this->_cpuinfo();
            $this->_pci();
        }
        if (!$this->blockname || $this->blockname==='network') {
            $this->_network();
        }
        if (!$this->blockname || $this->blockname==='memory') {
            $this->_memory();
        }
        if (!$this->blockname || $this->blockname==='filesystem') {
            $this->_filesystems();
        }
    }
}
