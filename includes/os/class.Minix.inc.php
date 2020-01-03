<?php
/**
 * Minix System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Minix OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.Minix.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Minix sysinfo class
 * get all the required information from Minix system
 *
 * @category  PHP
 * @package   PSI Minix OS class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Minix extends OS
{
    /**
     * content of the syslog
     *
     * @var array
     */
    private $_dmesg = null;

    /**
     * read /var/log/messages, but only if we haven't already
     *
     * @return array
     */
    protected function readdmesg()
    {
        if ($this->_dmesg === null) {
            if (CommonFunctions::rfts('/var/log/messages', $buf)) {
                    $blocks = preg_replace("/\s(kernel: MINIX \d+\.\d+\.\d+\.)/", '<BLOCK>$1', $buf);
                    $parts = preg_split("/<BLOCK>/", $blocks, -1, PREG_SPLIT_NO_EMPTY);
                    $this->_dmesg = preg_split("/\n/", $parts[count($parts) - 1], -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $this->_dmesg = array();
            }
        }

        return $this->_dmesg;
    }

    /**
     * get the cpu information
     *
     * @return void
     */
    protected function _cpuinfo()
    {
        if (CommonFunctions::rfts('/proc/cpuinfo', $bufr, 0, 4096, false)) {
            $processors = preg_split('/\s?\n\s?\n/', trim($bufr));
            foreach ($processors as $processor) {
                $_n = ""; $_f = ""; $_m = ""; $_s = "";
                $dev = new CpuDevice();
                $details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($details as $detail) {
                    $arrBuff = preg_split('/\s+:\s+/', trim($detail));
                    if (count($arrBuff) == 2) {
                        switch (strtolower($arrBuff[0])) {
                        case 'model name':
                            $_n = $arrBuff[1];
                            break;
                        case 'cpu mhz':
                            $dev->setCpuSpeed($arrBuff[1]);
                            break;
                        case 'cpu family':
                            $_f = $arrBuff[1];
                            break;
                        case 'model':
                            $_m = $arrBuff[1];
                            break;
                        case 'stepping':
                            $_s = $arrBuff[1];
                            break;
                        case 'flags':
                            if (preg_match("/ vmx/", $arrBuff[1])) {
                                $dev->setVirt("vmx");
                            } elseif (preg_match("/ svm/", $arrBuff[1])) {
                                $dev->setVirt("svm");
                            }
                            break;
                        case 'vendor_id':
                            $dev->setVendorId($arrBuff[1]);
                            break;
                        }
                    }
                }
                if ($_n == "") $_n="CPU";
                if ($_f != "") $_n.=" Family ".$_f;
                if ($_m != "") $_n.=" Model ".$_m;
                if ($_s != "") $_n.=" Stepping ".$_s;
                $dev->SetModel($_n);
                $this->sys->setCpus($dev);
            }
        } else
        foreach ($this->readdmesg() as $line) {
            if (preg_match('/kernel: (CPU .*) freq (.*) MHz/', $line, $ar_buf)) {
                $dev = new CpuDevice();
                $dev->setModel($ar_buf[1]);
                $dev->setCpuSpeed($ar_buf[2]);
                $this->sys->setCpus($dev);
            }
        }
    }

    /**
     * PCI devices
     * get the pci device information out of dmesg
     *
     * @return void
     */
    protected function _pci()
    {
        if (CommonFunctions::rfts('/proc/pci', $strBuf, 0, 4096, false)) {
            $arrLines = preg_split("/\n/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
            $arrResults = array();
            foreach ($arrLines as $strLine) {
               $arrParams = preg_split('/\s+/', trim($strLine), 4);
               if (count($arrParams) == 4)
                  $strName = $arrParams[3];
               else
                  $strName = "unknown";
               $strName = preg_replace('/\(.*\)/', '', $strName);
               $dev = new HWDevice();
               $dev->setName($strName);
               $arrResults[] = $dev;
            }
            foreach ($arrResults as $dev) {
                $this->sys->setPciDevices($dev);
            }
        }
        if (!(isset($arrResults) && is_array($arrResults)) && ($results = Parser::lspci())) {
            /* if access error: chmod 4755 /usr/bin/lspci */
            foreach ($results as $dev) {
                $this->sys->setPciDevices($dev);
            }
        }
    }

    /**
     * Minix Version
     *
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::executeProgram('uname', '-rvm', $ret)) {
            foreach ($this->readdmesg() as $line) {
                if (preg_match('/kernel: MINIX (\d+\.\d+\.\d+)\. \((.+)\)/', $line, $ar_buf)) {
                    $branch = $ar_buf[2];
                    break;
                }
            }
            if (isset($branch))
               $this->sys->setKernel($ret.' ('.$branch.')');
            else
               $this->sys->setKernel($ret);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function _distro()
    {
        if (CommonFunctions::executeProgram('uname', '-sr', $ret))
            $this->sys->setDistribution($ret);
        else
            $this->sys->setDistribution('Minix');

        $this->sys->setDistributionIcon('Minix.png');
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
            if (preg_match("/up (\d+) day[s]?,\s*(\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[3];
                $hours = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60);
            } elseif (preg_match("/up (\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[2];
                $hours = $ar_buf[1];
                $this->sys->setUptime($hours * 3600 + $min * 60);
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
        if (CommonFunctions::executeProgram('uptime', '', $buf)) {
            if (preg_match("/load averages: (.*), (.*), (.*)$/", $buf, $ar_buf)) {
                $this->sys->setLoad($ar_buf[1].' '.$ar_buf[2].' '.$ar_buf[3]);
            }
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
     *  Physical memory information and Swap Space information
     *
     *  @return void
     */
    private function _memory()
    {
        if (CommonFunctions::rfts('/proc/meminfo', $bufr, 1, 4096, false)) {
            $ar_buf = preg_split('/\s+/', trim($bufr));
            if (count($ar_buf) >= 5) {
                    $this->sys->setMemTotal($ar_buf[0]*$ar_buf[1]);
                    $this->sys->setMemFree($ar_buf[0]*$ar_buf[2]);
                    $this->sys->setMemCache($ar_buf[0]*$ar_buf[4]);
                    $this->sys->setMemUsed($ar_buf[0]*($ar_buf[1]-$ar_buf[2]));
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
     * network information
     *
     * @return void
     */
    private function _network()
    {
        if (CommonFunctions::executeProgram('ifconfig', '-a', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                if (preg_match("/^([^\s:]+):\saddress\s(\S+)\snetmask/", $line, $ar_buf)) {
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[1]);
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            $dev->setInfo($ar_buf[2]);
                    }
                    $this->sys->setNetDevices($dev);
                }
            }
        }
    }

    /**
     * Processes
     *
     * @return void
     */
    protected function _processes()
    {
        if (CommonFunctions::executeProgram('ps', 'alx', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $processes['*'] = 0;
            foreach ($lines as $line) {
                if (preg_match("/^\s(\w)\s/", $line, $ar_buf)) {
                    $processes['*']++;
                    $state = $ar_buf[1];
                    if ($state == 'W') $state = 'D'; //linux format
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
     * @return Void
     */
    public function build()
    {
        $this->error->addError("WARN", "The Minix version of phpSysInfo is a work in progress, some things currently don't work");
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
            $this->_pci();
            $this->_cpuinfo();
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
