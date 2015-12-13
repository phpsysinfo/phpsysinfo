<?php
/**
 * WINNT System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI WINNT OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.WINNT.inc.php 699 2012-09-15 11:57:13Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * WINNT sysinfo class
 * get all the required information from WINNT systems
 * information are retrieved through the WMI interface
 *
 * @category  PHP
 * @package   PSI WINNT OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class WINNT extends OS
{
    /**
     * holds the COM object that we pull all the WMI data from
     *
     * @var Object
     */
    private $_wmi = null;

    /**
     * holds all devices, which are in the system
     *
     * @var array
     */
    private $_wmidevices;

    /**
     * store language encoding of the system to convert some output to utf-8
     *
     * @var string
     */
    private $_codepage = null;

    /**
     * store language of the system
     *
     * @var string
     */
    private $_syslang = null;

    /**
     * build the global Error object and create the WMI connection
     */
    public function __construct()
    {
        parent::__construct();
        // don't set this params for local connection, it will not work
        $strHostname = '';
        $strUser = '';
        $strPassword = '';
        try {
            // initialize the wmi object
            $objLocator = new COM('WbemScripting.SWbemLocator');
            if ($strHostname == "") {
                $this->_wmi = $objLocator->ConnectServer();

            } else {
                $this->_wmi = $objLocator->ConnectServer($strHostname, 'root\CIMv2', $strHostname.'\\'.$strUser, $strPassword);
            }
        } catch (Exception $e) {
            $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
        }
        $this->_getCodeSet();
    }

    /**
     * store the codepage of the os for converting some strings to utf-8
     *
     * @return void
     */
    private function _getCodeSet()
    {
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_OperatingSystem', array('CodeSet', 'OSLanguage'));
        if ($buffer) {
            $this->_codepage = 'windows-'.$buffer[0]['CodeSet'];
            $lang = "";
            if (is_readable(APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(APP_ROOT.'/data/languages.ini', true))) {
                if (isset($langdata['WINNT'][$buffer[0]['OSLanguage']])) {
                    $lang = $langdata['WINNT'][$buffer[0]['OSLanguage']];
                }
            }
            if ($lang == "") {
                $lang = 'Unknown';
            }
            $this->_syslang = $lang.' ('.$buffer[0]['OSLanguage'].')';
        }
    }

    /**
     * retrieve different device types from the system based on selector
     *
     * @param string $strType type of the devices that should be returned
     *
     * @return array list of devices of the specified type
     */
    private function _devicelist($strType)
    {
        if (empty($this->_wmidevices)) {
            $this->_wmidevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PnPEntity', array('Name', 'PNPDeviceID'));
        }
        $list = array();
        foreach ($this->_wmidevices as $device) {
            if (substr($device['PNPDeviceID'], 0, strpos($device['PNPDeviceID'], "\\") + 1) == ($strType."\\")) {
                $list[] = $device['Name'];
            }
        }

        return $list;
    }

    /**
     * Host Name
     *
     * @return void
     */
    private function _hostname()
    {
        if (PSI_USE_VHOST === true) {
            if ($hnm = getenv('SERVER_NAME')) $this->sys->setHostname($hnm);
        } else {
            $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_ComputerSystem', array('Name'));
            if ($buffer) {
                $result = $buffer[0]['Name'];
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    $long = ip2long($ip);
                    if (($long >= 167772160 && $long <= 184549375) ||
                        ($long >= -1408237568 && $long <= -1407188993) ||
                        ($long >= -1062731776 && $long <= -1062666241) ||
                        ($long >= 2130706432 && $long <= 2147483647) || $long == -1) {
                        $this->sys->setHostname($result); //internal ip
                    } else {
                        $this->sys->setHostname(gethostbyaddr($ip));
                    }
                }
            } else {
                if ($hnm = getenv('COMPUTERNAME')) $this->sys->setHostname($hnm);
            }
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
        $result = 0;
        date_default_timezone_set('UTC');
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_OperatingSystem', array('LastBootUpTime', 'LocalDateTime'));
        if ($buffer) {
            $byear = intval(substr($buffer[0]['LastBootUpTime'], 0, 4));
            $bmonth = intval(substr($buffer[0]['LastBootUpTime'], 4, 2));
            $bday = intval(substr($buffer[0]['LastBootUpTime'], 6, 2));
            $bhour = intval(substr($buffer[0]['LastBootUpTime'], 8, 2));
            $bminute = intval(substr($buffer[0]['LastBootUpTime'], 10, 2));
            $bseconds = intval(substr($buffer[0]['LastBootUpTime'], 12, 2));
            $lyear = intval(substr($buffer[0]['LocalDateTime'], 0, 4));
            $lmonth = intval(substr($buffer[0]['LocalDateTime'], 4, 2));
            $lday = intval(substr($buffer[0]['LocalDateTime'], 6, 2));
            $lhour = intval(substr($buffer[0]['LocalDateTime'], 8, 2));
            $lminute = intval(substr($buffer[0]['LocalDateTime'], 10, 2));
            $lseconds = intval(substr($buffer[0]['LocalDateTime'], 12, 2));
            $boottime = mktime($bhour, $bminute, $bseconds, $bmonth, $bday, $byear);
            $localtime = mktime($lhour, $lminute, $lseconds, $lmonth, $lday, $lyear);
            $result = $localtime - $boottime;
            $this->sys->setUptime($result);
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    private function _users()
    {
        if (CommonFunctions::executeProgram("quser", "", $strBuf, false) && (strlen(trim($strBuf)) > 0)) {
                $lines = preg_split('/\n/', $strBuf);
                $users = count($lines)-1;
        } else {
            $users = 0;
            $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_Process', array('Caption'));
            foreach ($buffer as $process) {
                if (strtoupper($process['Caption']) == strtoupper('explorer.exe')) {
                    $users++;
                }
            }
        }
        $this->sys->setUsers($users);
    }

    /**
     * Distribution
     *
     * @return void
     */
    private function _distro()
    {
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_OperatingSystem', array('Version', 'ServicePackMajorVersion', 'Caption', 'OSArchitecture'));
        if ($buffer) {
            $kernel = $buffer[0]['Version'];
            if ($buffer[0]['ServicePackMajorVersion'] > 0) {
                $kernel .= ' SP'.$buffer[0]['ServicePackMajorVersion'];
            }
            if (isset($buffer[0]['OSArchitecture']) && preg_match("/^(\d+)/", $buffer[0]['OSArchitecture'], $bits)) {
                $this->sys->setKernel($kernel.' ('.$bits[1].'-bit)');
            } elseif (($allCpus = CommonFunctions::getWMI($this->_wmi, 'Win32_Processor', array('AddressWidth'))) && isset($allCpus[0]['AddressWidth'])) {
                $this->sys->setKernel($kernel.' ('.$allCpus[0]['AddressWidth'].'-bit)');
            } else {
                $this->sys->setKernel($kernel);
            }
            $this->sys->setDistribution($buffer[0]['Caption']);

            if ((($kernel[1] == ".") && ($kernel[0] <5)) || (substr($kernel, 0, 4) == "5.0."))
                $icon = 'Win2000.png';
            elseif ((substr($kernel, 0, 4) == "6.0.") || (substr($kernel, 0, 4) == "6.1."))
                $icon = 'WinVista.png';
            elseif ((substr($kernel, 0, 4) == "6.2.") || (substr($kernel, 0, 4) == "6.3.") || (substr($kernel, 0, 4) == "6.4.") || (substr($kernel, 0, 5) == "10.0."))
                $icon = 'Win8.png';
            else
                $icon = 'WinXP.png';
            $this->sys->setDistributionIcon($icon);
        } elseif (CommonFunctions::executeProgram("cmd", "/c ver 2>nul", $ver_value, false)) {
                if (preg_match("/ReactOS\r?\nVersion\s+(.+)/", $ver_value, $ar_temp)) {
                    $this->sys->setDistribution("ReactOS");
                    $this->sys->setKernel($ar_temp[1]);
                    $this->sys->setDistributionIcon('ReactOS.png');
                } elseif (preg_match("/^(Microsoft [^\[]*)\s*\[\D*\s*(.+)\]/", $ver_value, $ar_temp)) {
                    $this->sys->setDistribution($ar_temp[1]);
                    $this->sys->setKernel($ar_temp[2]);
                    $this->sys->setDistributionIcon('Win2000.png');
                } else {
                    $this->sys->setDistribution("WinNT");
                    $this->sys->setDistributionIcon('Win2000.png');
                }
        } else {
            $this->sys->setDistribution("WinNT");
            $this->sys->setDistributionIcon('Win2000.png');
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
        $loadavg = "";
        $sum = 0;
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_Processor', array('LoadPercentage'));
        if ($buffer) {
            foreach ($buffer as $load) {
                $value = $load['LoadPercentage'];
                $loadavg .= $value.' ';
                $sum += $value;
            }
            $this->sys->setLoad(trim($loadavg));
            if (PSI_LOAD_BAR) {
                $this->sys->setLoadPercent($sum / count($buffer));
            }
        }
    }

    /**
     * CPU information
     *
     * @return void
     */
    private function _cpuinfo()
    {
        $allCpus = CommonFunctions::getWMI($this->_wmi, 'Win32_Processor', array('Name', 'L2CacheSize', 'CurrentClockSpeed', 'ExtClock', 'NumberOfCores', 'MaxClockSpeed'));
        foreach ($allCpus as $oneCpu) {
            $coreCount = 1;
            if (isset($oneCpu['NumberOfCores'])) {
                $coreCount = $oneCpu['NumberOfCores'];
            }
            for ($i = 0; $i < $coreCount; $i++) {
                $cpu = new CpuDevice();
                $cpu->setModel($oneCpu['Name']);
                $cpu->setCache($oneCpu['L2CacheSize'] * 1024);
                $cpu->setCpuSpeed($oneCpu['CurrentClockSpeed']);
                $cpu->setBusSpeed($oneCpu['ExtClock']);
                if ($oneCpu['CurrentClockSpeed'] < $oneCpu['MaxClockSpeed']) $cpu->setCpuSpeedMax($oneCpu['MaxClockSpeed']);
                $this->sys->setCpus($cpu);
            }
        }
    }

    /**
     * Machine information
     *
     * @return void
     */
    private function _machine()
    {
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_ComputerSystem', array('Manufacturer', 'Model'));
        if ($buffer) {
            $buf = "";
            if (isset($buffer[0]['Manufacturer'])) {
                $buf .= ' '.$buffer[0]['Manufacturer'];
            }
            if (isset($buffer[0]['Model'])) {
                $buf .= ' '.$buffer[0]['Model'];
            }
            if (trim($buf) != "") {
                $this->sys->setMachine(trim($buf));
            }
        }
    }

    /**
     * Hardwaredevices
     *
     * @return void
     */
    private function _hardware()
    {
        foreach ($this->_devicelist('PCI') as $pciDev) {
            $dev = new HWDevice();
            $dev->setName($pciDev);
            $this->sys->setPciDevices($dev);
        }

        foreach ($this->_devicelist('IDE') as $ideDev) {
            $dev = new HWDevice();
            $dev->setName($ideDev);
            $this->sys->setIdeDevices($dev);
        }

        foreach ($this->_devicelist('SCSI') as $scsiDev) {
            $dev = new HWDevice();
            $dev->setName($scsiDev);
            $this->sys->setScsiDevices($dev);
        }

        foreach ($this->_devicelist('USB') as $usbDev) {
            $dev = new HWDevice();
            $dev->setName($usbDev);
            $this->sys->setUsbDevices($dev);
        }
    }

    /**
     * Network devices
     *
     * @return void
     */
    private function _network()
    {
        $allDevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PerfRawData_Tcpip_NetworkInterface', array('Name', 'BytesSentPersec', 'BytesTotalPersec', 'BytesReceivedPersec', 'PacketsReceivedErrors', 'PacketsReceivedDiscarded'));
        $allNetworkAdapterConfigurations = CommonFunctions::getWMI($this->_wmi, 'Win32_NetworkAdapterConfiguration', array('Description', 'MACAddress', 'IPAddress', 'SettingID'));

        foreach ($allDevices as $device) {
            $dev = new NetDevice();
            $name=$device['Name'];

            if (preg_match('/^isatap\.({[A-Fa-f0-9\-]*})/', $name, $ar_name)) { //isatap device
                foreach ($allNetworkAdapterConfigurations as $NetworkAdapterConfiguration) {
                    if ($ar_name[1]==$NetworkAdapterConfiguration['SettingID']) {
                        $dev->setName($NetworkAdapterConfiguration['Description']);
                        if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS) {
                            $dev->setInfo(preg_replace('/:/', '-', $NetworkAdapterConfiguration['MACAddress']));
                            if (isset($NetworkAdapterConfiguration['IPAddress']))
                                foreach($NetworkAdapterConfiguration['IPAddress'] as $ipaddres)
                                    if (($ipaddres!="0.0.0.0") && ($ipaddres!="::") && !preg_match('/^fe80::/i', $ipaddres))
                                        $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ipaddres);
                        }

                        break;
                     }
                }
            }
            if ($dev->getName() == "") { //no isatap or no isatap description
                $cname=preg_replace('/[^A-Za-z0-9]/', '_', $name); //convert to canonical
                if (preg_match('/\s-\s([^-]*)$/', $name, $ar_name))
                    $name=substr($name, 0, strlen($name)-strlen($ar_name[0]));
                $dev->setName($name);

                if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS) foreach ($allNetworkAdapterConfigurations as $NetworkAdapterConfiguration) {
                    if (preg_replace('/[^A-Za-z0-9]/', '_', $NetworkAdapterConfiguration['Description']) == $cname) {
                        if (!is_null($dev->getInfo())) {
                            $dev->setInfo(''); //multiple with the same name
                        } else {
                            $dev->setInfo(preg_replace('/:/', '-', $NetworkAdapterConfiguration['MACAddress']));
                            if (isset($NetworkAdapterConfiguration['IPAddress']))
                                foreach($NetworkAdapterConfiguration['IPAddress'] as $ipaddres)
                                    if (($ipaddres!="0.0.0.0") && !preg_match('/^fe80::/i', $ipaddres))
                                        $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').$ipaddres);
                        }
                    }
                }
            }

            // http://msdn.microsoft.com/library/default.asp?url=/library/en-us/wmisdk/wmi/win32_perfrawdata_tcpip_networkinterface.asp
            // there is a possible bug in the wmi interfaceabout uint32 and uint64: http://www.ureader.com/message/1244948.aspx, so that
            // magative numbers would occour, try to calculate the nagative value from total - positive number
            $txbytes = $device['BytesSentPersec'];
            $rxbytes = $device['BytesReceivedPersec'];
            if (($txbytes < 0) && ($rxbytes < 0)) {
                $txbytes += 4294967296;
                $rxbytes += 4294967296;
            } elseif ($txbytes < 0) {
                if ($device['BytesTotalPersec'] > $rxbytes)
                   $txbytes = $device['BytesTotalPersec'] - $rxbytes;
                else
                   $txbytes += 4294967296;
            } elseif ($rxbytes < 0) {
                if ($device['BytesTotalPersec'] > $txbytes)
                   $rxbytes = $device['BytesTotalPersec'] - $txbytes;
                else
                   $rxbytes += 4294967296;
            }
            $dev->setTxBytes($txbytes);
            $dev->setRxBytes($rxbytes);
            $dev->setErrors($device['PacketsReceivedErrors']);
            $dev->setDrops($device['PacketsReceivedDiscarded']);

            $this->sys->setNetDevices($dev);
        }
    }

    /**
     * Physical memory information and Swap Space information
     *
     * @link http://msdn2.microsoft.com/En-US/library/aa394239.aspx
     * @link http://msdn2.microsoft.com/en-us/library/aa394246.aspx
     * @return void
     */
    private function _memory()
    {
        $buffer = CommonFunctions::getWMI($this->_wmi, "Win32_OperatingSystem", array('TotalVisibleMemorySize', 'FreePhysicalMemory'));
        if ($buffer) {
            $this->sys->setMemTotal($buffer[0]['TotalVisibleMemorySize'] * 1024);
            $this->sys->setMemFree($buffer[0]['FreePhysicalMemory'] * 1024);
            $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
        }
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_PageFileUsage');
        foreach ($buffer as $swapdevice) {
            $dev = new DiskDevice();
            $dev->setName("SWAP");
            $dev->setMountPoint($swapdevice['Name']);
            $dev->setTotal($swapdevice['AllocatedBaseSize'] * 1024 * 1024);
            $dev->setUsed($swapdevice['CurrentUsage'] * 1024 * 1024);
            $dev->setFree($dev->getTotal() - $dev->getUsed());
            $dev->setFsType('swap');
            $this->sys->setSwapDevices($dev);
        }
    }

    /**
     * filesystem information
     *
     * @return void
     */
    private function _filesystems()
    {
        $typearray = array('Unknown', 'No Root Directory', 'Removable Disk', 'Local Disk', 'Network Drive', 'Compact Disc', 'RAM Disk');
        $floppyarray = array('Unknown', '5 1/4 in.', '3 1/2 in.', '3 1/2 in.', '3 1/2 in.', '3 1/2 in.', '5 1/4 in.', '5 1/4 in.', '5 1/4 in.', '5 1/4 in.', '5 1/4 in.', 'Other', 'HD', '3 1/2 in.', '3 1/2 in.', '5 1/4 in.', '5 1/4 in.', '3 1/2 in.', '3 1/2 in.', '5 1/4 in.', '3 1/2 in.', '3 1/2 in.', '8 in.');
        $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_LogicalDisk', array('Name', 'Size', 'FreeSpace', 'FileSystem', 'DriveType', 'MediaType'));
        foreach ($buffer as $filesystem) {
            $dev = new DiskDevice();
            $dev->setMountPoint($filesystem['Name']);
            $dev->setFsType($filesystem['FileSystem']);
            if ($filesystem['Size'] > 0) {
                $dev->setTotal($filesystem['Size']);
                $dev->setFree($filesystem['FreeSpace']);
                $dev->setUsed($filesystem['Size'] - $filesystem['FreeSpace']);
            }
            if ($filesystem['MediaType'] != "" && $filesystem['DriveType'] == 2) {
                $dev->setName($typearray[$filesystem['DriveType']]." (".$floppyarray[$filesystem['MediaType']].")");
            } else {
                $dev->setName($typearray[$filesystem['DriveType']]);
            }
            $this->sys->setDiskDevices($dev);
        }
        if (!$buffer && ($this->sys->getDistribution()=="ReactOS")) {
            // test for command 'free' on current disk
            if (CommonFunctions::executeProgram("cmd", "/c free 2>nul", $out_value, true)) {
                for ($letter='A'; $letter!='AA'; $letter++) if (CommonFunctions::executeProgram("cmd", "/c free ".$letter.": 2>nul", $out_value, false)) {
                    if (preg_match('/\n\s*([\d\.\,]+).*\n\s*([\d\.\,]+).*\n\s*([\d\.\,]+).*$/', $out_value, $out_dig)) {
                        $size = preg_replace('/(\.)|(\,)/', '', $out_dig[1]);
                        $used = preg_replace('/(\.)|(\,)/', '', $out_dig[2]);
                        $free = preg_replace('/(\.)|(\,)/', '', $out_dig[3]);
                        if ($used + $free == $size) {
                            $dev = new DiskDevice();
                            $dev->setMountPoint($letter.":");
                            $dev->setFsType('Unknown');
                            $dev->setTotal($size);
                            $dev->setFree($free);
                            $dev->setUsed($used);
                            $this->sys->setDiskDevices($dev);
                        }
                    }
                }
            }
        }
    }

    /**
     * get os specific encoding
     *
     * @see OS::getEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_codepage;
    }

    /**
     * get os specific language
     *
     * @see OS::getLanguage()
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->_syslang;
    }

    public function _processes()
    {
        $processes['*'] = 0;
        if (CommonFunctions::executeProgram("qprocess", "*", $strBuf, false) && (strlen(trim($strBuf)) > 0)) {
            $lines = preg_split('/\n/', $strBuf);
            $processes['*'] = (count($lines)-1) - 3 ; //correction for process "qprocess *"
        }
        if ($processes['*'] <= 0) {
            $buffer = CommonFunctions::getWMI($this->_wmi, 'Win32_Process', array('Caption'));
            $processes['*'] = count($buffer);
        }
        $processes[' '] = $processes['*'];
        $this->sys->setProcesses($processes);
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
        $this->_distro();
        if ($this->sys->getDistribution()=="ReactOS") {
            $this->error->addError("WARN", "The ReactOS version of phpSysInfo is a work in progress, some things currently don't work");
        }
        $this->_hostname();
        $this->_users();
        $this->_machine();
        $this->_uptime();
        $this->_cpuinfo();
        $this->_network();
        $this->_hardware();
        $this->_filesystems();
        $this->_memory();
        $this->_loadavg();
        $this->_processes();
    }
}
