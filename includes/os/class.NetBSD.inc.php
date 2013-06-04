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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class NetBSD extends BSDCommon
{
    /**
     * define the regexp for log parser
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCPURegExp1("^cpu(.*)\, (.*) MHz");
        $this->setCPURegExp2("/user = (.*), nice = (.*), sys = (.*), intr = (.*), idle = (.*)/");
        $this->setSCSIRegExp1("^(.*) at scsibus.*: <(.*)> .*");
        $this->setSCSIRegExp2("^(da[0-9]): (.*)MB ");
        $this->setPCIRegExp1("/(.*) at pci[0-9] dev [0-9]* function [0-9]*: (.*)$/");
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
            if (! empty($ar_buf_b[0]) && ! empty($ar_buf_n[3])) {
                $dev = new NetDevice();
                $dev->setName($ar_buf_b[0]);
                $dev->setTxBytes($ar_buf_b[4]);
                $dev->setRxBytes($ar_buf_b[3]);
                $dev->setDrops($ar_buf_n[8]);
                $dev->setErrors($ar_buf_n[4] + $ar_buf_n[6]);
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
            if (preg_match('/^(.*) at (pciide|wdc|atabus|atapibus)[0-9] (.*): <(.*)>/', $line, $ar_buf)) {
                $dev = new HWDevice();
                $dev->setName($ar_buf[1]);
                // now loop again and find the capacity
                foreach ($this->readdmesg() as $line2) {
                    if (preg_match("/^(".$ar_buf[1]."): (.*), (.*), (.*)MB, .*$/", $line2, $ar_buf_n)) {
                        $dev->setCapacity($ar_buf_n[4] * 2048 * 1.049);
                    } elseif (preg_match("/^(".$ar_buf[1]."): (.*) MB, (.*), (.*), .*$/", $line2, $ar_buf_n)) {
                        $dev->setCapacity($ar_buf_n[2] * 2048);
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
     * get the information
     *
     * @see BSDCommon::build()
     *
     * @return Void
     */
    public function build()
    {
        parent::build();
        $this->_distroicon();
        $this->_network();
        $this->_uptime();
    }
}
