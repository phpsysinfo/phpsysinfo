<?php
/**
 * BSDCommon Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI BSDCommon OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.BSDCommon.inc.php 621 2012-07-29 18:49:04Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * BSDCommon class
 * get all the required information for BSD Like systems
 * no need to implement in every class the same methods
 *
 * @category  PHP
 * @package   PSI BSDCommon OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class BSDCommon extends OS
{
    /**
     * Assoc array of all CPUs loads.
     */
    private $_cpu_loads = null;

    /**
     * content of the syslog
     *
     * @var array
     */
    private $_dmesg = null;

    /**
     * regexp1 for cpu information out of the syslog
     *
     * @var string
     */
    private $_CPURegExp1 = "//";

    /**
     * regexp2 for cpu information out of the syslog
     *
     * @var string
     */
    private $_CPURegExp2 = "//";

    /**
     * regexp1 for scsi information out of the syslog
     *
     * @var string
     */
    private $_SCSIRegExp1 = "//";

    /**
     * regexp2 for scsi information out of the syslog
     *
     * @var string
     */
    private $_SCSIRegExp2 = "//";

    /**
     * regexp3 for scsi information out of the syslog
     *
     * @var string
     */
    private $_SCSIRegExp3 = "//";

    /**
     * regexp1 for pci information out of the syslog
     *
     * @var string
     */
    private $_PCIRegExp1 = "//";

    /**
     * regexp1 for pci information out of the syslog
     *
     * @var string
     */
    private $_PCIRegExp2 = "//";

    /**
     * setter for cpuregexp1
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setCPURegExp1($value)
    {
        $this->_CPURegExp1 = $value;
    }

    /**
     * setter for cpuregexp2
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setCPURegExp2($value)
    {
        $this->_CPURegExp2 = $value;
    }

    /**
     * setter for scsiregexp1
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setSCSIRegExp1($value)
    {
        $this->_SCSIRegExp1 = $value;
    }

    /**
     * setter for scsiregexp2
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setSCSIRegExp2($value)
    {
        $this->_SCSIRegExp2 = $value;
    }

    /**
     * setter for scsiregexp3
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setSCSIRegExp3($value)
    {
        $this->_SCSIRegExp3 = $value;
    }

    /**
     * setter for pciregexp1
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setPCIRegExp1($value)
    {
        $this->_PCIRegExp1 = $value;
    }

    /**
     * setter for pciregexp2
     *
     * @param string $value value to set
     *
     * @return void
     */
    protected function setPCIRegExp2($value)
    {
        $this->_PCIRegExp2 = $value;
    }

    /**
     * read /var/run/dmesg.boot, but only if we haven't already
     *
     * @return array
     */
    protected function readdmesg()
    {
        if ($this->_dmesg === null) {
            if ((PSI_OS != 'Darwin') && (CommonFunctions::rfts('/var/run/dmesg.boot', $buf, 0, 4096, false) || CommonFunctions::rfts('/var/log/dmesg.boot', $buf, 0, 4096, false) || CommonFunctions::rfts('/var/run/dmesg.boot', $buf))) {  // Once again but with debug
                $parts = preg_split("/rebooting|Uptime/", $buf, -1, PREG_SPLIT_NO_EMPTY);
                $this->_dmesg = preg_split("/\n/", $parts[count($parts) - 1], -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $this->_dmesg = array();
            }
        }

        return $this->_dmesg;
    }

    /**
     * get a value from sysctl command
     *
     * @param string $key key for the value to get
     *
     * @return string
     */
    protected function grabkey($key)
    {
        $buf = "";
        if (CommonFunctions::executeProgram('sysctl', "-n $key", $buf, PSI_DEBUG)) {
            return $buf;
        } else {
            return '';
        }
    }

    /**
     * Virtual Host Name
     *
     * @return void
     */
    protected function hostname()
    {
        if (PSI_USE_VHOST) {
            if (CommonFunctions::readenv('SERVER_NAME', $hnm)) $this->sys->setHostname($hnm);
        } else {
            if (CommonFunctions::executeProgram('hostname', '', $buf, PSI_DEBUG)) {
                $this->sys->setHostname($buf);
            }
        }
    }

    /**
     * Kernel Version
     *
     * @return void
     */
    protected function kernel()
    {
        $s = $this->grabkey('kern.version');
        $a = preg_split('/:/', $s);
        if (isset($a[2])) {
            $this->sys->setKernel($a[0].$a[1].':'.$a[2]);
        } else {
            $this->sys->setKernel($s);
        }
    }

    /**
     * Virtualizer info
     *
     * @return void
     */
    private function virtualizer()
    {
        if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
            $testvirt = $this->sys->getVirtualizer();
            $novm = true;
            foreach ($testvirt as $virtkey=>$virtvalue) if ($virtvalue) {
                $novm = false;
                break;
            }
            // Detect QEMU cpu
            if ($novm && isset($testvirt["cpuid:QEMU"])) {
                $this->sys->setVirtualizer('qemu'); // QEMU
                $novm = false;
            }

            if ($novm && isset($testvirt["hypervisor"])) {
                $this->sys->setVirtualizer('unknown');
            }
        }
    }

    /**
     * CPU usage
     *
     * @return void
     */
    protected function cpuusage()
    {
        if (($this->_cpu_loads === null)) {
            $this->_cpu_loads = array();
            if (PSI_OS != 'Darwin') {
                if ($fd = $this->grabkey('kern.cp_time')) {
                    // Find out the CPU load
                    // user + sys = load
                    // total = total
                    if (preg_match($this->_CPURegExp2, $fd, $res) && (sizeof($res) > 4)) {
                        $load = $res[2] + $res[3] + $res[4]; // cpu.user + cpu.sys
                        $total = $res[2] + $res[3] + $res[4] + $res[5]; // cpu.total
                        // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
                        sleep(1);
                        $fd = $this->grabkey('kern.cp_time');
                        if (preg_match($this->_CPURegExp2, $fd, $res) && (sizeof($res) > 4)) {
                            $load2 = $res[2] + $res[3] + $res[4];
                            $total2 = $res[2] + $res[3] + $res[4] + $res[5];
                            if ($total2 != $total) {
                                $this->_cpu_loads['cpu'] = (100 * ($load2 - $load)) / ($total2 - $total);
                            } else {
                                $this->_cpu_loads['cpu'] = 0;
                            }
                        }
                    }
                }
            } else {
                $ncpu = $this->grabkey('hw.ncpu');
                if (($ncpu !== "") && ($ncpu >= 1) && CommonFunctions::executeProgram('ps', "-A -o %cpu", $pstable, false) && !empty($pstable)) {
                    $pslines = preg_split("/\n/", $pstable, -1, PREG_SPLIT_NO_EMPTY);
                    if (!empty($pslines) && (count($pslines)>1) && (trim($pslines[0])==="%CPU")) {
                        array_shift($pslines);
                        $sum = 0;
                        foreach ($pslines as $psline) {
                            $sum+=trim($psline);
                        }
                        $this->_cpu_loads['cpu'] = min($sum/$ncpu, 100);
                    }
                }
            }
        }

        if (isset($this->_cpu_loads['cpu'])) {
            return $this->_cpu_loads['cpu'];
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
    protected function loadavg()
    {
        $s = $this->grabkey('vm.loadavg');
        $s = preg_replace('/{ /', '', $s);
        $s = preg_replace('/ }/', '', $s);
        $this->sys->setLoad($s);

        if (PSI_LOAD_BAR) {
            $this->sys->setLoadPercent($this->cpuusage());
        }
    }

    /**
     * CPU information
     *
     * @return void
     */
    protected function cpuinfo()
    {
        $dev = new CpuDevice();
        $cpumodel = $this->grabkey('hw.model');
        $dev->setModel($cpumodel);
        if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO && preg_match('/^QEMU Virtual CPU version /', $cpumodel)) {
            $this->sys->setVirtualizer("cpuid:QEMU", false);
        }

        $notwas = true;
        foreach ($this->readdmesg() as $line) {
            if ($notwas) {
               $regexps = preg_split("/\n/", $this->_CPURegExp1, -1, PREG_SPLIT_NO_EMPTY); // multiple regexp separated by \n
               foreach ($regexps as $regexp) {
                   if (preg_match($regexp, $line, $ar_buf) && (sizeof($ar_buf) > 2)) {
                        if ($dev->getCpuSpeed() == 0) {
                            $dev->setCpuSpeed(round($ar_buf[2]));
                        }
                        $notwas = false;
                        break;
                    }
                }
            } else {
                if (preg_match("/^\s+Origin| Features/", $line, $ar_buf)) {
                    if (preg_match("/^\s+Origin[ ]*=[ ]*\"(.+)\"/", $line, $ar_buf)) {
                        $dev->setVendorId($ar_buf[1]);
                    } elseif (preg_match("/ Features2[ ]*=.*<(.+)>/", $line, $ar_buf)) {
                        $feats = preg_split("/,/", strtolower(trim($ar_buf[1])), -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($feats as $feat) {
                            if (($feat=="vmx") || ($feat=="svm")) {
                                $dev->setVirt($feat);
                            } elseif ($feat=="hv") {
                                if ($dev->getVirt() === null) {
                                    $dev->setVirt('hypervisor');
                                }
                                if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
                                    $this->sys->setVirtualizer("hypervisor", false);
                                }
                            }
                        }
                    }
                } else break;
            }
        }

        $ncpu = $this->grabkey('hw.ncpu');
        if (($ncpu === "") || !($ncpu >= 1)) {
            $ncpu = 1;
        }
        if (($ncpu == 1) && PSI_LOAD_BAR) {
            $dev->setLoad($this->cpuusage());
        }
        for ($ncpu ; $ncpu > 0 ; $ncpu--) {
            $this->sys->setCpus($dev);
        }
    }

    /**
     * Machine information
     *
     * @return void
     */
    private function machine()
    {
        if ((PSI_OS == 'NetBSD') || (PSI_OS == 'OpenBSD')) {
            $buffer = array();
            if (PSI_OS == 'NetBSD') { // NetBSD
                $buffer['Manufacturer'] = $this->grabkey('machdep.dmi.system-vendor');
                $buffer['Model'] = $this->grabkey('machdep.dmi.system-product');
                $buffer['Product'] = $this->grabkey('machdep.dmi.board-product');
                $buffer['SMBIOSBIOSVersion'] = $this->grabkey('machdep.dmi.bios-version');
                $buffer['ReleaseDate'] = $this->grabkey('machdep.dmi.bios-date');
            } else { // OpenBSD
                $buffer['Manufacturer'] = $this->grabkey('hw.vendor');
                $buffer['Model'] = $this->grabkey('hw.product');
                $buffer['Product'] = "";
                $buffer['SMBIOSBIOSVersion'] = "";
                $buffer['ReleaseDate'] = "";
            }
            if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
                $vendor_array = array();
                $vendor_array[] = $buffer['Model'];
                $vendor_array[] = trim($buffer['Manufacturer']." ".$buffer['Model']);
                if (PSI_OS == 'NetBSD') { // NetBSD
                    $vendor_array[] = $this->grabkey('machdep.dmi.board-vendor');
                    $vendor_array[] = $this->grabkey('machdep.dmi.bios-vendor');
                }
                $virt = CommonFunctions::decodevirtualizer($vendor_array);
                if ($virt !== null) {
                    $this->sys->setVirtualizer($virt);
                }
            }

            $buf = "";
            if (($buffer['Manufacturer'] !== "") && !preg_match("/^To be filled by O\.E\.M\.$|^System manufacturer$|^Not Specified$/i", $buf2=trim($buffer['Manufacturer'])) && ($buf2 !== "")) {
                $buf .= ' '.$buf2;
            }

            if (($buffer['Model'] !== "") && !preg_match("/^To be filled by O\.E\.M\.$|^System Product Name$|^Not Specified$/i", $buf2=trim($buffer['Model'])) && ($buf2 !== "")) {
                $model = $buf2;
                $buf .= ' '.$buf2;
            }
            if (($buffer['Product'] !== "") && !preg_match("/^To be filled by O\.E\.M\.$|^BaseBoard Product Name$|^Not Specified$|^Default string$/i", $buf2=trim($buffer['Product'])) && ($buf2 !== "")) {
                if ($buf2 !== $model) {
                    $buf .= '/'.$buf2;
                } elseif (isset($buffer['SystemFamily']) && !preg_match("/^To be filled by O\.E\.M\.$|^System Family$|^Not Specified$/i", $buf2=trim($buffer['SystemFamily'])) && ($buf2 !== "")) {
                    $buf .= '/'.$buf2;
                }
            }

            $bver = "";
            $brel = "";
            if (($buf2=trim($buffer['SMBIOSBIOSVersion'])) !== "") {
                $bver .= ' '.$buf2;
            }
            if ($buffer['ReleaseDate'] !== "") {
                if (preg_match("/^(\d{4})(\d{2})(\d{2})$/", $buffer['ReleaseDate'], $dateout)) {
                    $brel .= ' '.$dateout[2].'/'.$dateout[3].'/'.$dateout[1];
                } elseif (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $buffer['ReleaseDate'])) {
                    $brel .= ' '.$buffer['ReleaseDate'];
                }
            }
            if ((trim($bver) !== "") || (trim($brel) !== "")) {
                $buf .= ', BIOS'.$bver.$brel;
            }

            if (trim($buf) !== "") {
                $this->sys->setMachine(trim($buf));
            }
        } elseif ((PSI_OS == 'FreeBSD') && defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
            $vendorid = $this->grabkey('hw.hv_vendor');
            if (trim($vendorid) === "") {
                foreach ($this->readdmesg() as $line) if (preg_match("/^Hypervisor: Origin = \"(.+)\"/", $line, $ar_buf)) {
                    if (trim($ar_buf[1]) !== "") {
                        $vendorid = $ar_buf[1];
                    }
                    break;
                }
            }
            if (trim($vendorid) !== "") {
                $virt = CommonFunctions::decodevirtualizer($vendorid);
                if ($virt !== null) {
                    $this->sys->setVirtualizer($virt);
                } else {
                    $this->sys->setVirtualizer('unknown');
                }
            }
        }
    }

    /**
     * SCSI devices
     * get the scsi device information out of dmesg
     *
     * @return void
     */
    protected function scsi()
    {
        foreach ($this->readdmesg() as $line) {
            if (preg_match($this->_SCSIRegExp1, $line, $ar_buf) && (sizeof($ar_buf) > 2)) {
                $dev = new HWDevice();
                $dev->setName($ar_buf[1].": ".trim($ar_buf[2]));
                $this->sys->setScsiDevices($dev);
            } elseif (preg_match($this->_SCSIRegExp2, $line, $ar_buf) && (sizeof($ar_buf) > 1)) {
                /* duplication security */
                $notwas = true;
                foreach ($this->sys->getScsiDevices() as $finddev) {
                    if ($notwas && (substr($finddev->getName(), 0, strpos($finddev->getName(), ': ')) == $ar_buf[1])) {
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                            if (isset($ar_buf[3]) && ($ar_buf[3]==="G")) {
                                $finddev->setCapacity($ar_buf[2] * 1024 * 1024 * 1024);
                            } elseif (isset($ar_buf[2])) {
                                $finddev->setCapacity($ar_buf[2] * 1024 * 1024);
                            }
                        }
                        $notwas = false;
                        break;
                    }
                }
                if ($notwas) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1]);
                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                        if (isset($ar_buf[3]) && ($ar_buf[3]==="G")) {
                                $dev->setCapacity($ar_buf[2] * 1024 * 1024 * 1024);
                            } elseif (isset($ar_buf[2])) {
                                $dev->setCapacity($ar_buf[2] * 1024 * 1024);
                            }
                    }
                    $this->sys->setScsiDevices($dev);
                }
            } elseif (preg_match($this->_SCSIRegExp3, $line, $ar_buf) && (sizeof($ar_buf) > 1)) {
                /* duplication security */
                $notwas = true;
                foreach ($this->sys->getScsiDevices() as $finddev) {
                    if ($notwas && (substr($finddev->getName(), 0, strpos($finddev->getName(), ': ')) == $ar_buf[1])) {
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                           && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                            if (isset($ar_buf[2])) $finddev->setSerial(trim($ar_buf[2]));
                        }
                        $notwas = false;
                        break;
                    }
                }
                if ($notwas) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1]);
                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                       && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                        if (isset($ar_buf[2])) $dev->setSerial(trim($ar_buf[2]));
                    }
                    $this->sys->setScsiDevices($dev);
                }
            }
        }
        /* cleaning */
        foreach ($this->sys->getScsiDevices() as $finddev) {
            if (strpos($finddev->getName(), ': ') !== false)
                $finddev->setName(substr(strstr($finddev->getName(), ': '), 2));
        }
    }

    /**
     * parsing the output of pciconf command
     *
     * @return Array
     */
    protected function pciconf()
    {
        $arrResults = array();
        $intS = 0;
        if (CommonFunctions::executeProgram("pciconf", "-lv", $strBuf, PSI_DEBUG)) {
            $arrTemp = array();
            $arrBlocks = preg_split("/\n\S/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($arrBlocks as $strBlock) {
                $arrLines = preg_split("/\n/", $strBlock, -1, PREG_SPLIT_NO_EMPTY);
                $vend = null;
                foreach ($arrLines as $strLine) {
                    if (preg_match("/\sclass=0x([a-fA-F0-9]{4})[a-fA-F0-9]{2}\s.*\schip=0x([a-fA-F0-9]{4})([a-fA-F0-9]{4})\s/", $strLine, $arrParts)) {
                        $arrTemp[$intS] = 'Class '.$arrParts[1].': Device '.$arrParts[3].':'.$arrParts[2];
                        $vend = '';
                    } elseif (preg_match("/(.*) = '(.*)'/", $strLine, $arrParts)) {
                        if (trim($arrParts[1]) == "vendor") {
                            $vend = trim($arrParts[2]);
                        } elseif (trim($arrParts[1]) == "device") {
                            if (($vend !== null) && ($vend !== '')) {
                                $arrTemp[$intS] = $vend." - ".trim($arrParts[2]);
                            } else {
                                $arrTemp[$intS] = trim($arrParts[2]);
                                $vend = '';
                            }
                        }
                    }
                }
                if ($vend !== null) {
                    $intS++;
                }
            }
            foreach ($arrTemp as $name) {
                $dev = new HWDevice();
                $dev->setName($name);
                $arrResults[] = $dev;
            }
        }

        return $arrResults;
    }

    /**
     * PCI devices
     * get the pci device information out of dmesg
     *
     * @return void
     */
    protected function pci()
    {
        if ((!$results = Parser::lspci(false)) && (!$results = $this->pciconf())) {
            foreach ($this->readdmesg() as $line) {
                if (preg_match($this->_PCIRegExp1, $line, $ar_buf) && (sizeof($ar_buf) > 2)) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1].": ".$ar_buf[2]);
                    $results[] = $dev;
                } elseif (preg_match($this->_PCIRegExp2, $line, $ar_buf) && (sizeof($ar_buf) > 2)) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1].": ".$ar_buf[2]);
                    $results[] = $dev;
                }
            }
        }
        foreach ($results as $dev) {
            $this->sys->setPciDevices($dev);
        }
    }

    /**
     * IDE devices
     * get the ide device information out of dmesg
     *
     * @return void
     */
    protected function ide()
    {
        foreach ($this->readdmesg() as $line) {
            if (preg_match('/^(ad[0-9]+): (.*)MB <(.*)> (.*) (.*)/', $line, $ar_buf)) {
                $dev = new HWDevice();
                $dev->setName($ar_buf[1].": ".trim($ar_buf[3]));
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    $dev->setCapacity($ar_buf[2] * 1024 * 1024);
                }
                $this->sys->setIdeDevices($dev);
            } elseif (preg_match('/^(acd[0-9]+): (.*) <(.*)> (.*)/', $line, $ar_buf)) {
                $dev = new HWDevice();
                $dev->setName($ar_buf[1].": ".trim($ar_buf[3]));
                $this->sys->setIdeDevices($dev);
            } elseif (preg_match('/^(ada[0-9]+): <(.*)> (.*)/', $line, $ar_buf)) {
                $dev = new HWDevice();
                $dev->setName($ar_buf[1].": ".trim($ar_buf[2]));
                $this->sys->setIdeDevices($dev);
            } elseif (preg_match('/^(ada[0-9]+): (.*)MB \((.*)\)/', $line, $ar_buf)) {
                /* duplication security */
                $notwas = true;
                foreach ($this->sys->getIdeDevices() as $finddev) {
                    if ($notwas && (substr($finddev->getName(), 0, strpos($finddev->getName(), ': ')) == $ar_buf[1])) {
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                            $finddev->setCapacity($ar_buf[2] * 1024 * 1024);
                        }
                        $notwas = false;
                        break;
                    }
                }
                if ($notwas) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1]);
                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                        $dev->setCapacity($ar_buf[2] * 1024 * 1024);
                    }
                    $this->sys->setIdeDevices($dev);
                }
            } elseif (preg_match('/^(ada[0-9]+): Serial Number (.*)/', $line, $ar_buf)) {
                /* duplication security */
                $notwas = true;
                foreach ($this->sys->getIdeDevices() as $finddev) {
                    if ($notwas && (substr($finddev->getName(), 0, strpos($finddev->getName(), ': ')) == $ar_buf[1])) {
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                           && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                            $finddev->setSerial(trim($ar_buf[2]));
                        }
                        $notwas = false;
                        break;
                    }
                }
                if ($notwas) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[1]);
                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                       && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                        $finddev->setSerial(trim($ar_buf[2]));
                    }
                    $this->sys->setIdeDevices($dev);
                }
            }
        }
        /* cleaning */
        foreach ($this->sys->getIdeDevices() as $finddev) {
                    if (strpos($finddev->getName(), ': ') !== false)
                        $finddev->setName(substr(strstr($finddev->getName(), ': '), 2));
        }
    }

    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    protected function memory()
    {
        if (PSI_OS == 'FreeBSD' || PSI_OS == 'OpenBSD') {
            // vmstat on fbsd 4.4 or greater outputs kbytes not hw.pagesize
            // I should probably add some version checking here, but for now
            // we only support fbsd 4.4
            $pagesize = 1024;
        } else {
            $pagesize = $this->grabkey('hw.pagesize');
        }
        if (CommonFunctions::executeProgram('vmstat', '', $vmstat, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $vmstat, -1, PREG_SPLIT_NO_EMPTY);
            $ar_buf = preg_split("/\s+/", trim($lines[2]), 19);
            if (PSI_OS == 'NetBSD' || PSI_OS == 'DragonFly') {
                $this->sys->setMemFree($ar_buf[4] * 1024);
            } else {
                $this->sys->setMemFree($ar_buf[4] * $pagesize);
            }
            $this->sys->setMemTotal($this->grabkey('hw.physmem'));
            $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());

            if (((PSI_OS == 'OpenBSD' || PSI_OS == 'NetBSD') && CommonFunctions::executeProgram('swapctl', '-l -k', $swapstat, PSI_DEBUG)) || CommonFunctions::executeProgram('swapinfo', '-k', $swapstat, PSI_DEBUG)) {
                $lines = preg_split("/\n/", $swapstat, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($lines as $line) {
                    $ar_buf = preg_split("/\s+/", $line, 6);
                    if (($ar_buf[0] != 'Total') && ($ar_buf[0] != 'Device')) {
                        $dev = new DiskDevice();
                        $dev->setMountPoint($ar_buf[0]);
                        $dev->setName("SWAP");
                        $dev->setFsType('swap');
                        $dev->setTotal($ar_buf[1] * 1024);
                        $dev->setUsed($ar_buf[2] * 1024);
                        $dev->setFree($dev->getTotal() - $dev->getUsed());
                        $this->sys->setSwapDevices($dev);
                    }
                }
            }
        }
    }

    /**
     * USB devices
     * get the ide device information out of dmesg
     *
     * @return void
     */
    protected function usb()
    {
        $notwas = true;
        if ((PSI_OS == 'FreeBSD') && CommonFunctions::executeProgram('usbconfig', '', $bufr, false)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                if (preg_match('/^(ugen[0-9]+\.[0-9]+): <([^,]*)(.*)> at (usbus[0-9]+)/', $line, $ar_buf)) {
                    $notwas = false;
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[2]);
                    $this->sys->setUSBDevices($dev);
                }
            }
        }
        if ($notwas) foreach ($this->readdmesg() as $line) {
//            if (preg_match('/^(ugen[0-9\.]+): <(.*)> (.*) (.*)/', $line, $ar_buf)) {
//                    $dev->setName($ar_buf[1].": ".$ar_buf[2]);
            if (preg_match('/^(u[a-z]+[0-9]+): <([^,]*)(.*)> on (usbus[0-9]+)/', $line, $ar_buf)) {
                    $dev = new HWDevice();
                    $dev->setName($ar_buf[2]);
                    $this->sys->setUSBDevices($dev);
            }
        }
    }

    /**
     * filesystem information
     *
     * @return void
     */
    protected function filesystems()
    {
        $arrResult = Parser::df();
        foreach ($arrResult as $dev) {
            $this->sys->setDiskDevices($dev);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function distro()
    {
        if (CommonFunctions::executeProgram('uname', '-s', $result, PSI_DEBUG)) {
            $this->sys->setDistribution($result);
        }
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    private function uptime()
    {
        if ($kb = $this->grabkey('kern.boottime')) {
            if (preg_match("/sec = ([0-9]+)/", $kb, $buf)) { // format like: { sec = 1096732600, usec = 885425 } Sat Oct 2 10:56:40 2004
                $this->sys->setUptime(time() - $buf[1]);
            } else {
                date_default_timezone_set('UTC');
                $kbt = strtotime($kb);
                if (($kbt !== false) && ($kbt != -1)) {
                    $this->sys->setUptime(time() - $kbt); // format like: Sat Oct 2 10:56:40 2004
                } else {
                    $this->sys->setUptime(time() - $kb); // format like: 1096732600
                }
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_OS::build()
     *
     * @return void
     */
    public function build()
    {
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->distro();
            $this->hostname();
            $this->kernel();
            $this->_users();
            $this->loadavg();
            $this->uptime();
        }
        if (!$this->blockname || $this->blockname==='hardware') {
            $this->machine();
            $this->cpuinfo();
            $this->virtualizer();
            $this->pci();
            $this->ide();
            $this->scsi();
            $this->usb();
        }
        if (!$this->blockname || $this->blockname==='memory') {
            $this->memory();
        }
        if (!$this->blockname || $this->blockname==='filesystem') {
            $this->filesystems();
        }
    }
}
