<?php
/**
 * Linux System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Linux OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.Linux.inc.php 712 2012-12-05 14:09:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Linux sysinfo class
 * get all the required information from Linux system
 *
 * @category  PHP
 * @package   PSI Linux OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Linux extends OS
{
    /**
     * Assoc array of all CPUs loads.
     */
    protected $_cpu_loads;

    /**
     * Machine
     *
     * @return void
     */
    private function _machine()
    {
        $machine = "";
        if ((CommonFunctions::rfts('/var/log/dmesg', $result, 0, 4096, false)
              && preg_match('/^[\s\[\]\.\d]*DMI:\s*(.*)/m', $result, $ar_buf))
           ||(CommonFunctions::executeProgram('dmesg', '', $result, false)
              && preg_match('/^[\s\[\]\.\d]*DMI:\s*(.*)/m', $result, $ar_buf))) {
            $machine = trim($ar_buf[1]);
        } else { //data from /sys/devices/virtual/dmi/id/
            $product = "";
            $board = "";
            $bios = "";
            if (CommonFunctions::rfts('/sys/devices/virtual/dmi/id/board_vendor', $buf, 1, 4096, false) && (trim($buf)!="")) {
                $machine = trim($buf);
            }
            if (CommonFunctions::rfts('/sys/devices/virtual/dmi/id/product_name', $buf, 1, 4096, false) && (trim($buf)!="")) {
                $product = trim($buf);
            }
            if (CommonFunctions::rfts('/sys/devices/virtual/dmi/id/board_name', $buf, 1, 4096, false) && (trim($buf)!="")) {
                $board = trim($buf);
            }
            if (CommonFunctions::rfts('/sys/devices/virtual/dmi/id/bios_version', $buf, 1, 4096, false) && (trim($buf)!="")) {
                $bios = trim($buf);
            }
            if (CommonFunctions::rfts('/sys/devices/virtual/dmi/id/bios_date', $buf, 1, 4096, false) && (trim($buf)!="")) {
                $bios = trim($bios." ".trim($buf));
            }
            if ($product != "") {
                $machine .= " ".$product;
            }
            if ($board != "") {
                $machine .= "/".$board;
            }
            if ($bios != "") {
                $machine .= ", BIOS ".$bios;
            }
        }

        if ($machine != "") {
            $machine = trim(preg_replace("/^\/,?/", "", preg_replace("/ ?(To be filled by O\.E\.M\.|System manufacturer|System Product Name|Not Specified) ?/i", "", $machine)));
        }

        if (CommonFunctions::fileexists($filename="/etc/config/uLinux.conf") // QNAP detection
           && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
           && preg_match("/^Rsync\sModel\s*=\s*QNAP/m", $buf)
           && CommonFunctions::fileexists($filename="/etc/platform.conf") // Platform detection
           && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
           && preg_match("/^DISPLAY_NAME\s*=\s*(\S+)/m", $buf, $mach_buf) && ($mach_buf[1]!=="")) {
            if ($machine != "") {
                $machine = "QNAP ".$mach_buf[1].' - '.$machine;
            } else {
                $machine = "QNAP ".$mach_buf[1];
            }
        }

        if ($machine != "") {
            $this->sys->setMachine($machine);
        }
    }

    /**
     * Hostname
     *
     * @return void
     */
    protected function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            if (CommonFunctions::readenv('SERVER_NAME', $hnm)) $this->sys->setHostname($hnm);
        } else {
            if (CommonFunctions::rfts('/proc/sys/kernel/hostname', $result, 1, 4096, PSI_DEBUG && (PSI_OS != 'Android'))) {
                $result = trim($result);
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    $this->sys->setHostname(gethostbyaddr($ip));
                }
            } elseif (CommonFunctions::executeProgram('hostname', '', $ret)) {
                $this->sys->setHostname($ret);
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
        $result = "";
        if (CommonFunctions::executeProgram($uname="uptrack-uname", '-r', $strBuf, false) || // show effective kernel if ksplice uptrack is installed
            CommonFunctions::executeProgram($uname="uname", '-r', $strBuf, PSI_DEBUG)) {
            $result = $strBuf;
            if (CommonFunctions::executeProgram($uname, '-v', $strBuf, PSI_DEBUG)) {
                if (preg_match('/SMP/', $strBuf)) {
                    $result .= ' (SMP)';
                }
            }
            if (CommonFunctions::executeProgram($uname, '-m', $strBuf, PSI_DEBUG)) {
                $result .= ' '.$strBuf;
            }
        } elseif (CommonFunctions::rfts('/proc/version', $strBuf, 1) &&  preg_match('/version\s+(\S+)/', $strBuf, $ar_buf)) {
            $result = $ar_buf[1];
            if (preg_match('/SMP/', $strBuf)) {
                $result .= ' (SMP)';
            }
        }
        if ($result != "") {
            if (CommonFunctions::rfts('/proc/self/cgroup', $strBuf2, 0, 4096, false)) {
                if (preg_match('/:\/lxc\//m', $strBuf2)) {
                    $result .= ' [lxc]';
                } elseif (preg_match('/:\/docker\//m', $strBuf2)) {
                    $result .= ' [docker]';
                } elseif (preg_match('/:\/system\.slice\/docker\-/m', $strBuf2)) {
                    $result .= ' [docker]';
                }
            }
            if (CommonFunctions::rfts('/proc/version', $strBuf2, 1, 4096, false)
                && preg_match('/^Linux version [\d\.-]+-Microsoft/', $strBuf2)) {
                    $result .= ' [lxss]';
            }
            $this->sys->setKernel($result);
        }
    }

    /**
     * UpTime
     * time the system is running
     *
     * @return void
     */
    protected function _uptime()
    {
        if (CommonFunctions::rfts('/proc/uptime', $buf, 1, 4096, PSI_OS != 'Android')) {
            $ar_buf = preg_split('/ /', $buf);
            $this->sys->setUptime(trim($ar_buf[0]));
        } elseif (CommonFunctions::executeProgram('uptime', '', $buf)) {
            if (preg_match("/up (\d+) day[s]?,[ ]+(\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[3];
                $hours = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60);
            } elseif (preg_match("/up (\d+) day[s]?,[ ]+(\d+) min,/", $buf, $ar_buf)) {
                $min = $ar_buf[2];
                $days = $ar_buf[1];
                $this->sys->setUptime($days * 86400 + $min * 60);
            } elseif (preg_match("/up[ ]+(\d+):(\d+),/", $buf, $ar_buf)) {
                $min = $ar_buf[2];
                $hours = $ar_buf[1];
                $this->sys->setUptime($hours * 3600 + $min * 60);
            } elseif (preg_match("/up[ ]+(\d+) min,/", $buf, $ar_buf)) {
                $min = $ar_buf[1];
                $this->sys->setUptime($min * 60);
            }
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
        if (CommonFunctions::rfts('/proc/loadavg', $buf, 1, 4096, PSI_OS != 'Android')) {
            $result = preg_split("/\s/", $buf, 4);
            // don't need the extra values, only first three
            unset($result[3]);
            $this->sys->setLoad(implode(' ', $result));
        } elseif (CommonFunctions::executeProgram('uptime', '', $buf) && preg_match("/load average: (.*), (.*), (.*)$/", $buf, $ar_buf)) {
            $this->sys->setLoad($ar_buf[1].' '.$ar_buf[2].' '.$ar_buf[3]);
        }
        if (PSI_LOAD_BAR) {
            $this->sys->setLoadPercent($this->_parseProcStat('cpu'));
        }
    }

    /**
     * fill the load for a individual cpu, through parsing /proc/stat for the specified cpu
     *
     * @param String $cpuline cpu for which load should be meassured
     *
     * @return Integer
     */
    protected function _parseProcStat($cpuline)
    {
        if (is_null($this->_cpu_loads)) {
            $this->_cpu_loads = array();

            $cpu_tmp = array();
            if (CommonFunctions::rfts('/proc/stat', $buf)) {
                if (preg_match_all('/^(cpu[0-9]*) (.*)/m', $buf, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $line) {
                        $cpu = $line[1];
                        $buf2 = $line[2];

                        $cpu_tmp[$cpu] = array();

                        $ab = 0;
                        $ac = 0;
                        $ad = 0;
                        $ae = 0;
                        sscanf($buf2, "%Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                        $cpu_tmp[$cpu]['load'] = $ab + $ac + $ad; // cpu.user + cpu.sys
                        $cpu_tmp[$cpu]['total'] = $ab + $ac + $ad + $ae; // cpu.total
                    }
                }

                // we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
                sleep(1);

                if (CommonFunctions::rfts('/proc/stat', $buf)) {
                    if (preg_match_all('/^(cpu[0-9]*) (.*)/m', $buf, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $line) {
                            $cpu = $line[1];
                            if (isset($cpu_tmp[$cpu])) {
                                $buf2 = $line[2];

                                $ab = 0;
                                $ac = 0;
                                $ad = 0;
                                $ae = 0;
                                sscanf($buf2, "%Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
                                $load2 = $ab + $ac + $ad; // cpu.user + cpu.sys
                                $total2 = $ab + $ac + $ad + $ae; // cpu.total
                                $total = $cpu_tmp[$cpu]['total'];
                                $load = $cpu_tmp[$cpu]['load'];
                                $this->_cpu_loads[$cpu] = 0;
                                if ($total > 0 && $total2 > 0 && $load > 0 && $load2 > 0 && $total2 != $total && $load2 != $load) {
                                    $this->_cpu_loads[$cpu] = (100 * ($load2 - $load)) / ($total2 - $total);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($this->_cpu_loads[$cpuline])) {
            return $this->_cpu_loads[$cpuline];
        } else {
            return 0;
        }
    }

    /**
     * CPU information
     * All of the tags here are highly architecture dependant.
     *
     * @return void
     */
    protected function _cpuinfo()
    {
        if (CommonFunctions::rfts('/proc/cpuinfo', $bufr)) {
            $cpulist = null;
            $raslist = null;

            // sparc
            if (preg_match('/\nCpu(\d+)Bogo\s*:/i', $bufr)) {
                $bufr = preg_replace('/\nCpu(\d+)ClkTck\s*:/i', "\nCpu0ClkTck:", preg_replace('/\nCpu(\d+)Bogo\s*:/i', "\n\nprocessor: $1\nCpu0Bogo:", $bufr));
            } else {
                $bufr = preg_replace('/\nCpu(\d+)ClkTck\s*:/i', "\n\nprocessor: $1\nCpu0ClkTck:", $bufr);
            }

            if (preg_match('/\nprocessor\s*:\s*\d+\r?\nprocessor\s*:\s*\d+/', $bufr)) {
                $bufr = preg_replace('/^(processor\s*:\s*\d+)\r?$/m', "$1\n", $bufr);
            }

            // IBM/S390
            $bufr = preg_replace('/\ncpu number\s*:\s*(\d+)\r?\ncpu MHz dynamic\s*:\s*(\d+)/m', "\nprocessor:$1\nclock:$2", $bufr);

            $processors = preg_split('/\s?\n\s?\n/', trim($bufr));

            //first stage
            $_arch = null;
            $_impl = null;
            $_part = null;
            $_hard = null;
            $_revi = null;
            $_cpus = null;
            $_buss = null;
            $_bogo = null;
            $_vend = null;
            $procname = null;
            foreach ($processors as $processor) if (!preg_match('/^\s*processor\s*:/mi', $processor)) {
                $details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($details as $detail) {
                    $arrBuff = preg_split('/\s*:\s*/', trim($detail));
                    if ((count($arrBuff) == 2) && (($arrBuff1 = trim($arrBuff[1])) !== '')) {
                        switch (strtolower($arrBuff[0])) {
                        case 'cpu architecture':
                            $_arch = $arrBuff1;
                            break;
                        case 'cpu implementer':
                            $_impl = $arrBuff1;
                            break;
                        case 'cpu part':
                            $_part = $arrBuff1;
                            break;
                        case 'hardware':
                            $_hard = $arrBuff1;
                            break;
                        case 'revision':
                            $_revi = $arrBuff1;
                            break;
                        case 'cpu frequency':
                            if (preg_match('/^(\d+)\s+Hz/i', $arrBuff1, $bufr2)) {
                                $_cpus = round($bufr2[1]/1000000);
                            }
                            break;
                        case 'system bus frequency':
                            if (preg_match('/^(\d+)\s+Hz/i', $arrBuff1, $bufr2)) {
                                $_buss = round($bufr2[1]/1000000);
                            }
                            break;
                        case 'bogomips per cpu':
                            $_bogo = round($arrBuff1);
                            break;
                        case 'vendor_id':
                            $_vend = $arrBuff1;
                        case 'cpu':
                            $procname = $arrBuff1;
                            break;
                        }
                    }
                }
            }

            //second stage
            $cpucount = 0;
            $speedset = false;
            foreach ($processors as $processor) if (preg_match('/^\s*processor\s*:/mi', $processor)) {
                $proc = null;
                $arch = null;
                $impl = null;
                $part = null;
                $dev = new CpuDevice();
                $details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($details as $detail) {
                    $arrBuff = preg_split('/\s*:\s*/', trim($detail));
                    if ((count($arrBuff) == 2) && (($arrBuff1 = trim($arrBuff[1])) !== '')) {
                        switch (strtolower($arrBuff[0])) {
                        case 'processor':
                            $proc = $arrBuff1;
                            if (is_numeric($proc)) {
                                if (strlen($procname)>0) {
                                    $dev->setModel($procname);
                                }
                            } else {
                                $procname = $proc;
                                $dev->setModel($procname);
                            }
                            break;
                        case 'model name':
                        case 'cpu model':
                        case 'cpu type':
                        case 'cpu':
                            $dev->setModel($arrBuff1);
                            break;
                        case 'cpu mhz':
                        case 'clock':
                            if ($arrBuff1 > 0) { //openSUSE fix
                                $dev->setCpuSpeed($arrBuff1);
                                $speedset = true;
                            }
                            break;
                        case 'cpu mhz static':
                            if ($arrBuff1 > 0) { //openSUSE fix
                                $dev->setCpuSpeedMax($arrBuff1);
                            }
                            break;
                        case 'cycle frequency [hz]':
                            $dev->setCpuSpeed($arrBuff1 / 1000000);
                            $speedset = true;
                            break;
                        case 'cpu0clktck':
                            $dev->setCpuSpeed(hexdec($arrBuff1) / 1000000); // Linux sparc64
                            $speedset = true;
                            break;
                        case 'l3 cache':
                        case 'cache size':
                            $dev->setCache(trim(preg_replace("/[a-zA-Z]/", "", $arrBuff1)) * 1024);
                            break;
                        case 'initial bogomips':
                        case 'bogomips':
                        case 'cpu0bogo':
                            $dev->setBogomips(round($arrBuff1));
                            break;
                        case 'flags':
                            if (preg_match("/ vmx/", $arrBuff1)) {
                                $dev->setVirt("vmx");
                            } elseif (preg_match("/ svm/", $arrBuff1)) {
                                $dev->setVirt("svm");
                            } elseif (preg_match("/ hypervisor/", $arrBuff1)) {
                                $dev->setVirt("hypervisor");
                            }
                            break;
                        case 'i size':
                        case 'd size':
                            if ($dev->getCache() === null) {
                                $dev->setCache($arrBuff1 * 1024);
                            } else {
                                $dev->setCache($dev->getCache() + ($arrBuff1 * 1024));
                            }
                            break;
                        case 'cpu architecture':
                            $arch = $arrBuff1;
                            break;
                        case 'cpu implementer':
                            $impl = $arrBuff1;
                            break;
                        case 'cpu part':
                            $part = $arrBuff1;
                            break;
                        case 'vendor_id':
                            $dev->setVendorId($arrBuff1);
                            break;
                        }
                    }
                }
                if ($arch === null) $arch = $_arch;
                if ($impl === null) $impl = $_impl;
                if ($part === null) $part = $_part;

                // sparc64 specific code follows
                // This adds the ability to display the cache that a CPU has
                // Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
                // Modified by Tom Weustink <freshy98@gmx.net> in 2004
                $sparclist = array('SUNW,UltraSPARC@0,0', 'SUNW,UltraSPARC-II@0,0', 'SUNW,UltraSPARC@1c,0', 'SUNW,UltraSPARC-IIi@1c,0', 'SUNW,UltraSPARC-II@1c,0', 'SUNW,UltraSPARC-IIe@0,0');
                foreach ($sparclist as $name) {
                    if (CommonFunctions::rfts('/proc/openprom/'.$name.'/ecache-size', $buf, 1, 32, false)) {
                        $dev->setCache(base_convert(trim($buf), 16, 10));
                    }
                }
                // sparc64 specific code ends

                // XScale detection code
                if (($arch === "5TE") && ($dev->getBogomips() != null)) {
                    $dev->setCpuSpeed($dev->getBogomips()); //BogoMIPS are not BogoMIPS on this CPU, it's the speed
                    $speedset = true;
                    $dev->setBogomips(null); // no BogoMIPS available, unset previously set BogoMIPS
                }

                if (($dev->getBusSpeed() == 0) && ($_buss !== null)) {
                    $dev->setBusSpeed($_buss);
                }
                if (($dev->getCpuSpeed() == 0) && ($_cpus !== null)) {
                    $dev->setCpuSpeed($_cpus);
                    $speedset = true;
                }
                if (($dev->getBogomips() == 0) && ($_bogo !== null)) {
                    $dev->setBogomips($_bogo);
                }
                if (($dev->getVendorId() === null) && ($_vend !== null)) {
                    $dev->setVendorId($_vend);
                }

                if ($proc != null) {
                    if (!is_numeric($proc)) {
                        $proc = 0;
                    }
                    // variable speed processors specific code follows
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_cur_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeed(trim($buf) / 1000);
                        $speedset = true;
                    } elseif (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/scaling_cur_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeed(trim($buf) / 1000);
                        $speedset = true;
                    }
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_max_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeedMax(trim($buf) / 1000);
                    }
                    if (CommonFunctions::rfts('/sys/devices/system/cpu/cpu'.$proc.'/cpufreq/cpuinfo_min_freq', $buf, 1, 4096, false)) {
                        $dev->setCpuSpeedMin(trim($buf) / 1000);
                    }
                    // variable speed processors specific code ends
                    if (PSI_LOAD_BAR) {
                            $dev->setLoad($this->_parseProcStat('cpu'.$proc));
                    }
/*
                    if (CommonFunctions::rfts('/proc/acpi/thermal_zone/THRM/temperature', $buf, 1, 4096, false)
                       &&  preg_match("/(\S+)\sC$/", $buf, $value)) {
                        $dev->setTemp(value[1]);
                    }
*/
                    if (($arch !== null) && ($impl !== null) && ($part !== null)) {
                        if (($impl === '0x41')
                           && (($_hard === 'BCM2708') || ($_hard === 'BCM2835') || ($_hard === 'BCM2709') || ($_hard === 'BCM2836') || ($_hard === 'BCM2710') || ($_hard === 'BCM2837') || ($_hard === 'BCM2711') || ($_hard === 'BCM2838'))
                           && ($_revi !== null)) { // Raspberry Pi detection (instead of 'cat /proc/device-tree/model')
                            if ($raslist === null) $raslist = @parse_ini_file(PSI_APP_ROOT."/data/raspberry.ini", true);
                            if ($raslist && !preg_match('/[^0-9a-f]/', $_revi)) {
                                if (($revidec = hexdec($_revi)) & 0x800000) {
                                    if ($this->sys->getMachine() === '') {
                                        $manufacturer = ($revidec >> 16) & 15;
                                        if (isset($raslist['manufacturer'][$manufacturer])) {
                                            $manuf = ' '.$raslist['manufacturer'][$manufacturer];
                                        } else {
                                            $manuf = '';
                                        }
                                        $model = ($revidec >> 4) & 255;
                                        if (isset($raslist['model'][$model])) {
                                            $this->sys->setMachine('Raspberry Pi '.$raslist['model'][$model].' (PCB 1.'.($revidec & 15).$manuf.')');
                                        } else {
                                            $this->sys->setMachine('Raspberry Pi (PCB 1.'.($revidec & 15).$manuf.')');
                                        }
                                    }
                                } else {
                                    if ($this->sys->getMachine() === '') {
                                        if (isset($raslist['old'][$revidec & 0x7fffff])) {
                                            $this->sys->setMachine('Raspberry Pi '.$raslist['old'][$revidec & 0x7fffff]);
                                        } else {
                                            $this->sys->setMachine('Raspberry Pi');
                                        }
                                    }
                                }
                            }
                        } elseif (($_hard !== null) && ($this->sys->getMachine() === '')) { // other ARM hardware
                            $this->sys->setMachine($_hard);
                        }
                        if ($cpulist === null) $cpulist = @parse_ini_file(PSI_APP_ROOT."/data/cpus.ini", true);
                        if ($cpulist && (isset($cpulist['cpu'][$cpuimplpart = strtolower($impl.','.$part)]))) {
                            if (($cpumodel = $dev->getModel()) !== '') {
                                $dev->setModel($cpumodel.' - '.$cpulist['cpu'][$cpuimplpart]);
                            } else {
                                $dev->setModel($cpulist['cpu'][$cpuimplpart]);
                            }
                        }
                    } elseif (($_hard !== null) && ($this->sys->getMachine() === '')) { // other hardware
                        $this->sys->setMachine($_hard);
                    }

                    if ($dev->getModel() === "") {
                        $dev->setModel("unknown");
                    }
                    $cpucount++;
                    $this->sys->setCpus($dev);
                }
            }

            $cpudevices = glob('/sys/devices/system/cpu/cpu*/uevent', GLOB_NOSORT);
            if (is_array($cpudevices) && (($cpustopped = count($cpudevices)-$cpucount) > 0)) {
                for (; $cpustopped > 0; $cpustopped--) {
                    $dev = new CpuDevice();
                    $dev->setModel("stopped");
                    if ($speedset) {
                        $dev->setCpuSpeed(-1);
                    }
                    $this->sys->setCpus($dev);
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
        if ($arrResults = Parser::lspci()) {
            foreach ($arrResults as $dev) {
                $this->sys->setPciDevices($dev);
            }
        } elseif (CommonFunctions::rfts('/proc/pci', $strBuf, 0, 4096, false)) {
            $booDevice = false;
            $arrBuf = preg_split("/\n/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($arrBuf as $strLine) {
                if (preg_match('/^\s*Bus\s/', $strLine)) {
                    $booDevice = true;
                    continue;
                }
                if ($booDevice) {
                    $dev = new HWDevice();
                    $dev->setName(preg_replace('/\([^\)]+\)\.$/', '', trim($strLine)));
                    $this->sys->setPciDevices($dev);
/*
                    list($strKey, $strValue) = preg_split('/: /', $strLine, 2);
                    if (!preg_match('/bridge/i', $strKey) && !preg_match('/USB/i ', $strKey)) {
                        $dev = new HWDevice();
                        $dev->setName(preg_replace('/\([^\)]+\)\.$/', '', trim($strValue)));
                        $this->sys->setPciDevices($dev);
                    }
*/
                    $booDevice = false;
                }
            }
        } else {
            $pcidevices = glob('/sys/bus/pci/devices/*/uevent', GLOB_NOSORT);
            if (is_array($pcidevices) && (($total = count($pcidevices)) > 0)) {
                $buf = "";
                for ($i = 0; $i < $total; $i++) {
                    if (CommonFunctions::rfts($pcidevices[$i], $buf, 0, 4096, false) && (trim($buf) != "")) {
                        $pcibuf = "";
                        if (preg_match("/^PCI_CLASS=(\S+)/m", trim($buf), $subbuf)) {
                            $pcibuf = "Class ".$subbuf[1].":";
                        }
                        if (preg_match("/^PCI_ID=(\S+)/m", trim($buf), $subbuf)) {
                            $pcibuf .= " Device ".$subbuf[1];
                        }
                        if (preg_match("/^DRIVER=(\S+)/m", trim($buf), $subbuf)) {
                            $pcibuf .= " Driver ".$subbuf[1];
                        }
                        $dev = new HWDevice();
                        if (trim($pcibuf) != "") {
                            $dev->setName(trim($pcibuf));
                        } else {
                            $dev->setName("unknown");
                        }
                        $this->sys->setPciDevices($dev);
                    }
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
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS && CommonFunctions::rfts("/proc/ide/".$file."/media", $buf, 1)) {
                    if (trim($buf) == 'disk') {
                        if (CommonFunctions::rfts("/proc/ide/".$file."/capacity", $buf, 1, 4096, false) || CommonFunctions::rfts("/sys/block/".$file."/size", $buf, 1, 4096, false)) {
                            $dev->setCapacity(trim($buf) * 512);
                        }
                    }
                }
                if (CommonFunctions::rfts("/proc/ide/".$file."/model", $buf, 1)) {
                    $dev->setName($dev->getName().": ".trim($buf));
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
        $getline = 0;
        $device = null;
        $scsiid = null;
        if (CommonFunctions::executeProgram('lsscsi', '-c', $bufr, PSI_DEBUG) || CommonFunctions::rfts('/proc/scsi/scsi', $bufr, 0, 4096, PSI_DEBUG)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/Host: scsi(\d+) Channel: (\d+) Target: (\d+) Lun: (\d+)/i', $buf, $scsiids)
                   || preg_match('/Host: scsi(\d+) Channel: (\d+) Id: (\d+) Lun: (\d+)/i', $buf, $scsiids)) {
                    $scsiid = $scsiids;
                    $getline = 1;
                    continue;
                }
                if ($getline == 1) {
                    preg_match('/Vendor: (.*) Model: (.*) Rev: (.*)/i', $buf, $devices);
                    $getline = 2;
                    $device = $devices;
                    continue;
                }
                if ($getline == 2) {
                    preg_match('/Type:\s+(\S+)/i', $buf, $dev_type);

                    $dev = new HWDevice();
                    $dev->setName($device[1].' '.$device[2].' ('.$dev_type[1].')');

                    if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                       && ($dev_type[1]==='Direct-Access')) {
                       $sizelist = glob('/sys/bus/scsi/devices/'.intval($scsiid[1]).':'.intval($scsiid[2]).':'.intval($scsiid[3]).':'.intval($scsiid[4]).'/*/*/size', GLOB_NOSORT);
                       if (is_array($sizelist) && (($total = count($sizelist)) > 0)) {
                           $buf = "";
                           for ($i = 0; $i < $total; $i++) {
                               if (CommonFunctions::rfts($sizelist[$i], $buf, 1, 4096, false) && (($buf=trim($buf)) != "") && ($buf > 0)) {
                                   $dev->setCapacity($buf * 512);
                                   break;
                               }
                           }
                       }
                    }
                    $this->sys->setScsiDevices($dev);
                    $getline = 0;
                }
            }
        }
    }

    /**
     * USB devices
     *
     * @return void
     */
    protected function _usb()
    {
        $usbarray = array();
        if (CommonFunctions::executeProgram('lsusb', (PSI_OS != 'Android')?'':'2>/dev/null', $bufr, PSI_DEBUG && (PSI_OS != 'Android'), 5) && ($bufr !== "")) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                $device = preg_split("/ /", $buf, 7);
                if (((isset($device[6]) && trim($device[6]) != "")) ||
                    ((isset($device[5]) && trim($device[5]) != ""))) {
                    $usbid = intval($device[1]).'-'.intval(trim($device[3],':')).' '.$device[5];
                    if ((isset($device[6]) && trim($device[6]) != "")) {
                        $usbarray[$usbid]['name'] = trim($device[6]);
                    } else {
                        $usbarray[$usbid]['name'] = 'unknown';
                    }
                }
            }
        }

        $usbdevices = glob('/sys/bus/usb/devices/*/idProduct', GLOB_NOSORT);
        if (is_array($usbdevices) && (($total = count($usbdevices)) > 0)) {
            for ($i = 0; $i < $total; $i++) {
                if (CommonFunctions::rfts($usbdevices[$i], $idproduct, 1, 4096, false) && (($idproduct=trim($idproduct)) != "")) { //is readable
                    $busnum = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/busnum');
                    $devnum = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/devnum');
                    $idvendor = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/idVendor');
                    if (($busnum!==null) && ($devnum!==null) && ($idvendor!==null)) {
                        $usbid = intval($busnum).'-'.intval($devnum).' '.$idvendor.':'.$idproduct;
                        $manufacturer = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/manufacturer');
                        if ($manufacturer!==null) {
                            $usbarray[$usbid]['manufacturer'] = $manufacturer;
                        }
                        $product = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/product');
                        if ($product!==null) {
                            $usbarray[$usbid]['product'] = $product;
                        }
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                           && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                            $serial = CommonFunctions::rolv($usbdevices[$i], '/\/idProduct$/', '/serial');
                            if (($serial!==null) && !preg_match('/\W/', $serial)) {
                                $usbarray[$usbid]['serial'] = $serial;
                            }
                        }
                    }
                }
            }
        }

        if ((count($usbarray) == 0) && CommonFunctions::rfts('/proc/bus/usb/devices', $bufr, 0, 4096, false)) {
            $devnum = -1;
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/^T/', $buf)) {
                    $devnum++;
                } elseif (preg_match('/^S:/', $buf)) {
                    list($key, $value) = preg_split('/: /', $buf, 2);
                    list($key, $value2) = preg_split('/=/', $value, 2);
                    switch (trim($key)) {
                    case 'Manufacturer':
                        $usbarray[$devnum]['manufacturer'] = trim($value2);
                        break;
                    case 'Product':
                        $usbarray[$devnum]['product'] = trim($value2);
                        break;
                    case 'SerialNumber':
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                           && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL
                           && !preg_match('/\W/', trim($value2))) {
                            $usbarray[$devnum]['serial'] = trim($value2);
                         }
                         break;
                    }
                }
            }
        }

        if ((count($usbarray) == 0) && CommonFunctions::rfts('/proc/bus/input/devices', $bufr, 0, 4096, false)) {
            $devnam = "unknown";
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/^I:\s+(.+)/', $buf, $bufr)
                   && isset($bufr[1]) && (trim($bufr[1])!=="")) {
                    $devnam = trim($bufr[1]);
                    $usbarray[$devnam]['phys'] = 'unknown';
                } elseif (preg_match('/^N:\s+Name="([^"]+)"/', $buf, $bufr2)
                   && isset($bufr2[1]) && (trim($bufr2[1])!=="")) {
                    $usbarray[$devnam]['name'] = trim($bufr2[1]);
                } elseif (preg_match('/^P:\s+Phys=(.*)/', $buf, $bufr2)
                   && isset($bufr2[1]) && (trim($bufr2[1])!=="")) {
                    $usbarray[$devnam]['phys'] = trim($bufr2[1]);
                } elseif (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                   && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL
                   && preg_match('/^U:\s+Uniq=(.+)/', $buf, $bufr2)
                   && isset($bufr2[1]) && (trim($bufr2[1])!=="")) {
                    $usbarray[$devnam]['serial'] = trim($bufr2[1]);
                }
            }
        }

        foreach ($usbarray as $usbdev) if (!isset($usbdev['phys']) || preg_match('/^usb-/', $usbdev['phys'])) {
            $dev = new HWDevice();

            if (isset($usbdev['manufacturer']) && (($manufacturer=$usbdev['manufacturer']) !== 'no manufacturer')) {
                if (preg_match("/^linux\s/i", $manufacturer)) {
                    $manufacturer = 'Linux Foundation';
                }
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    $dev->setManufacturer($manufacturer);
                }
            } else {
                $manufacturer = '';
            }

            if (isset($usbdev['product'])) {
                $product = $usbdev['product'];
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    $dev->setProduct($product);
                }
            } else {
                $product = '';
            }

            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS
                && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL
                && isset($usbdev['serial'])) {
                $dev->setSerial($usbdev['serial']);
            }

            if (isset($usbdev['name']) && (($name=$usbdev['name']) !== 'unknown')) {
                $dev->setName($name);
            } else {
                if (($newname = trim($manufacturer.' '.$product)) !== '') {
                    $dev->setName($newname);
                } else {
                    $dev->setName('unknown');
                }
            }

            $this->sys->setUsbDevices($dev);
        }
    }

    /**
     * I2C devices
     *
     * @return void
     */
    protected function _i2c()
    {
        $i2cdevices = glob('/sys/bus/i2c/devices/*/name', GLOB_NOSORT);
        if (is_array($i2cdevices) && (($total = count($i2cdevices)) > 0)) {
            $buf = "";
            for ($i = 0; $i < $total; $i++) {
                if (CommonFunctions::rfts($i2cdevices[$i], $buf, 1, 4096, false) && (trim($buf) != "")) {
                    $dev = new HWDevice();
                    $dev->setName(trim($buf));
                    $this->sys->setI2cDevices($dev);
                }
            }
        }
    }

    /**
     * NVMe devices
     *
     * @return void
     */
    protected function _nvme()
    {
        if (CommonFunctions::executeProgram('nvme', 'list', $bufr, PSI_DEBUG) && ($bufr!="")) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $count = 0;
            $nlocate = array();
            $nsize = array();
            foreach ($bufe as $buf) {
                if ($count == 1) {
                    $locid = 0;
                    $nlocate[0] = 0;
                    $total = strlen($buf);
                    $begin = true;
                    for ($i = 0; $i < $total; $i++) {
                        if ($begin) {
                            if ($buf[$i] !== '-') {
                                $nsize[$locid] = $i - $nlocate[$locid];
                                $locid++;
                                $begin = false;
                            }
                        } else {
                            if ($buf[$i] === '-') {
                                $nlocate[$locid] = $i;
                                $begin = true;
                            }
                        }
                    }
                    if ($begin) {
                        $nsize[$locid] = $i - $nlocate[$locid];
                    }
                } elseif ($count > 1) {
                    if (isset($nlocate[2]) && isset($nsize[2])) {
                        $dev = new HWDevice();
                        $dev->setName(trim(substr($buf, $nlocate[2], $nsize[2])));
                        if (defined('PSI_SHOW_DEVICES_INFOS') && (PSI_SHOW_DEVICES_INFOS)) {
                            if (isset($nlocate[4]) && isset($nsize[4])) {
                                if (preg_match('/\/\s*([0-9\.]+)\s*(B|KB|MB|GB|TB|PB)$/', str_replace(',', '.', trim(substr($buf, $nlocate[4], $nsize[4]))), $tmpbuf)) {
                                    switch ($tmpbuf[2]) {
                                        case 'B':
                                            $dev->setCapacity($tmpbuf[1]);
                                            break;
                                        case 'KB':
                                            $dev->setCapacity(1000*$tmpbuf[1]);
                                            break;
                                        case 'MB':
                                            $dev->setCapacity(1000*1000*$tmpbuf[1]);
                                            break;
                                        case 'GB':
                                            $dev->setCapacity(1000*1000*1000*$tmpbuf[1]);
                                            break;
                                        case 'TB':
                                            $dev->setCapacity(1000*1000*1000*1000*$tmpbuf[1]);
                                            break;
                                        case 'PB':
                                            $dev->setCapacity(1000*1000*1000*1000*1000*$tmpbuf[1]);
                                            break;
                                    }
                                }
                            }
                            if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                                if (isset($nlocate[1]) && isset($nsize[1])) {
                                    $dev->setSerial(trim(substr($buf, $nlocate[1], $nsize[1])));
                                }
                            }
                        }
                        $this->sys->setNvmeDevices($dev);
                    }
                }
                $count++;
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
        if (CommonFunctions::rfts('/proc/net/dev', $bufr, 0, 4096, PSI_DEBUG)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/:/', $buf)) {
                    list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
                    $stats = preg_split('/\s+/', trim($stats_list));
                    $dev = new NetDevice();
                    $dev->setName(trim($dev_name));
                    $dev->setRxBytes($stats[0]);
                    $dev->setTxBytes($stats[8]);
                    $dev->setErrors($stats[2] + $stats[10]);
                    $dev->setDrops($stats[3] + $stats[11]);
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                        $macaddr = "";
                        if ((CommonFunctions::executeProgram('ip', 'addr show '.trim($dev_name), $bufr2, PSI_DEBUG) && ($bufr2!=""))
                           || CommonFunctions::executeProgram('ifconfig', trim($dev_name).' 2>/dev/null', $bufr2, PSI_DEBUG)) {
                            $bufe2 = preg_split("/\n/", $bufr2, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($bufe2 as $buf2) {
//                                if (preg_match('/^'.trim($dev_name).'\s+Link\sencap:Ethernet\s+HWaddr\s(\S+)/i', $buf2, $ar_buf2)
                                if (preg_match('/\s+encap:Ethernet\s+HWaddr\s(\S+)/i', $buf2, $ar_buf2)
                                   || preg_match('/\s+encap:UNSPEC\s+HWaddr\s(\S+)-00-00-00-00-00-00-00-00-00-00\s*$/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+ether\s+(\S+)\s+txqueuelen/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+link\/ether\s+(\S+)\s+brd/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+link\/ether\s+(\S+)$/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+link\/ieee802.11\s+(\S+)$/i', $buf2, $ar_buf2)) {
                                    if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) {
                                        $macaddr = preg_replace('/:/', '-', strtoupper($ar_buf2[1]));
                                        if ($macaddr === '00-00-00-00-00-00') { // empty
                                            $macaddr = "";
                                        }
                                    }
                                } elseif (preg_match('/^\s+inet\saddr:(\S+)\s+P-t-P:(\S+)/i', $buf2, $ar_buf2)
                                       || preg_match('/^\s+inet\s+(\S+)\s+netmask.+destination\s+(\S+)/i', $buf2, $ar_buf2)
                                       || preg_match('/^\s+inet\s+([^\/\s]+).*peer\s+([^\/\s]+).*\s+scope\s((global)|(host))/i', $buf2, $ar_buf2)) {
                                    if ($ar_buf2[1] != $ar_buf2[2]) {
                                        $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                    } else {
                                        $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                    }
                                } elseif ((preg_match('/^\s+inet\saddr:(\S+)/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $buf2, $ar_buf2)
                                   || preg_match('/^'.trim($dev_name).':\s+ip\s+(\S+)\s+mask/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+inet6\saddr:\s([^\/\s]+)(.+)\s+Scope:[GH]/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+inet6\s+(\S+)\s+prefixlen(.+)((<global>)|(<host>))/i', $buf2, $ar_buf2)
                                   || preg_match('/^\s+inet6?\s+([^\/\s]+).*\s+scope\s((global)|(host))/i', $buf2, $ar_buf2))
                                   && ($ar_buf2[1]!="::") && !preg_match('/^fe80::/i', $ar_buf2[1])) {
                                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ar_buf2[1]));
                                }
                            }
                        }
                        if ($macaddr != "") {
                            $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                        }
                        if (CommonFunctions::rfts('/sys/class/net/'.trim($dev_name).'/speed', $buf, 1, 4096, false) && (($speed=trim($buf))!="") && ($buf > 0) && ($buf < 65535)) {
                            if ($speed > 1000) {
                                $speed = $speed/1000;
                                $unit = "G";
                            } else {
                                $unit = "M";
                            }
                            if (CommonFunctions::rfts('/sys/class/net/'.trim($dev_name).'/duplex', $buf, 1, 4096, false) && (($duplex=strtolower(trim($buf)))!="") && ($duplex!='unknown')) {
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speed.$unit.'b/s '.$duplex);
                            } else {
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speed.$unit.'b/s');
                            }
                        }
                    }
                    $this->sys->setNetDevices($dev);
                }
            }
        } elseif (CommonFunctions::executeProgram('ip', 'addr show', $bufr, PSI_DEBUG) && ($bufr!="")) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $was = false;
            $macaddr = "";
            $speedinfo = "";
            $dev = null;
            foreach ($lines as $line) {
                if (preg_match("/^\d+:\s+([^\s:]+)/", $line, $ar_buf)) {
                    if ($was) {
                        if ($macaddr != "") {
                            $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                        }
                        if ($speedinfo != "") {
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speedinfo);
                        }
                        $this->sys->setNetDevices($dev);
                    }
                    $speedinfo = "";
                    $macaddr = "";
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[1]);
                    if (CommonFunctions::executeProgram('ip', '-s link show '.$ar_buf[1], $bufr2, PSI_DEBUG) && ($bufr2!="")
                       && preg_match("/\n\s+RX:\s[^\n]+\n\s+(\d+)\s+\d+\s+(\d+)\s+(\d+)[^\n]+\n\s+TX:\s[^\n]+\n\s+(\d+)\s+\d+\s+(\d+)\s+(\d+)/m", $bufr2, $ar_buf2)) {
                        $dev->setRxBytes($ar_buf2[1]);
                        $dev->setTxBytes($ar_buf2[4]);
                        $dev->setErrors($ar_buf2[2]+$ar_buf2[5]);
                        $dev->setDrops($ar_buf2[3]+$ar_buf2[6]);
                    }
                    $was = true;
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                        if (CommonFunctions::rfts('/sys/class/net/'.$ar_buf[1].'/speed', $buf, 1, 4096, false) && (trim($buf)!="")) {
                            $speed = trim($buf);
                            if ($speed > 1000) {
                                $speed = $speed/1000;
                                $unit = "G";
                            } else {
                                $unit = "M";
                            }
                            if (CommonFunctions::rfts('/sys/class/net/'.$ar_buf[1].'/duplex', $buf, 1, 4096, false) && (trim($buf)!="")) {
                                $speedinfo = $speed.$unit.'b/s '.strtolower(trim($buf));
                            } else {
                                $speedinfo = $speed.$unit.'b/s';
                            }
                        }
                    }
                } else {
                    if ($was) {
                        if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            if (preg_match('/^\s+link\/ether\s+(\S+)\s+brd/i', $line, $ar_buf2)) {
                                if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) $macaddr = preg_replace('/:/', '-', strtoupper($ar_buf2[1]));
                            } elseif (preg_match('/^\s+inet\s+([^\/\s]+).*peer\s+([^\/\s]+).*\s+scope\s((global)|(host))/i', $line, $ar_buf2)) {
                                if ($ar_buf2[1] != $ar_buf2[2]) {
                                     $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                } else {
                                     $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                }
                            } elseif (preg_match('/^\s+inet6?\s+([^\/\s]+).*\s+scope\s((global)|(host))/i', $line, $ar_buf2)
                                     && ($ar_buf2[1]!="::") && !preg_match('/^fe80::/i', $ar_buf2[1])) {
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ar_buf2[1]));
                            }
                        }
                    }
                }
            }
            if ($was) {
                if ($macaddr != "") {
                    $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                }
                if ($speedinfo != "") {
                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speedinfo);
                }
                $this->sys->setNetDevices($dev);
            }
        } elseif (CommonFunctions::executeProgram('ifconfig', '-a', $bufr, PSI_DEBUG)) {
            $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            $was = false;
            $errors = 0;
            $drops = 0;
            $macaddr = "";
            $speedinfo = "";
            $dev = null;
            foreach ($lines as $line) {
                if (preg_match("/^([^\s:]+)/", $line, $ar_buf)) {
                    if ($was) {
                        $dev->setErrors($errors);
                        $dev->setDrops($drops);
                        if ($macaddr != "") {
                            $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                        }
                        if ($speedinfo != "") {
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speedinfo);
                        }
                        $this->sys->setNetDevices($dev);
                    }
                    $errors = 0;
                    $drops = 0;
                    $speedinfo = "";
                    $macaddr = "";
                    $dev = new NetDevice();
                    $dev->setName($ar_buf[1]);
                    $was = true;
                    if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                        if (CommonFunctions::rfts('/sys/class/net/'.$ar_buf[1].'/speed', $buf, 1, 4096, false) && (trim($buf)!="")) {
                            $speed = trim($buf);
                            if ($speed > 1000) {
                                $speed = $speed/1000;
                                $unit = "G";
                            } else {
                                $unit = "M";
                            }
                            if (CommonFunctions::rfts('/sys/class/net/'.$ar_buf[1].'/duplex', $buf, 1, 4096, false) && (trim($buf)!="")) {
                                $speedinfo = $speed.$unit.'b/s '.strtolower(trim($buf));
                            } else {
                                $speedinfo = $speed.$unit.'b/s';
                            }
                        }
                        if (preg_match('/^'.$ar_buf[1].'\s+Link\sencap:Ethernet\s+HWaddr\s(\S+)/i', $line, $ar_buf2))
                            if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) $macaddr = preg_replace('/:/', '-', strtoupper($ar_buf2[1]));
                        elseif (preg_match('/^'.$ar_buf[1].':\s+ip\s+(\S+)\s+mask/i', $line, $ar_buf2))
                            $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                    }
                } else {
                    if ($was) {
                        if (preg_match('/\sRX bytes:(\d+)\s/i', $line, $ar_buf2)) {
                            $dev->setRxBytes($ar_buf2[1]);
                        }
                        if (preg_match('/\sTX bytes:(\d+)\s/i', $line, $ar_buf2)) {
                            $dev->setTxBytes($ar_buf2[1]);
                        }

                        if (preg_match('/\sRX packets:\d+\serrors:(\d+)\sdropped:(\d+)/i', $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[2];
                        } elseif (preg_match('/\sTX packets:\d+\serrors:(\d+)\sdropped:(\d+)/i', $line, $ar_buf2)) {
                            $errors +=$ar_buf2[1];
                            $drops +=$ar_buf2[2];
                        }

                        if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                            if (preg_match('/\s+encap:Ethernet\s+HWaddr\s(\S+)/i', $line, $ar_buf2)
                             || preg_match('/^\s+ether\s+(\S+)\s+txqueuelen/i', $line, $ar_buf2)) {
                                if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) $macaddr = preg_replace('/:/', '-', strtoupper($ar_buf2[1]));
                            } elseif (preg_match('/^\s+inet\saddr:(\S+)\s+P-t-P:(\S+)/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask.+destination\s+(\S+)/i', $line, $ar_buf2)) {
                                if ($ar_buf2[1] != $ar_buf2[2]) {
                                     $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1].";:".$ar_buf2[2]);
                                } else {
                                     $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ar_buf2[1]);
                                }
                            } elseif ((preg_match('/^\s+inet\saddr:(\S+)/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet\s+(\S+)\s+netmask/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet6\saddr:\s([^\/\s]+)(.+)\s+Scope:[GH]/i', $line, $ar_buf2)
                                  || preg_match('/^\s+inet6\s+(\S+)\s+prefixlen(.+)((<global>)|(<host>))/i', $line, $ar_buf2))
                                  && ($ar_buf2[1]!="::") && !preg_match('/^fe80::/i', $ar_buf2[1])) {
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ar_buf2[1]));
                            }
                        }
                    }
                }
            }
            if ($was) {
                $dev->setErrors($errors);
                $dev->setDrops($drops);
                if ($macaddr != "") {
                    $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                }
                if ($speedinfo != "") {
                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$speedinfo);
                }
                $this->sys->setNetDevices($dev);
            }
        }
    }

    /**
     * Physical memory information and Swap Space information
     *
     * @return void
     */
    protected function _memory()
    {
        if (CommonFunctions::rfts('/proc/meminfo', $mbuf)) {
            $bufe = preg_split("/\n/", $mbuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                if (preg_match('/^MemTotal:\s+(\d+)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemTotal($ar_buf[1] * 1024);
                } elseif (preg_match('/^MemFree:\s+(\d+)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemFree($ar_buf[1] * 1024);
                } elseif (preg_match('/^Cached:\s+(\d+)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemCache($ar_buf[1] * 1024);
                } elseif (preg_match('/^Buffers:\s+(\d+)\s*kB/i', $buf, $ar_buf)) {
                    $this->sys->setMemBuffer($ar_buf[1] * 1024);
                }
            }
            $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
            // values for splitting memory usage
            if ($this->sys->getMemCache() !== null && $this->sys->getMemBuffer() !== null) {
                $this->sys->setMemApplication($this->sys->getMemUsed() - $this->sys->getMemCache() - $this->sys->getMemBuffer());
            }
            if (CommonFunctions::rfts('/proc/swaps', $sbuf, 0, 4096, false)) {
                $swaps = preg_split("/\n/", $sbuf, -1, PREG_SPLIT_NO_EMPTY);
                unset($swaps[0]);
                foreach ($swaps as $swap) {
                    $ar_buf = preg_split('/\s+/', $swap, 5);
                    $dev = new DiskDevice();
                    $dev->setMountPoint($ar_buf[0]);
                    $dev->setName("SWAP");
                    $dev->setTotal($ar_buf[2] * 1024);
                    $dev->setUsed($ar_buf[3] * 1024);
                    $dev->setFree($dev->getTotal() - $dev->getUsed());
                    $this->sys->setSwapDevices($dev);
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
        $df_args = "";
        $hideFstypes = array();
        if (defined('PSI_HIDE_FS_TYPES') && is_string(PSI_HIDE_FS_TYPES)) {
            if (preg_match(ARRAY_EXP, PSI_HIDE_FS_TYPES)) {
                $hideFstypes = eval(PSI_HIDE_FS_TYPES);
            } else {
                $hideFstypes = array(PSI_HIDE_FS_TYPES);
            }
        }
        foreach ($hideFstypes as $Fstype) {
            $df_args .= "-x $Fstype ";
        }
        if ($df_args !== "") {
            $df_args = trim($df_args); //trim spaces
            $arrResult = Parser::df("-P $df_args 2>/dev/null");
        } else {
            $arrResult = Parser::df("-P 2>/dev/null");
        }
        foreach ($arrResult as $dev) {
            $this->sys->setDiskDevices($dev);
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    protected function _distro()
    {
        $this->sys->setDistribution("Linux");
        $list = @parse_ini_file(PSI_APP_ROOT."/data/distros.ini", true);
        if (!$list) {
            return;
        }
        // We have the '2>/dev/null' because Ubuntu gives an error on this command which causes the distro to be unknown
        if (CommonFunctions::executeProgram('lsb_release', '-a 2>/dev/null', $distro_info, PSI_DEBUG) && (strlen($distro_info) > 0)) {
            $distro_tmp = preg_split("/\n/", $distro_info, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($distro_tmp as $info) {
                $info_tmp = preg_split('/:/', $info, 2);
                if (isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "") &&
                     isset($distro_tmp[1]) && !is_null($distro_tmp[1]) && (trim($distro_tmp[1]) != "")) {
                    $distro[trim($info_tmp[0])] = trim($info_tmp[1]);
                }
            }
            if (!isset($distro['Distributor ID']) && !isset($distro['Description'])) { // Systems like StartOS
                if (isset($distro_tmp[0]) && !is_null($distro_tmp[0]) && (trim($distro_tmp[0]) != "")) {
                    $this->sys->setDistribution(trim($distro_tmp[0]));
                    if (preg_match('/^(\S+)\s*/', $distro_tmp[0], $id_buf)
                        && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                }
            } else {
                if (isset($distro['Description'])
                   && preg_match('/^NAME=\s*"?([^"\n]+)"?\s*$/', $distro['Description'], $name_tmp)) {
                   $distro['Description'] = $name_tmp[1];
                }
                if (isset($distro['Description'])
                   && ($distro['Description'] != "n/a")
                   && (!isset($distro['Distributor ID'])
                   || (($distro['Distributor ID'] != "n/a")
                   && ($distro['Description'] != $distro['Distributor ID'])))) {
                    $this->sys->setDistribution($distro['Description']);
                    if (isset($distro['Release']) && ($distro['Release'] != "n/a")
                       && ($distro['Release'] != $distro['Description']) && strstr($distro['Release'], ".")){
                        if (preg_match("/^(\d+)\.[0]+$/", $distro['Release'], $match_buf)) {
                            $tofind = $match_buf[1];
                        } else {
                            $tofind = $distro['Release'];
                        }
                        if (!preg_match("/^".$tofind."[\s\.]|[\(\[]".$tofind."[\.\)\]]|\s".$tofind."$|\s".$tofind."[\s\.]/", $distro['Description'])) {
                            $this->sys->setDistribution($this->sys->getDistribution()." ".$distro['Release']);
                        }
                    }
                } elseif (isset($distro['Distributor ID']) && ($distro['Distributor ID'] != "n/a")) {
                    $this->sys->setDistribution($distro['Distributor ID']);
                    if (isset($distro['Release']) && ($distro['Release'] != "n/a")) {
                        $this->sys->setDistribution($this->sys->getDistribution()." ".$distro['Release']);
                    }
                    if (isset($distro['Codename']) && ($distro['Codename'] != "n/a")) {
                        $this->sys->setDistribution($this->sys->getDistribution()." (".$distro['Codename'].")");
                    }
                }
                if (isset($distro['Distributor ID']) && ($distro['Distributor ID'] != "n/a") && isset($list[$distro['Distributor ID']]['Image'])) {
                    $this->sys->setDistributionIcon($list[$distro['Distributor ID']]['Image']);
                } elseif (isset($distro['Description']) && ($distro['Description'] != "n/a")) {
                    $this->sys->setDistribution($distro['Description']);
                    if (isset($list[$distro['Description']]['Image'])) {
                        $this->sys->setDistributionIcon($list[$distro['Description']]['Image']);
                    }
                }
            }
        } else {
            /* default error handler */
            if (function_exists('errorHandlerPsi')) {
                restore_error_handler();
            }
            /* fatal errors only */
            $old_err_rep = error_reporting();
            error_reporting(E_ERROR);

            // Fall back in case 'lsb_release' does not exist but exist /etc/lsb-release
            if (CommonFunctions::fileexists($filename="/etc/lsb-release")
               && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
               && preg_match('/^DISTRIB_ID="?([^"\n]+)"?/m', $buf, $id_buf)) {
                if (preg_match('/^DISTRIB_DESCRIPTION="?([^"\n]+)"?/m', $buf, $desc_buf)
                   && (trim($desc_buf[1])!=trim($id_buf[1]))) {
                    $this->sys->setDistribution(trim($desc_buf[1]));
                    if (preg_match('/^DISTRIB_RELEASE="?([^"\n]+)"?/m', $buf, $vers_buf)
                       && (trim($vers_buf[1])!=trim($desc_buf[1])) && strstr($vers_buf[1], ".")){
                        if (preg_match("/^(\d+)\.[0]+$/", trim($vers_buf[1]), $match_buf)) {
                            $tofind = $match_buf[1];
                        } else {
                            $tofind = trim($vers_buf[1]);
                        }
                        if (!preg_match("/^".$tofind."[\s\.]|[\(\[]".$tofind."[\.\)\]]|\s".$tofind."$|\s".$tofind."[\s\.]/", trim($desc_buf[1]))) {
                            $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                        }
                    }
                } else {
                    if (isset($list[trim($id_buf[1])]['Name'])) {
                        $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                    } else {
                        $this->sys->setDistribution(trim($id_buf[1]));
                    }
                    if (preg_match('/^DISTRIB_RELEASE="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                    }
                    if (preg_match('/^DISTRIB_CODENAME="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution($this->sys->getDistribution()." (".trim($vers_buf[1]).")");
                    }
                }
                if (isset($list[trim($id_buf[1])]['Image'])) {
                    $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                }
            } else { // otherwise find files specific for distribution
                foreach ($list as $section=>$distribution) {
                    if (!isset($distribution['Files'])) {
                        continue;
                    } else {
                        foreach (preg_split("/;/", $distribution['Files'], -1, PREG_SPLIT_NO_EMPTY) as $filename) {
                            if (CommonFunctions::fileexists($filename)) {
                                $distro = $distribution;
                                if (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="detection")) {
                                    $buf = "";
                                } elseif (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="execute")) {
                                    if (!CommonFunctions::executeProgram($filename, '2>/dev/null', $buf, PSI_DEBUG)) {
                                        $buf = "";
                                    }
                                } else {
                                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                                        $buf = "";
                                    } elseif (isset($distribution['Mode'])&&(strtolower($distribution['Mode'])=="analyse")) {
                                        if (preg_match('/^(\S+)\s*/', preg_replace('/^Red\s+/', 'Red', $buf), $id_buf)
                                           && isset($list[trim($id_buf[1])]['Image'])) {
                                            $distro = $list[trim($id_buf[1])];
                                        }
                                    }
                                }
                                if (isset($distro['Image'])) {
                                    $this->sys->setDistributionIcon($distro['Image']);
                                }
                                if (isset($distribution['Name'])) {
                                    if (is_null($buf) || (trim($buf) == "")) {
                                        $this->sys->setDistribution($distribution['Name']);
                                    } else {
                                        $this->sys->setDistribution($distribution['Name']." ".trim($buf));
                                    }
                                } else {
                                    if (is_null($buf) || (trim($buf) == "")) {
                                        $this->sys->setDistribution($section);
                                    } else {
                                        $this->sys->setDistribution(trim($buf));
                                    }
                                }
                                if (isset($distribution['Files2'])) {
                                    foreach (preg_split("/;/", $distribution['Files2'], -1, PREG_SPLIT_NO_EMPTY) as $filename2) {
                                        if (CommonFunctions::fileexists($filename2) && CommonFunctions::rfts($filename2, $buf, 0, 4096, false)) {
                                            if (preg_match('/^majorversion="?([^"\n]+)"?/m', $buf, $maj_buf)
                                               && preg_match('/^minorversion="?([^"\n]+)"?/m', $buf, $min_buf)) {
                                                $distr2=$maj_buf[1].'.'.$min_buf[1];
                                                if (preg_match('/^buildphase="?([^"\n]+)"?/m', $buf, $pha_buf) && ($pha_buf[1]!=="0")) {
                                                    $distr2.='.'.$pha_buf[1];
                                                }
                                                if (preg_match('/^buildnumber="?([^"\n]+)"?/m', $buf, $num_buf)) {
                                                    $distr2.='-'.$num_buf[1];
                                                }
                                                if (preg_match('/^builddate="?([^"\n]+)"?/m', $buf, $dat_buf)) {
                                                    $distr2.=' ('.$dat_buf[1].')';
                                                }
                                                $this->sys->setDistribution($this->sys->getDistribution()." ".$distr2);
                                            } else {
                                                $distr2=trim(substr($buf, 0, strpos($buf, "\n")));
                                                if (!is_null($distr2) && ($distr2 != "")) {
                                                    $this->sys->setDistribution($this->sys->getDistribution()." ".$distr2);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }
                                break 2;
                            }
                        }
                    }
                }
            }
            // if the distribution is still unknown
            if ($this->sys->getDistribution() == "Linux") {
                if (CommonFunctions::fileexists($filename="/etc/DISTRO_SPECS")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRO_NAME=\'(.+)\'/m', $buf, $id_buf)) {
                    if (isset($list[trim($id_buf[1])]['Name'])) {
                        $dist = trim($list[trim($id_buf[1])]['Name']);
                    } else {
                        $dist = trim($id_buf[1]);
                    }
                    if (preg_match('/^DISTRO_VERSION=([^#\n\r]+)/m', $buf, $vers_buf)) {
                        $this->sys->setDistribution(trim($dist." ".trim($vers_buf[1])));
                    } else {
                        $this->sys->setDistribution($dist);
                    }
                    if (isset($list[trim($id_buf[1])]['Image'])) {
                        $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    } else {
                        if (isset($list['Puppy']['Image'])) {
                            $this->sys->setDistributionIcon($list['Puppy']['Image']);
                        }
                    }
                } elseif ((CommonFunctions::fileexists($filename="/etc/distro-release")
                        && CommonFunctions::rfts($filename, $buf, 1, 4096, false)
                        && !is_null($buf) && (trim($buf) != ""))
                    || (CommonFunctions::fileexists($filename="/etc/system-release")
                        && CommonFunctions::rfts($filename, $buf, 1, 4096, false)
                        && !is_null($buf) && (trim($buf) != ""))) {
                    $this->sys->setDistribution(trim($buf));
                    if (preg_match('/^(\S+)\s*/', preg_replace('/^Red\s+/', 'Red', $buf), $id_buf)
                        && isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    }
                } elseif (CommonFunctions::fileexists($filename="/etc/solydxk/info")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match('/^DISTRIB_ID="?([^"\n]+)"?/m', $buf, $id_buf)) {
                    if (preg_match('/^DESCRIPTION="?([^"\n]+)"?/m', $buf, $desc_buf)
                       && (trim($desc_buf[1])!=trim($id_buf[1]))) {
                        $this->sys->setDistribution(trim($desc_buf[1]));
                    } else {
                        if (isset($list[trim($id_buf[1])]['Name'])) {
                            $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                        } else {
                            $this->sys->setDistribution(trim($id_buf[1]));
                        }
                        if (preg_match('/^RELEASE="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                            $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                        }
                        if (preg_match('/^CODENAME="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                            $this->sys->setDistribution($this->sys->getDistribution()." (".trim($vers_buf[1]).")");
                        }
                    }
                    if (isset($list[trim($id_buf[1])]['Image'])) {
                        $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                    } else {
                        $this->sys->setDistributionIcon($list['SolydXK']['Image']);
                    }
                } elseif (CommonFunctions::fileexists($filename="/etc/os-release")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && (preg_match('/^TAILS_VERSION_ID="?([^"\n]+)"?/m', $buf, $tid_buf)
                   || preg_match('/^NAME="?([^"\n]+)"?/m', $buf, $id_buf))) {
                    if (preg_match('/^TAILS_VERSION_ID="?([^"\n]+)"?/m', $buf, $tid_buf)) {
                        if (preg_match('/^TAILS_PRODUCT_NAME="?([^"\n]+)"?/m', $buf, $desc_buf)) {
                            $this->sys->setDistribution(trim($desc_buf[1])." ".trim($tid_buf[1]));
                        } else {
                            if (isset($list['Tails']['Name'])) {
                                $this->sys->setDistribution(trim($list['Tails']['Name'])." ".trim($tid_buf[1]));
                            } else {
                                $this->sys->setDistribution('Tails'." ".trim($tid_buf[1]));
                            }
                        }
                        $this->sys->setDistributionIcon($list['Tails']['Image']);
                    } else {
                        if (preg_match('/^PRETTY_NAME="?([^"\n]+)"?/m', $buf, $desc_buf)
                           && !preg_match('/\$/', $desc_buf[1])) { //if is not defined by variable
                            $this->sys->setDistribution(trim($desc_buf[1]));
                        } else {
                            if (isset($list[trim($id_buf[1])]['Name'])) {
                                $this->sys->setDistribution(trim($list[trim($id_buf[1])]['Name']));
                            } else {
                                $this->sys->setDistribution(trim($id_buf[1]));
                            }
                            if (preg_match('/^VERSION="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                                $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                            } elseif (preg_match('/^VERSION_ID="?([^"\n]+)"?/m', $buf, $vers_buf)) {
                                $this->sys->setDistribution($this->sys->getDistribution()." ".trim($vers_buf[1]));
                            }
                        }
                        if (isset($list[trim($id_buf[1])]['Image'])) {
                            $this->sys->setDistributionIcon($list[trim($id_buf[1])]['Image']);
                        }
                    }
                } elseif (CommonFunctions::fileexists($filename="/etc/debian_version")) {
                    if (!CommonFunctions::rfts($filename, $buf, 1, 4096, false)) {
                        $buf = "";
                    }
                    if (isset($list['Debian']['Image'])) {
                        $this->sys->setDistributionIcon($list['Debian']['Image']);
                    }
                    if (isset($list['Debian']['Name'])) {
                        if (is_null($buf) || (trim($buf) == "")) {
                            $this->sys->setDistribution($list['Debian']['Name']);
                        } else {
                            $this->sys->setDistribution($list['Debian']['Name']." ".trim($buf));
                        }
                    } else {
                        if (is_null($buf) || (trim($buf) == "")) {
                            $this->sys->setDistribution('Debian');
                        } else {
                            $this->sys->setDistribution(trim($buf));
                        }
                    }
                } elseif (CommonFunctions::fileexists($filename="/etc/config/uLinux.conf")
                   && CommonFunctions::rfts($filename, $buf, 0, 4096, false)
                   && preg_match("/^Rsync\sModel\s*=\s*QNAP/m", $buf)
                   && preg_match("/^Version\s*=\s*([\d\.]+)\r?\nBuild\sNumber\s*=\s*(\S+)/m", $buf, $ver_buf)) {
                    $buf = $ver_buf[1]."-".$ver_buf[2];
                    if (isset($list['QTS']['Image'])) {
                        $this->sys->setDistributionIcon($list['QTS']['Image']);
                    }
                    if (isset($list['QTS']['Name'])) {
                        $this->sys->setDistribution($list['QTS']['Name']." ".trim($buf));
                    } else {
                        $this->sys->setDistribution(trim($buf));
                    }
                }
            }
            /* restore error level */
            error_reporting($old_err_rep);
            /* restore error handler */
            if (function_exists('errorHandlerPsi')) {
                set_error_handler('errorHandlerPsi');
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
        $process = glob('/proc/*/status', GLOB_NOSORT);
        if (is_array($process) && (($total = count($process)) > 0)) {
            $processes['*'] = 0;
            $buf = "";
            for ($i = 0; $i < $total; $i++) {
                if (CommonFunctions::rfts($process[$i], $buf, 0, 4096, false)) {
                    $processes['*']++; //current total
                    if (preg_match('/^State:\s+(\w)/m', $buf, $state)) {
                        if (isset($processes[$state[1]])) {
                            $processes[$state[1]]++;
                        } else {
                            $processes[$state[1]] = 1;
                        }
                    }
                }
            }
            if (!($processes['*'] > 0)) {
                $processes['*'] = $processes[' '] = $total; //all unknown
            }
            $this->sys->setProcesses($processes);
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
            $this->_machine();
            $this->_cpuinfo();
            $this->_pci();
            $this->_ide();
            $this->_scsi();
            $this->_nvme();
            $this->_usb();
            $this->_i2c();
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
