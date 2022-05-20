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
class SSH extends Linux
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
        if (CommonFunctions::executeProgram('uname' ,'', $result, false) && ($result !== "")) {
            switch (strtolower($result)) {
            case 'linux':
                $this->_ostype = 'Linux';
                break;
            }
        }
        if (($this->_ostype === null) && ($this->getSystemStatus() !== '')) {
            $this->_ostype = 'FortiOS';
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
        if ($this->_ostype === 'FortiOS') {
            return 'UTF-8';
        }
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

    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    protected function _memory($mbuf = null, $sbuf = null)
    {
        switch ($this->_ostype) {
        case 'Fortios':
            if (CommonFunctions::executeProgram('get', 'hardware memory', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_memory(substr($resulte, strlen($resulto[1][0])));
            }
            break;
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
        case 'Fortios':
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
        case 'Fortios':
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
        case 'Fortios':
            if (CommonFunctions::executeProgram('get', 'hardware cpu', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_cpuinfo(substr($resulte, strlen($resulto[1][0])));
            }
            break;
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
        case 'Fortios':
            if (($sysstat = $this->getSystemStatus()) !== '') {
                $machine= '';
                if (preg_match("/^Version: (\S+) v/", $sysstat, $buf)) {
                    $machine = $buf[1];
                }
                if (preg_match("/\nSystem Part-Number: (\S+)\n/", $sysstat, $buf)) {
                    $machine .= ' '.$buf[1];
                }
                if (preg_match("/\nBIOS version: (\S+)\n/", $sysstat, $buf)) {
                    $machine .= ' BIOS '.$buf[1];
                }
                $machine = trim($machine);

                if ($machine !== '') {
                    $this->sys->setMachine($machine);
                }
            }
            break;
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
        case 'Fortios':
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
        case 'Fortios':
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
        case 'Fortios':
            if (preg_match("/^Version: \S+ (v[^\n]+)\n/", $this->getSystemStatus(), $buf)) {
                $this->sys->setDistribution('FortiOS '.trim($buf[1]));
            }
            $this->sys->setDistributionIcon($this->_ostype);
            break;
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
        if ($this->_ostype === 'FortiOS') {
            if ($this->_cpu_loads === null) {
                $this->_cpu_loads = array();
                if (($strBuf = $this->getSystemPerformance()) !== '') {
                    $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($lines as $line) if (preg_match('/^CPU(\d*) states: \d+% user \d+% system \d+% nice (\d+)% idle /', $line, $buf)) {
                        $this->_cpu_loads['cpu'.$buf[1]] = 100-$buf[2];
                    }
                }
            }

            if (isset($this->_cpu_loads[$cpuline])) {
                return $this->_cpu_loads[$cpuline];
            } else {
                return null;
            }
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
        case 'Fortios':
            if (CommonFunctions::executeProgram('fnsysctl', 'cat /proc/loadavg', $resulte, false) && ($resulte !== "")
               && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_loadavg(substr($resulte, strlen($resulto[1][0])));
            }
            break;
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
        case 'Fortios':
            if (preg_match("/\nUptime: ([^\n]+)\n/", $this->getSystemPerformance(), $buf)) {
                parent::_uptime('up '.trim($buf[1]));
            }
            break;
        case 'Linux':
            parent::_uptime();
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
        case 'Fortios':
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
        case 'Fortios':
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
        case 'Linux':
            parent::build();
        }
    }
}
