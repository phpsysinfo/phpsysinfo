<?php
/**
 * NetBSD System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI NetBSD OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.NetBSD.inc.php 287 2009-06-26 12:11:59Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * NetBSD sysinfo class
 * get all the required information from NetBSD systems
 *
 * @category  PHP
 * @package   PSI NetBSD OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class NetBSD extends BSDCommon
{
    /**
     * define the regexp for log parser
     */
    public function __construct($blockname = false)
    {
        parent::__construct($blockname);
        $this->setCPURegExp1("/^cpu(.*)\, (.*) MHz/");
        $this->setCPURegExp2("/user = (.*), nice = (.*), sys = (.*), intr = (.*), idle = (.*)/");
        $this->setSCSIRegExp1("/^(.*) at scsibus.*: <(.*)> .*/");
        $this->setSCSIRegExp2("/^(sd[0-9]+): (.*)([MG])B,/");
        $this->setPCIRegExp1("/(.*) at pci[0-9]+ dev [0-9]* function [0-9]*: (.*)$/");
        $this->setPCIRegExp2("/\"(.*)\" (.*).* at [.0-9]+ irq/");
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    private function _uptime()
    {
        $a = $this->grabkey('kern.boottime');
        $this->sys->setUptime(time() - $a);
    }

    /**
     * get network information
     *
     * @return void
     */
    private function _network()
    {
        CommonFunctions::executeProgram('netstat', '-nbdi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link"', $netstat_b);
        CommonFunctions::executeProgram('netstat', '-ndi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link"', $netstat_n);
        $lines_b = preg_split("/\n/", $netstat_b, -1, PREG_SPLIT_NO_EMPTY);
        $lines_n = preg_split("/\n/", $netstat_n, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0, $max = sizeof($lines_b); $i < $max; $i++) {
            $ar_buf_b = preg_split("/\s+/", $lines_b[$i]);
            $ar_buf_n = preg_split("/\s+/", $lines_n[$i]);
            if (!empty($ar_buf_b[0]) && (!empty($ar_buf_n[3]) || ($ar_buf_n[3] === "0"))) {
                $dev = new NetDevice();
                $dev->setName($ar_buf_b[0]);
                $dev->setTxBytes($ar_buf_b[4]);
                $dev->setRxBytes($ar_buf_b[3]);
                $dev->setDrops($ar_buf_n[8]);
                $dev->setErrors($ar_buf_n[4] + $ar_buf_n[6]);
                if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS) && (CommonFunctions::executeProgram('ifconfig', $ar_buf_b[0].' 2>/dev/null', $bufr2, PSI_DEBUG))) {
                    $speedinfo = "";
                    $bufe2 = preg_split("/\n/", $bufr2, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($bufe2 as $buf2) {
                        if (preg_match('/^\s+address:\s+(\S+)/i', $buf2, $ar_buf2)) {
                            if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').preg_replace('/:/', '-', strtoupper($ar_buf2[1])));
                        } elseif (preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $buf2, $ar_buf2)) {
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                        } elseif ((preg_match('/^\s+inet6\s+([^\s%]+)\s+prefixlen/i', $buf2, $ar_buf2)
                              || preg_match('/^\s+inet6\s+([^\s%]+)%\S+\s+prefixlen/i', $buf2, $ar_buf2))
                              && ($ar_buf2[1]!="::") && !preg_match('/^fe80::/i', $ar_buf2[1])) {
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ar_buf2[1]));
                        } elseif (preg_match('/^\s+media:\s+/i', $buf2) && preg_match('/[\(\s](\d+)(G*)base/i', $buf2, $ar_buf2)) {
                            if (isset($ar_buf2[2]) && strtoupper($ar_buf2[2])=="G") {
                                $unit = "G";
                            } else {
                                $unit = "M";
                            }
                            if (preg_match('/\s(\S+)-duplex/i', $buf2, $ar_buf3))
                                $speedinfo = $ar_buf2[1].$unit.'b/s '.strtolower($ar_buf3[1]);
                            else
                                $speedinfo = $ar_buf2[1].$unit.'b/s';
                        }
                    }
                    if ($speedinfo != "") $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speedinfo);
                }
                $this->sys->setNetDevices($dev);
            }
        }
    }

    /**
     * IDE information
     *
     * @return void
     */
    protected function ide()
    {
        foreach ($this->readdmesg() as $line) {
            if (preg_match('/^(.*) at (pciide|wdc|atabus|atapibus)[0-9]+ (.*): <(.*)>/', $line, $ar_buf)
               || preg_match('/^(.*) at (pciide|wdc|atabus|atapibus)[0-9]+ /', $line, $ar_buf)) {
                $dev = new HWDevice();
                if (isset($ar_buf[4])) {
                    $dev->setName($ar_buf[4]);
                } else {
                    $dev->setName($ar_buf[1]);
                    // now loop again and find the name
                    foreach ($this->readdmesg() as $line2) {
                        if (preg_match("/^(".$ar_buf[1]."): <(.*)>$/", $line2, $ar_buf_n)) {
                            $dev->setName($ar_buf_n[2]);
                            break;
                        }
                    }
                }
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    // now loop again and find the capacity
                    foreach ($this->readdmesg() as $line2) {
                        if (preg_match("/^(".$ar_buf[1]."): (.*), (.*), (.*)MB, .*$/", $line2, $ar_buf_n)) {
                            $dev->setCapacity($ar_buf_n[4] * 1024 * 1024);
                            break;
                        } elseif (preg_match("/^(".$ar_buf[1]."): (.*) MB, (.*), (.*), .*$/", $line2, $ar_buf_n)) {
                            $dev->setCapacity($ar_buf_n[2] * 1024 * 1024);
                            break;
                        } elseif (preg_match("/^(".$ar_buf[1]."): (.*) GB, (.*), (.*), .*$/", $line2, $ar_buf_n)) {
                            $dev->setCapacity($ar_buf_n[2] * 1024 * 1024 * 1024);
                            break;
                        }
                    }
                }
                $this->sys->setIdeDevices($dev);
            }
        }
    }

    /**
     * get icon name
     *
     * @return void
     */
    private function _distroicon()
    {
        $this->sys->setDistributionIcon('NetBSD.png');
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
                    elseif ($state == 'I') $state = 'S';
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
     * @see BSDCommon::build()
     *
     * @return Void
     */
    public function build()
    {
        parent::build();
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_distroicon();
            $this->_uptime();
            $this->_processes();
        }
        if (!$this->blockname || $this->blockname==='network') {
            $this->_network();
        }
    }
}
