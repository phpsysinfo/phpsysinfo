<?php
/**
 * Fort System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Forti OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.Forti.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Forti sysinfo class
 * get all the required information from Forti system
 *
 * @category  PHP
 * @package   PSI Forti OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2022 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Forti extends Linux
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
     * get os specific encoding
     *
     * @see PSI_Interface_OS::getEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
        return 'UTF-8';
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
        return null;
    }


    private function getSystemStatus()
    {
        if ($this->_sysstatus === null) {
            if (CommonFunctions::executeProgram('echo', 'get system status | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
                if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                    $this->_sysstatus = substr($resulte, $resulto[1][1]);
                } else {
                    $this->_sysstatus = $resulte;
                }
            } else {
                $this->_sysstatus =  '';
            }
        }

        return $this->_sysstatus;
    }

    private function getSystemPerformance()
    {
        if ($this->_sysperformance === null) {
            if (CommonFunctions::executeProgram('echo', 'get system performance status | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
                if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                    $this->_sysperformance = substr($resulte, $resulto[1][1]);
                } else {
                    $this->_sysperformance = $resulte;
                }
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
    protected function _memory($mbuf = null)
    {
        if (CommonFunctions::executeProgram('echo', 'get hardware memory | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
            if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_memory(substr($resulte, $resulto[1][1]));
            } else {
                parent::_memory($resulte);
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
        $netdevs = array();
        if (CommonFunctions::executeProgram('echo', 'diagnose ip address list | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
            if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $strBuf = substr($resulte, $resulto[1][1]);
            } else {
                $strBuf = $resulte;
            }
            $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) if (preg_match('/^IP=(\S+)->.+ devname=(\S+)$/', $line, $buf)) {
                if (!isset($netdevs[$buf[2]])) {
                    $netdevs[$buf[2]] = $buf[1];
                } else {
                    $netdevs[$buf[2]] .= ';'.$buf[1];
                }
            }
        }
        if (CommonFunctions::executeProgram('echo', 'diagnose ipv6 address list | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
            if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                $strBuf = substr($resulte, $resulto[1][1]);
            } else {
                $strBuf = $resulte;
            }
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
                if ($netname === 'root') {
                    $dev->setName('lo');
                } else {
                    $dev->setName($netname);
                }
                $this->sys->setNetDevices($dev);
                $dev->setInfo($netinfo);
            }
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
        if (CommonFunctions::executeProgram('echo', 'get hardware cpu | sshpass -p \''.PSI_EMU_PASSWORD.'\' ssh -T -o \'StrictHostKeyChecking=no\' '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT, $resulte, false) && ($resulte !== "")) {
            if (preg_match('/[[\$#]#] (.+)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
                parent::_cpuinfo(substr($resulte, $resulto[1][1]));
            } else {
                parent::_cpuinfo($resulte);
            }
        }
    }

    /**
     * Machine
     *
     * @return void
     */
    private function _machine()
    {
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
    }

    /**
     * Hostname
     *
     * @return void
     */
    protected function _hostname()
    {
        $hostname = PSI_EMU_HOSTNAME;

        if (preg_match("/\nHostname: ([^\n]+)\n/", $this->getSystemStatus(), $buf)) {
            $hostname = trim($buf[1]);
        }

        $ip = gethostbyname($hostname);
        if ($ip != $hostname) {
            $this->sys->setHostname(gethostbyaddr($ip));
        } else {
            $this->sys->setHostname($hostname);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function _distro()
    {
        if (preg_match("/^Version: \S+ (v[^\n]+)\n/", $this->getSystemStatus(), $buf)) {
            $this->sys->setDistribution('FortiOS '.trim($buf[1]));
        }
        $this->sys->setDistributionIcon('FortiOS.png');
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

    /**
     * Processor Load
     * optionally create a loadbar
     *
     * @return void
     */
    protected function _loadavg()
    {
        if (PSI_LOAD_BAR) {
            $this->sys->setLoadPercent($this->_parseProcStat('cpu'));
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
        if (preg_match("/\nUptime: ([^\n]+)\n/", $this->getSystemPerformance(), $buf)) {
            parent::_uptime('up '.trim($buf[1]));
        }
    }

    /**
     * get the information
     *
     * @return void
     */
    public function build()
    {
        $this->error->addWarning("The Forti OS version of phpSysInfo is a work in progress, some things currently don't work");
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_distro();
            $this->_hostname();
//            $this->_kernel();
            $this->_uptime();
//            $this->_users();
//            $this->_parseProcStat2();
            $this->_loadavg();
//            $this->_processes();
        }
        if (!$this->blockname || $this->blockname==='hardware') {
            $this->_machine();
            $this->_cpuinfo();
            //$this->_virtualizer();
//            $this->_pci();
//            $this->_usb();
//            $this->_i2c();
        }
        if (!$this->blockname || $this->blockname==='memory') {
            $this->_memory();
        }
        if (!$this->blockname || $this->blockname==='filesystem') {
//            $this->_filesystems();
        }
        if (!$this->blockname || $this->blockname==='network') {
            $this->_network();
        }
    }
}
