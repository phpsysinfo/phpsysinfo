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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class WINNT extends OS
{
    /**
     * holds the data from WMI Win32_OperatingSystem
     *
     * @var array
     */
    private $_Win32_OperatingSystem = null;

    /**
     * holds the data from WMI Win32_ComputerSystem
     *
     * @var array
     */
    private $_Win32_ComputerSystem = null;

    /**
     * holds the data from WMI Win32_Processor
     *
     * @var array
     */
    private $_Win32_Processor = null;

     /**
     * holds the data from WMI Win32_PerfFormattedData_PerfOS_Processor
     *
     * @var array
     */
    private $_Win32_PerfFormattedData_PerfOS_Processor = null;

    /**
     * holds the data from systeminfo command
     *
     * @var string
     */
    private $_systeminfo = null;

    /**
     * holds the COM object that we pull all the WMI data from
     *
     * @var Object
     */
    private $_wmi = null;

    /**
     * holds the COM object that we pull all the RegRead data from
     *
     * @var Object
     */
    private $_reg = null;

    /**
     * holds the COM object that we pull all the EnumKey data from
     *
     * @var Object
     */
    private $_key = null;

    /**
     * holds all devices, which are in the system
     *
     * @var array
     */
    private $_wmidevices;

    /**
     * holds all disks, which are in the system
     *
     * @var array
     */
    private $_wmidisks;

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
     * reads the data from WMI Win32_OperatingSystem
     *
     * @return array
     */
    private function _get_Win32_OperatingSystem()
    {
        if ($this->_Win32_OperatingSystem === null) $this->_Win32_OperatingSystem = CommonFunctions::getWMI($this->_wmi, 'Win32_OperatingSystem', array('CodeSet', 'Locale', 'LastBootUpTime', 'LocalDateTime', 'Version', 'ServicePackMajorVersion', 'Caption', 'OSArchitecture', 'TotalVisibleMemorySize', 'FreePhysicalMemory'));
        return $this->_Win32_OperatingSystem;
    }

    /**
     * reads the data from WMI Win32_ComputerSystem
     *
     * @return array
     */
    private function _get_Win32_ComputerSystem()
    {
        if ($this->_Win32_ComputerSystem === null) $this->_Win32_ComputerSystem = CommonFunctions::getWMI($this->_wmi, 'Win32_ComputerSystem', array('Name', 'Manufacturer', 'Model'));
        return $this->_Win32_ComputerSystem;
    }

    /**
     * reads the data from WMI Win32_Processor
     *
     * @return array
     */
    private function _get_Win32_Processor()
    {
        if ($this->_Win32_Processor === null) $this->_Win32_Processor = CommonFunctions::getWMI($this->_wmi, 'Win32_Processor', array('LoadPercentage', 'AddressWidth', 'Name', 'L2CacheSize', 'L3CacheSize', 'CurrentClockSpeed', 'ExtClock', 'NumberOfCores', 'NumberOfLogicalProcessors', 'MaxClockSpeed', 'Manufacturer'));
        return $this->_Win32_Processor;
    }

    /**
     * reads the data from WMI Win32_PerfFormattedData_PerfOS_Processor
     *
     * @return array
     */
    private function _get_Win32_PerfFormattedData_PerfOS_Processor()
    {
        if ($this->_Win32_PerfFormattedData_PerfOS_Processor === null) {
            $this->_Win32_PerfFormattedData_PerfOS_Processor = array();
            $buffer = $this->_get_Win32_OperatingSystem();
            if ($buffer && isset($buffer[0]) && isset($buffer[0]['Version']) && version_compare($buffer[0]['Version'], "5.1", ">=")) { // minimal windows 2003 or windows XP
                $cpubuffer = CommonFunctions::getWMI($this->_wmi, 'Win32_PerfFormattedData_PerfOS_Processor', array('Name', 'PercentProcessorTime'));
                if ($cpubuffer) foreach ($cpubuffer as $cpu) {
                    if (isset($cpu['Name']) && isset($cpu['PercentProcessorTime'])) {
                        $this->_Win32_PerfFormattedData_PerfOS_Processor['cpu'.$cpu['Name']] = $cpu['PercentProcessorTime'];
                    }
                }
            }
        }

        return $this->_Win32_PerfFormattedData_PerfOS_Processor;
    }

    /**
     * reads the data from systeminfo
     *
     * @return string
     */
    private function _get_systeminfo()
    {
        if ($this->_systeminfo === null) CommonFunctions::executeProgram('systeminfo', '', $this->_systeminfo, false);
        return $this->_systeminfo;
    }

    /**
     * build the global Error object and create the WMI connection
     */
    public function __construct($blockname = false)
    {
        parent::__construct($blockname);
        try {
            // initialize the wmi object
            $objLocator = new COM('WbemScripting.SWbemLocator');
            $this->_wmi = $objLocator->ConnectServer('', 'root\CIMv2');
        } catch (Exception $e) {
            $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
        }
        try {
            // initialize the RegRead object
            $this->_reg = new COM("WScript.Shell");
        } catch (Exception $e) {
            //$this->error->addError("Windows Scripting Host error", "PhpSysInfo can not initialize Windows Scripting Host for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
            $this->_reg = false;
        }
        try {
            // initialize the EnumKey object
            $this->_key = new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\default:StdRegProv");
        } catch (Exception $e) {
            //$this->error->addError("WWinmgmts Impersonationlevel Script Error", "PhpSysInfo can not initialize Winmgmts Impersonationlevel Script for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed.");
            $this->_key = false;
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
        $buffer = $this->_get_Win32_OperatingSystem();
        if (!$buffer) {
            if (CommonFunctions::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\CodePage\\ACP", $strBuf, false)) {
                $buffer[0]['CodeSet'] = $strBuf;
            }
            if (CommonFunctions::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\Language\\Default", $strBuf, false)) {
                $buffer[0]['Locale'] = $strBuf;
            }
        }
        if ($buffer && isset($buffer[0])) {
            if (isset($buffer[0]['CodeSet'])) {
                $codeset = $buffer[0]['CodeSet'];
                if ($codeset == 932) {
                    $codename = ' (SJIS)';
                } elseif ($codeset == 949) {
                    $codename = ' (EUC-KR)';
                } elseif ($codeset == 950) {
                    $codename = ' (BIG-5)';
                } else {
                    $codename = '';
                }
                CommonFunctions::setcp($codeset);
                $this->_codepage = 'windows-'.$codeset.$codename;
            }
            if (isset($buffer[0]['Locale']) && (($locale = hexdec($buffer[0]['Locale']))>0)) {
                $lang = "";
                if (is_readable(PSI_APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(PSI_APP_ROOT.'/data/languages.ini', true))) {
                    if (isset($langdata['WINNT'][$locale])) {
                        $lang = $langdata['WINNT'][$locale];
                    }
                }
                if ($lang == "") {
                    $lang = 'Unknown';
                }
                $this->_syslang = $lang.' ('.$locale.')';
            }
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
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $this->_wmidevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PnPEntity', array('Name', 'PNPDeviceID', 'Manufacturer', 'PNPClass'));
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                    $this->_wmidisks = CommonFunctions::getWMI($this->_wmi, 'Win32_DiskDrive', array('PNPDeviceID', 'Size', 'SerialNumber'));
                } else {
                    $this->_wmidisks = CommonFunctions::getWMI($this->_wmi, 'Win32_DiskDrive', array('PNPDeviceID', 'Size'));
                }
            } else {
                $this->_wmidevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PnPEntity', array('Name', 'PNPDeviceID'));
                $this->_wmidisks = array();
            }
        }
        $list = array();
        foreach ($this->_wmidevices as $device) {
            if (substr($device['PNPDeviceID'], 0, strpos($device['PNPDeviceID'], "\\") + 1) == ($strType."\\")) {
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    if (!isset($device['PNPClass']) || ($device['PNPClass']===$strType) || ($device['PNPClass']==='System')) {
                        $device['PNPClass'] = null;
                    }
                    if (preg_match('/^\(.*\)$/', $device['Manufacturer'])) {
                        $device['Manufacturer'] = null;
                    }
                    $device['Capacity'] = null;
                    if (($strType==='IDE')||($strType==='SCSI')) {
                        foreach ($this->_wmidisks as $disk) {
                            if (($disk['PNPDeviceID'] === $device['PNPDeviceID']) && isset($disk['Size'])) {
                                $device['Capacity'] = $disk['Size'];
                                break;
                            }
                        }
                    }
                    $device['Serial'] = null;
                    if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                        if ($strType==='USB') {
                            if (preg_match('/\\\\(\w+)$/', $device['PNPDeviceID'], $buf)) {
                                $device['Serial'] = $buf[1];
                            }
                        } elseif (($strType==='IDE')||($strType==='SCSI')) {
                            foreach ($this->_wmidisks as $disk) {
                                if (($disk['PNPDeviceID'] === $device['PNPDeviceID']) && isset($disk['SerialNumber'])) {
                                    $device['Serial'] = $disk['SerialNumber'];
                                    break;
                                }
                            }
                        }
                    }
                    $list[] = array('Name'=>$device['Name'], 'Manufacturer'=>$device['Manufacturer'], 'Product'=>$device['PNPClass'], 'Capacity'=>$device['Capacity'], 'Serial'=>$device['Serial']);
                } else {
                    $list[] = array('Name'=>$device['Name']);
                }
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
            if (CommonFunctions::readenv('SERVER_NAME', $hnm)) $this->sys->setHostname($hnm);
        } else {
            $buffer = $this->_get_Win32_ComputerSystem();
            if (!$buffer && CommonFunctions::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\ComputerName\\ActiveComputerName\\ComputerName", $strBuf, false) && (strlen($strBuf) > 0)) {
                    $buffer[0]['Name'] = $strBuf;
            }
            if ($buffer) {
                $result = $buffer[0]['Name'];
                $ip = gethostbyname($result);
                if ($ip != $result) {
                    if ((version_compare("10.0.0.0", $ip, "<=") && version_compare($ip, "10.255.255.255", "<=")) ||
                        (version_compare("172.16.0.0", $ip, "<=") && version_compare($ip, "172.31.255.255", "<=")) ||
                        (version_compare("192.168.0.0", $ip, "<=") && version_compare($ip, "192.168.255.255", "<=")) ||
                        (version_compare("127.0.0.0", $ip, "<=") && version_compare($ip, "127.255.255.255", "<=")) ||
                        (version_compare("169.254.1.0", $ip, "<=") && version_compare($ip, "169.254.254.255", "<=")) ||
                        (version_compare("255.255.255.255", $ip, "=="))) {
                        $this->sys->setHostname($result); // internal ip
                    } else {
                        $this->sys->setHostname(gethostbyaddr($ip));
                    }
                }
            } else {
                if (CommonFunctions::readenv('COMPUTERNAME', $hnm)) $this->sys->setHostname($hnm);
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
        $buffer = $this->_get_Win32_OperatingSystem();
        if ($buffer && ($buffer[0]['LastBootUpTime'] !== null)) {
            $local = $buffer[0]['LocalDateTime'];
            $boot = $buffer[0]['LastBootUpTime'];

            $lyear = intval(substr($local, 0, 4));
            $lmonth = intval(substr($local, 4, 2));
            $lday = intval(substr($local, 6, 2));
            $lhour = intval(substr($local, 8, 2));
            $lminute = intval(substr($local, 10, 2));
            $lseconds = intval(substr($local, 12, 2));
            $loffset = intval(substr($boot, 21, 4));

            $byear = intval(substr($boot, 0, 4));
            $bmonth = intval(substr($boot, 4, 2));
            $bday = intval(substr($boot, 6, 2));
            $bhour = intval(substr($boot, 8, 2));
            $bminute = intval(substr($boot, 10, 2));
            $bseconds = intval(substr($boot, 12, 2));
            $boffset = intval(substr($boot, 21, 4));

            if (version_compare($buffer[0]['Version'], "5.1", "<")) { // fix LastBootUpTime on Windows 2000 and older
                $boffset += $boffset;
            }

            $localtime = mktime($lhour, $lminute, $lseconds, $lmonth, $lday, $lyear) - $loffset*60;
            $boottime = mktime($bhour, $bminute, $bseconds, $bmonth, $bday, $byear) - $boffset*60;

            $result = $localtime - $boottime;

            $this->sys->setUptime($result);
        } elseif (($this->sys->getDistribution()=="ReactOS") && CommonFunctions::executeProgram('uptime', '', $strBuf, false) && (strlen($strBuf) > 0) && preg_match("/^System Up Time:\s+(\d+) days, (\d+) Hours, (\d+) Minutes, (\d+) Seconds/", $strBuf, $ar_buf)) {
            $sec = $ar_buf[4];
            $min = $ar_buf[3];
            $hours = $ar_buf[2];
            $days = $ar_buf[1];
            $this->sys->setUptime($days * 86400 + $hours * 3600 + $min * 60 + $sec);
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    protected function _users()
    {
        if (CommonFunctions::executeProgram('quser', '', $strBuf, false) && (strlen($strBuf) > 0)) {
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
        $buffer = $this->_get_Win32_OperatingSystem();
        if ($buffer) {
            $ver = $buffer[0]['Version'];
            $kernel = $ver;
            if ($buffer[0]['ServicePackMajorVersion'] > 0) {
                $kernel .= ' SP'.$buffer[0]['ServicePackMajorVersion'];
            }
            if (isset($buffer[0]['OSArchitecture']) && preg_match("/^(\d+)/", $buffer[0]['OSArchitecture'], $bits)) {
                $this->sys->setKernel($kernel.' ('.$bits[1].'-bit)');
            } elseif (($allCpus = $this->_get_Win32_Processor()) && isset($allCpus[0]['AddressWidth'])) {
                $this->sys->setKernel($kernel.' ('.$allCpus[0]['AddressWidth'].'-bit)');
            } else {
                $this->sys->setKernel($kernel);
            }
            $this->sys->setDistribution($buffer[0]['Caption']);

            if (version_compare($ver, "5.1", "<"))
                $icon = 'Win2000.png';
            elseif (version_compare($ver, "5.1", ">=") && version_compare($ver, "6.0", "<"))
                $icon = 'WinXP.png';
            elseif (version_compare($ver, "6.0", ">=") && version_compare($ver, "6.2", "<"))
                $icon = 'WinVista.png';
            else
                $icon = 'Win8.png';
            $this->sys->setDistributionIcon($icon);
        } elseif (CommonFunctions::executeProgram('cmd', '/c ver 2>nul', $ver_value, false)) {
                if (preg_match("/ReactOS\r?\nVersion\s+(.+)/", $ver_value, $ar_temp)) {
                    $this->sys->setDistribution("ReactOS");
                    $this->sys->setKernel($ar_temp[1]);
                    $this->sys->setDistributionIcon('ReactOS.png');
                    $this->_wmi = false; // No WMI info on ReactOS yet
                } elseif (preg_match("/^(Microsoft [^\[]*)\s*\[\D*\s*(.+)\]/", $ver_value, $ar_temp)) {
                    if (CommonFunctions::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\ProductName", $strBuf, false) && (strlen($strBuf) > 0)) {
                        if (preg_match("/^Microsoft /", $strBuf)) {
                            $this->sys->setDistribution($strBuf);
                        } else {
                            $this->sys->setDistribution("Microsoft ".$strBuf);
                        }
                    } else {
                        $this->sys->setDistribution($ar_temp[1]);
                    }
                    $kernel = $ar_temp[2];
                    $this->sys->setKernel($kernel);
                    if ((($kernel[1] == '.') && ($kernel[0] <5)) || (substr($kernel, 0, 4) == '5.0.'))
                        $icon = 'Win2000.png';
                    elseif ((substr($kernel, 0, 4) == '6.0.') || (substr($kernel, 0, 4) == '6.1.'))
                        $icon = 'WinVista.png';
                    elseif ((substr($kernel, 0, 4) == '6.2.') || (substr($kernel, 0, 4) == '6.3.') || (substr($kernel, 0, 4) == '6.4.') || (substr($kernel, 0, 5) == '10.0.'))
                        $icon = 'Win8.png';
                    else
                        $icon = 'WinXP.png';
                    $this->sys->setDistributionIcon($icon);
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
        if (($cpubuffer = $this->_get_Win32_PerfFormattedData_PerfOS_Processor()) && isset($cpubuffer['cpu_Total'])) {
            $this->sys->setLoad($cpubuffer['cpu_Total']);
            if (PSI_LOAD_BAR) {
                $this->sys->setLoadPercent($cpubuffer['cpu_Total']);
            }
        } elseif ($buffer = $this->_get_Win32_Processor()) {
            $loadok = true;
            $sum = 0;
            foreach ($buffer as $load) {
                $value = $load['LoadPercentage'];
                if ($value !== null) {
                    $sum += $value;
                } else {
                    $loadok = false;
                    break;
                }
            }
            if ($loadok) {
                $percent = $sum / count($buffer);
                $this->sys->setLoad($percent);
                if (PSI_LOAD_BAR) {
                    $this->sys->setLoadPercent($percent);
                }
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
        $allCpus = $this->_get_Win32_Processor();
        if (!$allCpus) {
            $hkey = "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\CentralProcessor";
            if (CommonFunctions::enumKey($this->_key, $hkey, $arrBuf, false)) {
                foreach ($arrBuf as $coreCount) {
                    if (CommonFunctions::readReg($this->_reg, $hkey."\\".$coreCount."\\ProcessorNameString", $strBuf, false)) {
                        $allCpus[$coreCount]['Name'] = $strBuf;
                    }
                    if (CommonFunctions::readReg($this->_reg, $hkey."\\".$coreCount."\\~MHz", $strBuf, false)) {
                        if (preg_match("/^0x([0-9a-f]+)$/i", $strBuf, $hexvalue)) {
                            $allCpus[$coreCount]['CurrentClockSpeed'] = hexdec($hexvalue[1]);
                        }
                    }
                    if (CommonFunctions::readReg($this->_reg, $hkey."\\".$coreCount."\\VendorIdentifier", $strBuf, false)) {
                        $allCpus[$coreCount]['Manufacturer'] = $strBuf;
                    }
                }
            }
        }

        $globalcpus = 0;
        foreach ($allCpus as $oneCpu) {
            $cpuCount = 1;
            if (isset($oneCpu['NumberOfLogicalProcessors'])) {
                $cpuCount = $oneCpu['NumberOfLogicalProcessors'];
            } elseif (isset($oneCpu['NumberOfCores'])) {
                $cpuCount = $oneCpu['NumberOfCores'];
            }
            $globalcpus+=$cpuCount;
        }

        foreach ($allCpus as $oneCpu) {
            $cpuCount = 1;
            if (isset($oneCpu['NumberOfLogicalProcessors'])) {
                $cpuCount = $oneCpu['NumberOfLogicalProcessors'];
            } elseif (isset($oneCpu['NumberOfCores'])) {
                $cpuCount = $oneCpu['NumberOfCores'];
            }
            for ($i = 0; $i < $cpuCount; $i++) {
                $cpu = new CpuDevice();
                if (isset($oneCpu['Name'])) $cpu->setModel($oneCpu['Name']);
                if (isset($oneCpu['L3CacheSize']) && ($oneCpu['L3CacheSize'] > 0)) {
                    $cpu->setCache($oneCpu['L3CacheSize'] * 1024);
                } elseif (isset($oneCpu['L2CacheSize'])) {
                    $cpu->setCache($oneCpu['L2CacheSize'] * 1024);
                }
                if (isset($oneCpu['CurrentClockSpeed'])) {
                    $cpu->setCpuSpeed($oneCpu['CurrentClockSpeed']);
                    if (isset($oneCpu['MaxClockSpeed']) && ($oneCpu['CurrentClockSpeed'] < $oneCpu['MaxClockSpeed'])) $cpu->setCpuSpeedMax($oneCpu['MaxClockSpeed']);
                }
                if (isset($oneCpu['ExtClock'])) $cpu->setBusSpeed($oneCpu['ExtClock']);
                if (isset($oneCpu['Manufacturer'])) $cpu->setVendorId($oneCpu['Manufacturer']);
                if (PSI_LOAD_BAR) {
                    if (($cpubuffer = $this->_get_Win32_PerfFormattedData_PerfOS_Processor()) && (count($cpubuffer) == ($globalcpus+1)) && isset($cpubuffer['cpu'.$i])) {
                           $cpu->setLoad($cpubuffer['cpu'.$i]);
                    } elseif ((count($allCpus) == $globalcpus) && isset($oneCpu['LoadPercentage'])) {
                        $cpu->setLoad($oneCpu['LoadPercentage']);
                    }
                }
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
        $buffer = $this->_get_Win32_ComputerSystem();
        if ($buffer) {
            $buf = "";
            if (isset($buffer[0]['Manufacturer']) && !preg_match("/^To be filled by O\.E\.M\.$|^System manufacturer$|^Not Specified$/i", $buf2=$buffer[0]['Manufacturer'])) {
                $buf .= ' '.$buf2;
            }

            if (isset($buffer[0]['Model']) && !preg_match("/^To be filled by O\.E\.M\.$|^System Product Name$|^Not Specified$/i", $buf2=$buffer[0]['Model'])) {
                $buf .= ' '.$buf2;
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
            $dev->setName($pciDev['Name']);
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $dev->setManufacturer($pciDev['Manufacturer']);
                $dev->setProduct($pciDev['Product']);
            }
            $this->sys->setPciDevices($dev);
        }

        foreach ($this->_devicelist('IDE') as $ideDev) {
            $dev = new HWDevice();
            $dev->setName($ideDev['Name']);
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $dev->setCapacity($ideDev['Capacity']);
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                    $dev->setSerial($ideDev['Serial']);
                }
            }
            $this->sys->setIdeDevices($dev);
        }

        foreach ($this->_devicelist('SCSI') as $scsiDev) {
            $dev = new HWDevice();
            $dev->setName($scsiDev['Name']);
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $dev->setCapacity($scsiDev['Capacity']);
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                    $dev->setSerial($scsiDev['Serial']);
                }
            }
            $this->sys->setScsiDevices($dev);
        }

        foreach ($this->_devicelist('USB') as $usbDev) {
            $dev = new HWDevice();
            $dev->setName($usbDev['Name']);
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $dev->setManufacturer($usbDev['Manufacturer']);
                $dev->setProduct($usbDev['Product']);
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                    $dev->setSerial($usbDev['Serial']);
                }
            }
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
        if ($this->_wmi) {
            $buffer = $this->_get_Win32_OperatingSystem();
            if ($buffer && isset($buffer[0]) && isset($buffer[0]['Version']) && version_compare($buffer[0]['Version'], "6.2", ">=")) { // minimal windows 2012 or windows 8
                $allDevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PerfRawData_Tcpip_NetworkAdapter', array('Name', 'BytesSentPersec', 'BytesTotalPersec', 'BytesReceivedPersec', 'PacketsReceivedErrors', 'PacketsReceivedDiscarded', 'CurrentBandwidth'));
            } else {
                $allDevices = CommonFunctions::getWMI($this->_wmi, 'Win32_PerfRawData_Tcpip_NetworkInterface', array('Name', 'BytesSentPersec', 'BytesTotalPersec', 'BytesReceivedPersec', 'PacketsReceivedErrors', 'PacketsReceivedDiscarded', 'CurrentBandwidth'));
            }
            if ($allDevices) {
                $aliases = array();
                $hkey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Network\\{4D36E972-E325-11CE-BFC1-08002BE10318}";
                if (CommonFunctions::enumKey($this->_key, $hkey, $arrBuf, false)) {
                    foreach ($arrBuf as $netID) {
                        if (CommonFunctions::readReg($this->_reg, $hkey."\\".$netID."\\Connection\\PnPInstanceId", $strInstanceID, false)) { //a w Name jest net alias
                            if (CommonFunctions::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Enum\\".$strInstanceID."\\FriendlyName", $strName, false)) {
                                $cname = str_replace(array('(', ')', '#'), array('[', ']', '_'), $strName); //convert to canonical
                                if (!isset($aliases[$cname])) { // duplicate checking
                                    $aliases[$cname]['id'] = $netID;
                                    $aliases[$cname]['name'] = $strName;
                                    if (CommonFunctions::readReg($this->_reg, $hkey."\\".$netID."\\Connection\\Name", $strCName, false)
                                       && (str_replace(array('(', ')', '#'), array('[', ']', '_'), $strCName) !== $cname)) {
                                        $aliases[$cname]['netname'] = $strCName;
                                    }
                                } else {
                                    $aliases[$cname]['id'] = '';
                                }
                            }
                        }
                    }
                }

                $aliases2 = array();
                $hkey = "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\NetworkCards";
                if (CommonFunctions::enumKey($this->_key, $hkey, $arrBuf, false)) {
                    foreach ($arrBuf as $netCount) {
                        if (CommonFunctions::readReg($this->_reg, $hkey."\\".$netCount."\\Description", $strName, false)
                            && CommonFunctions::readReg($this->_reg, $hkey."\\".$netCount."\\ServiceName", $strGUID, false)) {
                            $cname = str_replace(array('(', ')', '#'), array('[', ']', '_'), $strName); //convert to canonical
                            if (!isset($aliases2[$cname])) { // duplicate checking
                                $aliases2[$cname]['id'] = $strGUID;
                                $aliases2[$cname]['name'] = $strName;
                            } else {
                                $aliases2[$cname]['id'] = '';
                            }
                        }
                    }
                }

                $allNetworkAdapterConfigurations = CommonFunctions::getWMI($this->_wmi, 'Win32_NetworkAdapterConfiguration', array('SettingID', /*'Description',*/ 'MACAddress', 'IPAddress'));
                foreach ($allDevices as $device) if (!preg_match('/^WAN Miniport \[/', $device['Name'])) {
                    $dev = new NetDevice();
                    $name = $device['Name'];

                    if (preg_match('/^isatap\.({[A-Fa-f0-9\-]*})/', $name)) {
                        $dev->setName("Microsoft ISATAP Adapter");
                    } else {
                        if (preg_match('/\s-\s([^-]*)$/', $name, $ar_name)) {
                            $name=substr($name, 0, strlen($name)-strlen($ar_name[0]));
                        }
                        $dev->setName($name);
                    }

                    $macexist = false;
                    if (((($ali=$aliases) && isset($ali[$name])) || (($ali=$aliases2) && isset($ali[$name]))) && isset($ali[$name]['id']) && ($ali[$name]['id'] !== "")) {
                        foreach ($allNetworkAdapterConfigurations as $NetworkAdapterConfiguration) {
                            if ($ali[$name]['id']==$NetworkAdapterConfiguration['SettingID']) {
                                $mininame = $ali[$name]['name'];
                                if (preg_match('/^isatap\.({[A-Fa-f0-9\-]*})/', $mininame))
                                    $mininame="Microsoft ISATAP Adapter";
                                elseif (preg_match('/\s-\s([^-]*)$/', $mininame, $ar_name))
                                    $name=substr($mininame, 0, strlen($mininame)-strlen($ar_name[0]));
                                $dev->setName($mininame);
                                if (trim($NetworkAdapterConfiguration['MACAddress']) !== "") $macexist = true;
                                if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS) {
                                    if (isset($ali[$name]['netname'])) $dev->setInfo(str_replace(';', ':', $ali[$name]['netname']));
                                    if ((!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR)
                                       && (trim($NetworkAdapterConfiguration['MACAddress']) !== "")) $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').str_replace(':', '-', strtoupper($NetworkAdapterConfiguration['MACAddress'])));
                                    if (isset($NetworkAdapterConfiguration['IPAddress']))
                                        foreach ($NetworkAdapterConfiguration['IPAddress'] as $ipaddres)
                                            if (($ipaddres != "0.0.0.0") && ($ipaddres != "::") && !preg_match('/^fe80::/i', $ipaddres))
                                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ipaddres));
                                }

                                break;
                            }
                        }
                    }

                    if ($macexist
//                        || ($device['CurrentBandwidth'] >= 1000000)
                        || ($device['BytesTotalPersec'] != 0)
                        || ($device['BytesSentPersec'] != 0)
                        || ($device['BytesReceivedPersec'] != 0)
                        || ($device['PacketsReceivedErrors'] != 0)
                        || ($device['PacketsReceivedDiscarded'] != 0)) { // hide unused
                        if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS) {
                            if (($speedinfo = $device['CurrentBandwidth']) >= 1000000) {
                                if ($speedinfo > 1000000000) {
                                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').round($speedinfo/1000000000, 2)."Gb/s");
                                } else {
                                    $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').round($speedinfo/1000000, 2)."Mb/s");
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
            }
        } elseif (($buffer = $this->_get_systeminfo()) && preg_match('/^(\s+)\[\d+\]:[^\r\n]+\r\n\s+[^\s\[]/m', $buffer, $matches, PREG_OFFSET_CAPTURE)) {
            $netbuf = substr($buffer, $matches[0][1]);
            if (preg_match('/^[^\s]/m', $netbuf, $matches2, PREG_OFFSET_CAPTURE)) {
                $netbuf = substr($netbuf, 0, $matches2[0][1]);
            }
            $netstrs = preg_split('/^'.$matches[1][0].'\[\d+\]:/m', $netbuf, -1, PREG_SPLIT_NO_EMPTY);
            $devnr = 0;
            foreach ($netstrs as $netstr) {
                $netstrls = preg_split('/\r\n/', $netstr, -1, PREG_SPLIT_NO_EMPTY);
                if (sizeof($netstrls)>1) {
                    $dev = new NetDevice();
                    foreach ($netstrls as $nr=>$netstrl) {
                        if ($nr === 0) {
                            $name = trim($netstrl);
                            if ($name !== "") {
                                $dev->setName($name);
                            } else {
                                $dev->setName('dev'.$devnr);
                                $devnr++;
                            }
                        } elseif (preg_match('/\[\d+\]:\s+(.+)/', $netstrl, $netinfo)) {
                            $ipaddres = trim($netinfo[1]);
                            if (($ipaddres!="0.0.0.0") && !preg_match('/^fe80::/i', $ipaddres))
                                $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').strtolower($ipaddres));
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
     * @link http://msdn2.microsoft.com/En-US/library/aa394239.aspx
     * @link http://msdn2.microsoft.com/en-us/library/aa394246.aspx
     * @return void
     */
    private function _memory()
    {
        if ($this->_wmi) {
            $buffer = $this->_get_Win32_OperatingSystem();
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
        } elseif (($buffer = $this->_get_systeminfo()) && preg_match("/:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+\s([\d \xFF]+)\sMB\r\n/m", $buffer, $buffer2)) {
//           && (preg_match("/:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+:\s([\d \xFF]+)\sMB\r\n.+\s([\d \xFF]+)\sMB\r\n.*:\s+(\S+)\r\n/m", $buffer, $buffer2)) {
            $this->sys->setMemTotal(preg_replace('/(\s)|(\xFF)/', '', $buffer2[1]) * 1024 * 1024);
            $this->sys->setMemFree(preg_replace('/(\s)|(\xFF)/', '', $buffer2[2]) * 1024 * 1024);
            $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
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
            if (CommonFunctions::executeProgram('cmd', '/c free 2>nul', $out_value, true)) {
                for ($letter='A'; $letter!='AA'; $letter++) if (CommonFunctions::executeProgram('cmd', '/c free '.$letter.': 2>nul', $out_value, false)) {
                    $values = preg_replace('/[^\d\n]/', '', $out_value);
                    if (preg_match('/\n(\d+)\n(\d+)\n(\d+)$/', $values, $out_dig)) {
                        $size = $out_dig[1];
                        $used = $out_dig[2];
                        $free = $out_dig[3];
                        if ($used + $free == $size) {
                            $dev = new DiskDevice();
                            $dev->setMountPoint($letter.":");
                            $dev->setFsType('Unknown');
                            $dev->setName('Unknown');
                            $dev->setTotal($size);
                            $dev->setUsed($used);
                            $dev->setFree($free);
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
        if (CommonFunctions::executeProgram('qprocess', '*', $strBuf, false) && (strlen($strBuf) > 0)) {
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
        $this->_distro(); //share getDistribution()
        if ($this->sys->getDistribution()=="ReactOS") {
            $this->error->addError("WARN", "The ReactOS version of phpSysInfo is a work in progress, some things currently don't work");
        }
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_hostname();
            $this->_users();
            $this->_uptime();
            $this->_loadavg();
            $this->_processes();
        }
        if (!$this->blockname || $this->blockname==='network') {
            $this->_network();
        }
        if (!$this->blockname || $this->blockname==='hardware') {
            $this->_machine();
            $this->_cpuinfo();
            $this->_hardware();
        }
        if (!$this->blockname || $this->blockname==='filesystem') {
            $this->_filesystems();
        }
        if (!$this->blockname || $this->blockname==='memory') {
            $this->_memory();
        }
    }
}
