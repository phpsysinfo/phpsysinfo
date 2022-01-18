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
     * holds codepage for chcp
     *
     * @var int
     */
    private static $_cp = null;

    /**
     * holds the data from WMI Win32_OperatingSystem
     *
     * @var array
     */
    private static $_Win32_OperatingSystem = null;

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
    private static $_Win32_Processor = null;

    /**
     * holds the data from WMI Win32_PhysicalMemory
     *
     * @var array
     */
    private static $_Win32_PhysicalMemory = null;

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
     * holds the COM object that we pull WMI root\CIMv2 data from
     *
     * @var Object
     */
    private static $_wmi = null;

    /**
     * holds the COM object that we pull all the EnumKey and RegRead data from
     *
     * @var Object
     */
    private $_reg = null;

    /**
     * holds result of 'cmd /c ver'
     *
     * @var string
     */
    private $_ver = "";

    /**
     * holds system manufacturer
     *
     * @var string
     */
    private $_Manufacturer = "";

    /**
     * holds system model
     *
     * @var string
     */
    private $_Model = "";

    /**
     * holds all devices, which are in the system
     *
     * @var array
     */
    private $_wmidevices = null;

    /**
     * holds all disks, which are in the system
     *
     * @var array
     */
    private $_wmidisks = array();

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
     * value of checking run as administrator
     *
     * @var boolean
     */
    private static $_asadmin = null;

    /**
     * returns codepage for chcp
     *
     * @return int
     */
    public static function getcp()
    {
        return self::$_cp;
    }

    /**
     * returns the COM object that we pull WMI root\CIMv2 data from
     *
     * @return Object
     */
    public static function getcimv2wmi()
    {
        return self::$_wmi;
    }

    /**
     * reads the data from WMI Win32_OperatingSystem
     *
     * @return array
     */
    public static function _get_Win32_OperatingSystem()
    {
        if (self::$_Win32_OperatingSystem === null) self::$_Win32_OperatingSystem = self::getWMI(self::$_wmi, 'Win32_OperatingSystem', array('CodeSet', 'Locale', 'LastBootUpTime', 'LocalDateTime', 'Version', 'ServicePackMajorVersion', 'Caption', 'TotalVisibleMemorySize', 'FreePhysicalMemory'));
        return self::$_Win32_OperatingSystem;
    }

    /**
     * reads the data from WMI Win32_ComputerSystem
     *
     * @return array
     */
    private function _get_Win32_ComputerSystem()
    {
        if ($this->_Win32_ComputerSystem === null) $this->_Win32_ComputerSystem = self::getWMI(self::$_wmi, 'Win32_ComputerSystem', array('Name', 'Manufacturer', 'Model', 'SystemFamily'));
        return $this->_Win32_ComputerSystem;
    }

    /**
     * reads the data from WMI Win32_Processor
     *
     * @return array
     */
    public static function _get_Win32_Processor()
    {
        if (self::$_Win32_Processor === null) self::$_Win32_Processor = self::getWMI(self::$_wmi, 'Win32_Processor', array('DeviceID', 'LoadPercentage', 'AddressWidth', 'Name', 'L2CacheSize', 'L3CacheSize', 'CurrentClockSpeed', 'ExtClock', 'NumberOfCores', 'NumberOfLogicalProcessors', 'MaxClockSpeed', 'Manufacturer', 'Architecture', 'Caption', 'CurrentVoltage'));
        return self::$_Win32_Processor;
    }

    /**
     * reads the data from WMI Win32_PhysicalMemory
     *
     * @return array
     */
    public static function _get_Win32_PhysicalMemory()
    {
        if (self::$_Win32_PhysicalMemory === null) self::$_Win32_PhysicalMemory = self::getWMI(self::$_wmi, 'Win32_PhysicalMemory', array('PartNumber', 'DeviceLocator', 'Capacity', 'Manufacturer', 'SerialNumber', 'Speed', 'ConfiguredClockSpeed', 'ConfiguredVoltage', 'MemoryType', 'SMBIOSMemoryType', 'FormFactor', 'DataWidth', 'TotalWidth', 'BankLabel', 'MinVoltage', 'MaxVoltage'));
        return self::$_Win32_PhysicalMemory;
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
                $cpubuffer = self::getWMI(self::$_wmi, 'Win32_PerfFormattedData_PerfOS_Processor', array('Name', 'PercentProcessorTime'));
                foreach ($cpubuffer as $cpu) {
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
        if (!defined('PSI_EMU_HOSTNAME')) {
            if ($this->_systeminfo === null) CommonFunctions::executeProgram('systeminfo', '', $this->_systeminfo, false);
            return $this->_systeminfo;
        } else {
            return '';
        }
    }

    /**
     * checks WINNT and 'run as Administrator' mode
     *
     * @return boolean
     */
    public static function isAdmin()
    {
        if (self::$_asadmin == null) {
            if (PSI_OS == 'WINNT') {
                $strBuf = '';
                CommonFunctions::executeProgram('sfc', '2>&1', $strBuf, false); // 'net session' for detection does not work if "Server" (LanmanServer) service is stopped
                if (preg_match('/^\/SCANNOW\s/m', preg_replace('/(\x00)/', '', $strBuf))) { // SCANNOW checking - also if Unicode
                    self::$_asadmin = true;
                } else {
                    self::$_asadmin = false;
                }
            } else {
                self::$_asadmin = false;
            }
        }

        return self::$_asadmin;
    }

    /**
     * function for getting a list of values in the specified context
     * optionally filter this list, based on the list from third parameter
     *
     * @param $wmi object holds the COM object that we pull the WMI data from
     * @param string $strClass name of the class where the values are stored
     * @param array  $strValue filter out only needed values, if not set all values of the class are returned
     *
     * @return array content of the class stored in an array
     */
    public static function getWMI($wmi, $strClass, $strValue = array())
    {
        $arrData = array();
        if (gettype($wmi) === "object") {
            $value = "";
            try {
                $objWEBM = $wmi->Get($strClass);
                $arrProp = $objWEBM->Properties_;
                $arrWEBMCol = $objWEBM->Instances_();
                foreach ($arrWEBMCol as $objItem) {
                    if (is_array($arrProp)) {
                        reset($arrProp);
                    }
                    $arrInstance = array();
                    foreach ($arrProp as $propItem) {
                        $value = $objItem->{$propItem->Name}; //instead exploitable eval("\$value = \$objItem->".$propItem->Name.";");
                        if (empty($strValue)) {
                            if (is_string($value)) $arrInstance[$propItem->Name] = trim($value);
                            else $arrInstance[$propItem->Name] = $value;
                        } else {
                            if (in_array($propItem->Name, $strValue)) {
                                if (is_string($value)) $arrInstance[$propItem->Name] = trim($value);
                                else $arrInstance[$propItem->Name] = $value;
                            }
                        }
                    }
                    $arrData[] = $arrInstance;
                }
            } catch (Exception $e) {
                if (PSI_DEBUG && (($message = trim($e->getMessage())) !== "<b>Source:</b> SWbemServicesEx<br/><b>Description:</b> Not found")) {
                    $error = PSI_Error::singleton();
                    $error->addError("getWMI()", preg_replace('/<br\/>/', "\n", preg_replace('/<b>|<\/b>/', '', $message)));
                }
            }
        } elseif ((gettype($wmi) === "string") && (PSI_OS == 'Linux')) {
            $delimeter = '@@@DELIM@@@';
            if (CommonFunctions::executeProgram('wmic', '--delimiter="'.$delimeter.'" '.$wmi.' '.$strClass.'" 2>/dev/null', $strBuf, true) && preg_match("/^CLASS:\s/", $strBuf)) {
                if (self::$_cp) {
                    if (self::$_cp == 932) {
                        $codename = ' (SJIS)';
                    } elseif (self::$_cp == 949) {
                        $codename = ' (EUC-KR)';
                    } elseif (self::$_cp == 950) {
                        $codename = ' (BIG-5)';
                    } else {
                        $codename = '';
                    }
                    self::convertCP($strBuf, 'windows-'.self::$_cp.$codename);
                }
                $lines = preg_split('/\n/', $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                if (count($lines) >=3) {
                    unset($lines[0]);
                    $names = preg_split('/'.$delimeter.'/', $lines[1], -1, PREG_SPLIT_NO_EMPTY);
                    $namesc = count($names);
                    unset($lines[1]);
                    foreach ($lines as $line) {
                        $arrInstance = array();
                        $values = preg_split('/'.$delimeter.'/', $line, -1);
                        if (count($values) == $namesc) {
                            foreach ($values as $id=>$value) {
                                if (empty($strValue)) {
                                    if ($value !== "(null)") $arrInstance[$names[$id]] = trim($value);
                                    else $arrInstance[$names[$id]] = null;
                                } else {
                                    if (in_array($names[$id], $strValue)) {
                                        if ($value !== "(null)") $arrInstance[$names[$id]] = trim($value);
                                        else $arrInstance[$names[$id]] = null;
                                    }
                                }
                            }
                            $arrData[] = $arrInstance;
                        }
                    }
                }
            }
        }

        return $arrData;
    }

    /**
     * readReg function
     *
     * @return boolean command successfull or not
     */
    public static function readReg($reg, $strName, &$strBuffer, $booErrorRep = true, $dword = false, $bits64 = false)
    {
        $strBuffer = '';

        if ($reg === false) {
            if (defined('PSI_EMU_HOSTNAME')) {
                return false;
            }
            $last = strrpos($strName, "\\");
            $keyname = substr($strName, $last + 1);
            if ($bits64) {
                $param = ' /reg:64';
            } else {
                $param = '';
            }
            if ($dword) {
                $valtype = "DWORD";
            } else {
                $valtype = "SZ|EXPAND_SZ";
            }
            if (self::$_cp) {
                if (CommonFunctions::executeProgram('cmd', '/c chcp '.self::$_cp.' >nul & reg query "'.substr($strName, 0, $last).'" /v '.$keyname.$param.' 2>&1', $strBuf, $booErrorRep) && (strlen($strBuf) > 0) && preg_match("/^\s*".$keyname."\s+REG_(".$valtype.")\s+(.+)\s*$/mi", $strBuf, $buffer2)) {
                    if ($dword) {
                        $strBuffer = strval(hexdec($buffer2[2]));
                    } else {
                        $strBuffer = $buffer2[2];
                    }
                } else {
                    return false;
                }
            } else {
                if (CommonFunctions::executeProgram('reg', 'query "'.substr($strName, 0, $last).'" /v '.$keyname.$param.' 2>&1', $strBuf, $booErrorRep) && (strlen($strBuf) > 0) && preg_match("/^\s*".$keyname."\s+REG_(".$valtype.")\s+(.+)\s*$/mi", $strBuf, $buffer2)) {
                    if ($dword) {
                        $strBuffer = strval(hexdec($buffer2[2]));
                    } else {
                        $strBuffer = $buffer2[2];
                    }
                } else {
                    return false;
                }
            }
        } elseif (gettype($reg) === "object") {
            $_hkey = array('HKEY_CLASSES_ROOT'=>0x80000000, 'HKEY_CURRENT_USER'=>0x80000001, 'HKEY_LOCAL_MACHINE'=>0x80000002, 'HKEY_USERS'=>0x80000003, 'HKEY_PERFORMANCE_DATA'=>0x80000004, 'HKEY_PERFORMANCE_TEXT'=>0x80000050, 'HKEY_PERFORMANCE_NLSTEXT'=>0x80000060, 'HKEY_CURRENT_CONFIG'=>0x80000005, 'HKEY_DYN_DATA'=>0x80000006);
            $first = strpos($strName, "\\");
            $last = strrpos($strName, "\\");
            $hkey = substr($strName, 0, $first);
            if (isset($_hkey[$hkey])) {
                $sub_keys = new VARIANT();
                try {
                    if ($dword) {
                        $reg->Get("StdRegProv")->GetDWORDValue(strval($_hkey[$hkey]), substr($strName, $first+1, $last-$first-1), substr($strName, $last+1), $sub_keys);
                    } else {
                        $reg->Get("StdRegProv")->GetStringValue(strval($_hkey[$hkey]), substr($strName, $first+1, $last-$first-1), substr($strName, $last+1), $sub_keys);
                    }
                } catch (Exception $e) {
                    if ($booErrorRep) {
                        $error = PSI_Error::singleton();
                        $error->addError("GetStringValue()", preg_replace('/<br\/>/', "\n", preg_replace('/<b>|<\/b>/', '', $e->getMessage())));
                    }

                    return false;
                }
                if (variant_get_type($sub_keys) !== VT_NULL) {
                    $strBuffer = strval($sub_keys);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * enumKey function
     *
     * @return boolean command successfull or not
     */
    public static function enumKey($reg, $strName, &$arrBuffer, $booErrorRep = true)
    {
        $arrBuffer = array();

        if ($reg === false) {
            if (defined('PSI_EMU_HOSTNAME')) {
                return false;
            }
            if (self::$_cp) {
                if (CommonFunctions::executeProgram('cmd', '/c chcp '.self::$_cp.' >nul & reg query "'.$strName.'" 2>&1', $strBuf, $booErrorRep) && (strlen($strBuf) > 0) && preg_match_all("/^".preg_replace("/\\\\/", "\\\\\\\\", $strName)."\\\\(.*)/mi", $strBuf, $buffer2)) {
                    foreach ($buffer2[1] as $sub_key) {
                        $arrBuffer[] = trim($sub_key);
                    }
                } else {
                    return false;
                }
            } else {
                if (CommonFunctions::executeProgram('reg', 'query "'.$strName.'" 2>&1', $strBuf, $booErrorRep) && (strlen($strBuf) > 0) && preg_match_all("/^".preg_replace("/\\\\/", "\\\\\\\\", $strName)."\\\\(.*)/mi", $strBuf, $buffer2)) {
                    foreach ($buffer2[1] as $sub_key) {
                        $arrBuffer[] = trim($sub_key);
                    }
                } else {
                    return false;
                }
            }
        } elseif (gettype($reg) === "object") {
            $_hkey = array('HKEY_CLASSES_ROOT'=>0x80000000, 'HKEY_CURRENT_USER'=>0x80000001, 'HKEY_LOCAL_MACHINE'=>0x80000002, 'HKEY_USERS'=>0x80000003, 'HKEY_PERFORMANCE_DATA'=>0x80000004, 'HKEY_PERFORMANCE_TEXT'=>0x80000050, 'HKEY_PERFORMANCE_NLSTEXT'=>0x80000060, 'HKEY_CURRENT_CONFIG'=>0x80000005, 'HKEY_DYN_DATA'=>0x80000006);
            $first = strpos($strName, "\\");
            $hkey = substr($strName, 0, $first);
            if (isset($_hkey[$hkey])) {
                $sub_keys = new VARIANT();
                try {
                   $reg->Get("StdRegProv")->EnumKey(strval($_hkey[$hkey]), substr($strName, $first+1), $sub_keys);
                } catch (Exception $e) {
                    if ($booErrorRep) {
                        $error = PSI_Error::singleton();
                        $error->addError("enumKey()", preg_replace('/<br\/>/', "\n", preg_replace('/<b>|<\/b>/', '', $e->getMessage())));;
                    }

                    return false;
                }
                if (variant_get_type($sub_keys) !== VT_NULL) {
                    foreach ($sub_keys as $sub_key) {
                        $arrBuffer[] = $sub_key;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * initWMI function
     *
     * @return string, object or false
     */
    public static function initWMI($namespace, $booErrorRep = false)
    {
        $wmi = false;
        if (self::$_wmi !== false) { // WMI not disabled
            try {
                if (PSI_OS == 'Linux') {
                    if (defined('PSI_EMU_HOSTNAME'))
                        $wmi = '--namespace="'.$namespace.'" -U '.PSI_EMU_USER.'%'.PSI_EMU_PASSWORD.' //'.PSI_EMU_HOSTNAME.' "select * from';
                } elseif (PSI_OS == 'WINNT') {
                    $objLocator = new COM('WbemScripting.SWbemLocator');
                    if (defined('PSI_EMU_HOSTNAME'))
                        $wmi = $objLocator->ConnectServer(PSI_EMU_HOSTNAME, $namespace, PSI_EMU_USER, PSI_EMU_PASSWORD);
                    else
                        $wmi = $objLocator->ConnectServer('', $namespace);
                }
            } catch (Exception $e) {
                if ($booErrorRep) {
                    $error = PSI_Error::singleton();
                    $error->addError("WMI connect ".$namespace." error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed or credentials.");
                }
            }
        }

        return $wmi;
    }

    /**
     * convertCP function
     *
     * @return void
     */
    public static function convertCP(&$strBuf, $encoding)
    {
        if (defined('PSI_SYSTEM_CODEPAGE') && (PSI_SYSTEM_CODEPAGE != null) && ($encoding != null) && ($encoding != PSI_SYSTEM_CODEPAGE)) {
            $systemcp = PSI_SYSTEM_CODEPAGE;
            if (preg_match("/^windows-\d+ \((.+)\)$/", $systemcp, $buf)) {
                $systemcp = $buf[1];
            }
            if (preg_match("/^windows-\d+ \((.+)\)$/", $encoding, $buf)) {
                $encoding = $buf[1];
            }
            $enclist = mb_list_encodings();
            if (in_array($encoding, $enclist) && in_array($systemcp, $enclist)) {
                $strBuf = mb_convert_encoding($strBuf, $encoding, $systemcp);
            } elseif (function_exists("iconv")) {
                if (($iconvout=iconv($systemcp, $encoding.'//IGNORE', $strBuf))!==false) {
                    $strBuf = $iconvout;
                }
            } elseif (function_exists("libiconv") && (($iconvout=libiconv($systemcp, $encoding, $strBuf))!==false)) {
                $strBuf = $iconvout;
            }
        }
    }

    /**
     * build the global Error object and create the WMI connection
     */
    public function __construct($blockname = false)
    {
        parent::__construct($blockname);
        if (!defined('PSI_EMU_HOSTNAME') && CommonFunctions::executeProgram('cmd', '/c ver 2>nul', $ver_value, false) && (($ver_value = trim($ver_value)) !== "")) {
            $this->_ver = $ver_value;
        }
        if (($this->_ver !== "") && preg_match("/ReactOS\r?\n\S+\s+.+/", $this->_ver)) {
            self::$_wmi = false; // No WMI info on ReactOS yet
            $this->_reg = false; // No EnumKey and ReadReg on ReactOS yet
        } else {
            if (PSI_OS == 'WINNT') {
                if (defined('PSI_EMU_HOSTNAME')) {
                    try {
                        $objLocator = new COM('WbemScripting.SWbemLocator');
                        $wmi = $objLocator->ConnectServer('', 'root\CIMv2');
                        $buffer = self::getWMI($wmi, 'Win32_OperatingSystem', array('CodeSet'));
                        if (!$buffer) {
                            $reg = $objLocator->ConnectServer('', 'root\default');
                            if (self::readReg($reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\CodePage\\ACP", $strBuf, false)) {
                                $buffer[0]['CodeSet'] = $strBuf;
                            }
                        }
                        if ($buffer && isset($buffer[0]) && isset($buffer[0]['CodeSet'])) {
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
                            define('PSI_SYSTEM_CODEPAGE', 'windows-'.$codeset.$codename);
                        } else {
                            define('PSI_SYSTEM_CODEPAGE', null);
                            if (PSI_DEBUG) {
                                $this->error->addError("__construct()", "PhpSysInfo can not detect PSI_SYSTEM_CODEPAGE");
                            }
                        }
                    } catch (Exception $e) {
                        define('PSI_SYSTEM_CODEPAGE', null);
                        if (PSI_DEBUG) {
                            $this->error->addError("WMI connect error", "PhpSysInfo can not connect to the WMI interface for security reasons.\nCheck an authentication mechanism for the directory where phpSysInfo is installed");
                        }
                    }
                } else {
                    define('PSI_SYSTEM_CODEPAGE', null);
                }
            }
            self::$_wmi = self::initWMI('root\CIMv2', true);
            if (PSI_OS == 'WINNT') {
                $this->_reg = self::initWMI('root\default', PSI_DEBUG);
                if (gettype($this->_reg) === "object") {
                    $this->_reg->Security_->ImpersonationLevel = 3;
                }
            } else {
                $this->_reg = false; // No EnumKey and ReadReg on Linux
            }
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
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\CodePage\\ACP", $strBuf, false)) {
                $buffer[0]['CodeSet'] = $strBuf;
            }
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\Language\\Default", $strBuf, false)) {
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
                self::$_cp = $codeset;
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
        if ($this->_wmidevices === null) {
            $this->_wmidevices = array();
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $this->_wmidevices = self::getWMI(self::$_wmi, 'Win32_PnPEntity', array('Name', 'PNPDeviceID', 'Manufacturer', 'PNPClass'));
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL) {
                    $this->_wmidisks = self::getWMI(self::$_wmi, 'Win32_DiskDrive', array('PNPDeviceID', 'Size', 'SerialNumber'));
                } else {
                    $this->_wmidisks = self::getWMI(self::$_wmi, 'Win32_DiskDrive', array('PNPDeviceID', 'Size'));
                }
            } else {
                $this->_wmidevices = self::getWMI(self::$_wmi, 'Win32_PnPEntity', array('Name', 'PNPDeviceID'));
            }

            if (empty($this->_wmidevices)) {
                $lstdevs = array();
                $services = array();
                foreach (array('PCI', 'USB') as $type) {
                    $hkey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Enum\\".$type;
                    if (self::enumKey($this->_reg, $hkey, $vendevs, false)) {
                        foreach ($vendevs as $vendev) if (self::enumKey($this->_reg, $hkey."\\".$vendev, $ids, false)) {
                            foreach ($ids as $id) {
                                if ($type === 'PCI') { // enumerate all PCI devices
                                    $lstdevs[$type."\\".$vendev."\\".$id] = true;
                                } elseif (self::readReg($this->_reg, $hkey."\\".$vendev."\\".$id."\\Service", $service, false)) {
                                    $services[$service] = true; // ever used USB services
                                    break;
                                }
                            }
                        }
                    }
                }

                $hkey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services";
                foreach ($services as $service=>$tmp) if (self::readReg($this->_reg, $hkey."\\".$service."\\Enum\\Count", $count, false, true) && ($count > 0)) {
                    for ($i = 0; $i < $count; $i++) if (self::readReg($this->_reg, $hkey."\\".$service."\\Enum\\".$i, $id, false) && preg_match("/^USB/", $id)) {
                        $lstdevs[$id] = true; // used USB devices
                    }
                }

                $hkey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Enum\\";
                foreach ($lstdevs as $lstdev=>$tmp) {
                    if (self::readReg($this->_reg, $hkey.$lstdev."\\DeviceDesc", $nameBuf, false)) {
                        $namesplit = preg_split('/;/', $nameBuf, -1, PREG_SPLIT_NO_EMPTY);
                        if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS && self::readReg($this->_reg, $hkey.$lstdev."\\Mfg", $mfgBuf, false)) {
                            $mfgsplit = preg_split('/;/', $mfgBuf, -1, PREG_SPLIT_NO_EMPTY);
                            $this->_wmidevices[] = array('Name'=>$namesplit[count($namesplit)-1], 'PNPDeviceID'=>$lstdev, 'Manufacturer'=>$mfgsplit[count($mfgsplit)-1]);
                        } else {
                            $this->_wmidevices[] = array('Name'=>$namesplit[count($namesplit)-1], 'PNPDeviceID'=>$lstdev);
                        }
                    }
                }

                $hkey = "HKEY_LOCAL_MACHINE\\HARDWARE\\DEVICEMAP\\Scsi";
                $id = 0;
                if (self::enumKey($this->_reg, $hkey, $portBuf, false)) {
                    foreach ($portBuf as $scsiport) {
                        if (self::enumKey($this->_reg, $hkey."\\".$scsiport, $busBuf, false)) {
                            foreach ($busBuf as $scsibus) {
                                if (self::enumKey($this->_reg, $hkey."\\".$scsiport."\\".$scsibus, $tarBuf, false)) {
                                    foreach ($tarBuf as $scsitar) if (!strncasecmp($scsitar, "Target Id ", strlen("Target Id "))) {
                                        if (self::enumKey($this->_reg, $hkey."\\".$scsiport."\\".$scsibus."\\".$scsitar, $logBuf, false)) {
                                            foreach ($logBuf as $scsilog) if (!strncasecmp($scsilog, "Logical Unit Id ", strlen("Logical Unit Id "))) {
                                               $hkey2 = $hkey."\\".$scsiport."\\".$scsibus."\\".$scsitar."\\".$scsilog."\\";
                                               if ((self::readReg($this->_reg, $hkey2."DeviceType", $typeBuf, false) || self::readReg($this->_reg, $hkey2."Type", $typeBuf, false))
                                                  && (($typeBuf=strtolower(trim($typeBuf))) !== "")) {
                                                  if ((($typeBuf == 'diskperipheral') || ($typeBuf == 'cdromperipheral'))
                                                     && self::readReg($this->_reg, $hkey2."Identifier", $ideBuf, false)) {
                                                      $this->_wmidevices[] = array('Name'=>$ideBuf, 'PNPDeviceID'=>'SCSI\\'.$id);
                                                      if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS && defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL
                                                         && self::readReg($this->_reg, $hkey2."SerialNumber", $serBuf, false)
                                                         && (($serBuf=trim($serBuf)) !== "")) {
                                                          $this->_wmidisks[] = array('PNPDeviceID'=>'SCSI\\'.$id, 'SerialNumber'=>$serBuf);
                                                      }
                                                      $id++;
                                                  }
                                               }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $list = array();
        foreach ($this->_wmidevices as $device) {
            if (substr($device['PNPDeviceID'], 0, strpos($device['PNPDeviceID'], "\\") + 1) == ($strType."\\")) {
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    if (!isset($device['PNPClass']) || ($device['PNPClass']===$strType) || ($device['PNPClass']==='System')) {
                        $device['PNPClass'] = null;
                    }
                    if (!isset($device['Manufacturer']) || preg_match('/^\(.*\)$/', $device['Manufacturer']) || (($device['PNPClass']==='USB') && preg_match('/\sUSB\s/', $device['Manufacturer']))) {
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
//                            if (preg_match('/\\\\([^\\\\][^&\\\\][^\\\\]+)$/', $device['PNPDeviceID'], $buf)) { // second character !== &
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
        if (PSI_USE_VHOST && !defined('PSI_EMU_HOSTNAME')) {
            if (CommonFunctions::readenv('SERVER_NAME', $hnm)) $this->sys->setHostname($hnm);
        } else {
            $buffer = $this->_get_Win32_ComputerSystem();
            if (!$buffer && self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\ComputerName\\ActiveComputerName\\ComputerName", $strBuf, false) && (strlen($strBuf) > 0)) {
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
                        $hostname = gethostbyaddr($ip);
                        if ($hostname !== false)
                            $this->sys->setHostname($hostname);
                        else
                            $this->sys->setHostname($result);
                    }
                } else {
                    $this->sys->setHostname($result);
                }
            } elseif (defined('PSI_EMU_HOSTNAME')) {
                $this->sys->setHostname(PSI_EMU_HOSTNAME);
            } elseif (CommonFunctions::readenv('COMPUTERNAME', $hnm)) {
                $this->sys->setHostname($hnm);
            }
        }
    }

    /**
     * Virtualizer info
     *
     * @return void
     */
    protected function _virtualizer()
    {
        if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
            $cpuvirt = $this->sys->getVirtualizer(); // previous info from _cpuinfo()

            $vendor_array = array();
            if ($this->_Model != "") {
                $vendor_array[] = $this->_Model;
            }
            if ($this->_Manufacturer != "") {
                if ($this->_Model != "") {
                    $vendor_array[] = $this->_Manufacturer." ".$this->_Model;
                } else {
                    $vendor_array[] = $this->_Manufacturer;
                }
            }
            $novm = true;
            if (count($vendor_array)>0) {
                $virt = CommonFunctions::decodevirtualizer($vendor_array);
                if ($virt !== null) {
                    $this->sys->setVirtualizer($virt);
                    $novm = false;
                }
            }
            if ($novm) {
                // Detect QEMU cpu
                if (isset($cpuvirt["cpuid:QEMU"])) {
                    $this->sys->setVirtualizer('qemu'); // QEMU
                }
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
        } elseif (!defined('PSI_EMU_HOSTNAME') && (substr($this->sys->getDistribution(), 0, 7)=="ReactOS") && CommonFunctions::executeProgram('uptime', '', $strBuf, false) && (strlen($strBuf) > 0) && preg_match("/^System Up Time:\s+(\d+) days, (\d+) Hours, (\d+) Minutes, (\d+) Seconds/", $strBuf, $ar_buf)) {
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
        if (!defined('PSI_EMU_HOSTNAME') && CommonFunctions::executeProgram('quser', '', $strBuf, false) && (strlen($strBuf) > 0)) {
                $lines = preg_split('/\n/', $strBuf);
                $users = count($lines)-1;
        } else {
            $users = 0;
            $buffer = self::getWMI(self::$_wmi, 'Win32_Process', array('Caption'));
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
            if (($this->_ver !== "") && preg_match("/^Microsoft [^\[]*\s*\[\D*\s*(".$ver."\.\d+).*\]/", $this->_ver, $ar_temp)) {
                $kernel = $ar_temp[1];
            } else {
                $kernel = $ver;
            }
            if ($buffer[0]['ServicePackMajorVersion'] > 0) {
                $kernel .= ' SP'.$buffer[0]['ServicePackMajorVersion'];
            }
            if ($allCpus = $this->_get_Win32_Processor()) {
                $addresswidth = 0;
                if (isset($allCpus[0]['AddressWidth']) && (($addresswidth = $allCpus[0]['AddressWidth']) > 0)) {
                    $kernel .= ' ('.$addresswidth.'-bit)';
                }
                if (isset($allCpus[0]['Architecture'])) {
                    switch ($allCpus[0]['Architecture']) {
                    case 0: $kernel .= ' x86'; break;
                    case 1: $kernel .= ' MIPS'; break;
                    case 2: $kernel .= ' Alpha'; break;
                    case 3: $kernel .= ' PowerPC'; break;
                    case 5: $kernel .= ' ARM'; break;
                    case 6: $kernel .= ' ia64'; break;
                    case 9: if ($addresswidth == 32) {
                                $kernel .= ' x86';
                            } else {
                                $kernel .= ' x64';
                            }
                            break;
                    case 12: if ($addresswidth == 32) {
                                 $kernel .= ' ARM';
                             } else {
                                 $kernel .= ' ARM64';
                             }
                    }
                }
            }
            $this->sys->setKernel($kernel);
            $distribution = $buffer[0]['Caption'];
            if (version_compare($ver, "10.0", ">=") && !preg_match('/server/i', $buffer[0]['Caption']) && ($list = @parse_ini_file(PSI_APP_ROOT."/data/osnames.ini", true))) {
                $karray = preg_split('/\./', $ver);
                if (isset($karray[2]) && isset($list['win10'][$karray[2]])) {
                    $distribution .= ' ('.$list['win10'][$karray[2]].')';
                }
            }
            $this->sys->setDistribution($distribution);
            if (version_compare($ver, "5.1", "<"))
                $icon = 'Win2000.png';
            elseif (version_compare($ver, "5.1", ">=") && version_compare($ver, "6.0", "<"))
                $icon = 'WinXP.png';
            elseif (version_compare($ver, "6.0", ">=") && version_compare($ver, "6.2", "<"))
                $icon = 'WinVista.png';
            elseif (version_compare($ver, "6.2", ">=") && version_compare($ver, "10.0.21996", "<"))
                $icon = 'Win8.png';
            else
                $icon = 'Win11.png';
            $this->sys->setDistributionIcon($icon);
        } elseif ($this->_ver !== "") {
                if (preg_match("/ReactOS\r?\n\S+\s+(.+)/", $this->_ver, $ar_temp)) {
                    if (preg_match("/^(\d+\.\d+\.\d+[\S]*)(.+)$/", trim($ar_temp[1]), $ver_temp)) {
                        $this->sys->setDistribution("ReactOS ".trim($ver_temp[1]));
                        $this->sys->setKernel(trim($ver_temp[2]));
                    } else {
                        $this->sys->setDistribution("ReactOS");
                        $this->sys->setKernel($ar_temp[1]);
                    }
                    $this->sys->setDistributionIcon('ReactOS.png');
                } elseif (preg_match("/^(Microsoft [^\[]*)\s*\[\D*\s*([\.\d]+)\]/", $this->_ver, $ar_temp)) {
                    $ver = $ar_temp[2];
                    $kernel = $ver;
                    if (($this->_reg === false) && self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\ProductName", $strBuf, false, false, true) && (strlen($strBuf) > 0)) { // only if reg query via cmd
                        if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SOFTWARE\\WOW6432Node\\Microsoft\\Windows NT\\CurrentVersion\\ProductName", $tmpBuf, false)) {
                            $kernel .= ' (64-bit)';
                        }
                        if (preg_match("/^Microsoft /", $strBuf)) {
                            $distribution = $strBuf;
                        } else {
                            $distribution = "Microsoft ".$strBuf;
                        }
                    } elseif (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\ProductName", $strBuf, false) && (strlen($strBuf) > 0)) {
                        if (preg_match("/^Microsoft /", $strBuf)) {
                            $distribution = $strBuf;
                        } else {
                            $distribution = "Microsoft ".$strBuf;
                        }
                    } else {
                        $distribution = $ar_temp[1];
                    }
                    $this->sys->setKernel($kernel);
                    if (version_compare($ver, "10.0", ">=") && !preg_match('/server/i', $this->sys->getDistribution()) && ($list = @parse_ini_file(PSI_APP_ROOT."/data/osnames.ini", true))) {
                        if (version_compare($ver, "10.0.21996", ">=") && version_compare($ver, "11.0", "<")) {
                            $distribution = preg_replace("/Windows 10/", "Windows 11", $distribution); // fix Windows 11 detection
                        }
                        $karray = preg_split('/\./', $ver);
                        if (isset($karray[2]) && isset($list['win10'][$karray[2]])) {
                            $distribution .= ' ('.$list['win10'][$karray[2]].')';
                        }
                    }
                    $this->sys->setDistribution($distribution);
                    if (version_compare($ver, "5.1", "<"))
                        $icon = 'Win2000.png';
                    elseif (version_compare($ver, "5.1", ">=") && version_compare($ver, "6.0", "<"))
                        $icon = 'WinXP.png';
                    elseif (version_compare($ver, "6.0", ">=") && version_compare($ver, "6.2", "<"))
                        $icon = 'WinVista.png';
                    elseif (version_compare($ver, "6.2", ">=") && version_compare($ver, "10.0.21996", "<"))
                        $icon = 'Win8.png';
                    else
                        $icon = 'Win11.png';
                    $this->sys->setDistributionIcon($icon);
                } else {
                    $this->sys->setDistribution("WINNT");
                    $this->sys->setDistributionIcon('WINNT.png');
                }
        } else {
            $this->sys->setDistribution("WINNT");
            $this->sys->setDistributionIcon('WINNT.png');
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
        if (empty($allCpus)) {
            $hkey = "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\CentralProcessor";
            if (self::enumKey($this->_reg, $hkey, $arrBuf, false)) {
                foreach ($arrBuf as $coreCount) {
                    if (self::readReg($this->_reg, $hkey."\\".$coreCount."\\ProcessorNameString", $strBuf, false)) {
                        $allCpus[$coreCount]['Name'] = $strBuf;
                    }
                    if (self::readReg($this->_reg, $hkey."\\".$coreCount."\\~MHz", $strBuf, false)) {
                        if (preg_match("/^0x([0-9a-f]+)$/i", $strBuf, $hexvalue)) {
                            $allCpus[$coreCount]['CurrentClockSpeed'] = hexdec($hexvalue[1]);
                        }
                    }
                    if (self::readReg($this->_reg, $hkey."\\".$coreCount."\\VendorIdentifier", $strBuf, false)) {
                        $allCpus[$coreCount]['Manufacturer'] = $strBuf;
                    }
                    if (self::readReg($this->_reg, $hkey."\\".$coreCount."\\Identifier", $strBuf, false)) {
                        $allCpus[$coreCount]['Caption'] = $strBuf;
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

        $cpulist = null;
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
                } elseif (isset($oneCpu['L2CacheSize']) && ($oneCpu['L2CacheSize'] > 0)) {
                    $cpu->setCache($oneCpu['L2CacheSize'] * 1024);
                }
                if (isset($oneCpu['CurrentVoltage']) && ($oneCpu['CurrentVoltage'] > 0)) {
                    $cpu->setVoltage($oneCpu['CurrentVoltage']/10);
                }
                if (isset($oneCpu['CurrentClockSpeed']) && ($oneCpu['CurrentClockSpeed'] > 0)) {
                    $cpu->setCpuSpeed($oneCpu['CurrentClockSpeed']);
                    if (isset($oneCpu['MaxClockSpeed']) && ($oneCpu['CurrentClockSpeed'] <= $oneCpu['MaxClockSpeed'])) $cpu->setCpuSpeedMax($oneCpu['MaxClockSpeed']);
                }
                if (isset($oneCpu['ExtClock']) && ($oneCpu['ExtClock'] > 0)) {
                    $cpu->setBusSpeed($oneCpu['ExtClock']);
                }
                if (isset($oneCpu['Manufacturer'])) {
                    $cpumanufacturer = $oneCpu['Manufacturer'];
                    $cpu->setVendorId($cpumanufacturer);
                    if ($cpumanufacturer === "QEMU") {
                        if (isset($oneCpu['Caption'])) {
                            $impl = '';
                            if (preg_match('/^ARMv8 \(64-bit\) Family 8 Model ([0-9a-fA-F]+) Revision[ ]+([0-9a-fA-F]+)$/', $oneCpu['Caption'], $partvar)) {
                                switch (strtolower($partvar[1])) {
                                case '51':
                                    $impl = '0x0'; break; // Qemu
                                case 'd03':
                                case 'd07':
                                case 'd08':
                                    $impl = '0x41'; break; // ARM Limited
                                case '1':
                                    $impl = '0x46'; // Fujitsu Ltd.
                                }
                            } elseif (preg_match('/^ARM Family 7 Model ([0-9a-fA-F]+) Revision[ ]+([0-9a-fA-F]+)$/', $oneCpu['Caption'], $partvar)) {
                                switch (strtolower($partvar[1])) {
                                case 'c07':
                                case 'c0f':
                                        $impl = '0x41'; // ARM Limited
                                }
                            }
                            if ($impl !== '') {
                                if ($cpulist === null) $cpulist = @parse_ini_file(PSI_APP_ROOT."/data/cpus.ini", true);
                                if ($cpulist) {
                                    if ((isset($cpulist['cpu'][$cpufromlist = strtolower($impl.',0x'.$partvar[1].',0x'.$partvar[2])]))
                                       || isset($cpulist['cpu'][$cpufromlist = strtolower($impl.',0x'.$partvar[1])])) {
                                        if (($cpumodel = $cpu->getModel()) !== '') {
                                            $cpu->setModel($cpumodel.' - '.$cpulist['cpu'][$cpufromlist]);
                                        } else {
                                            $cpu->setModel($cpulist['cpu'][$cpufromlist]);
                                        }
                                    }
                                }
                            }
                        }
                        if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
                            $this->sys->setVirtualizer("cpuid:QEMU", false);
                        }
                    }
                }
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
        $bufferp = self::getWMI(self::$_wmi, 'Win32_BaseBoard', array('Product'));
        $bufferb = self::getWMI(self::$_wmi, 'Win32_BIOS', array('SMBIOSBIOSVersion', 'ReleaseDate'));

        if (!$buffer) {
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\systemManufacturer", $strBuf, false)) {
                $buffer[0]['Manufacturer'] = $strBuf;
            }
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\SystemProductName", $strBuf, false)) {
                $buffer[0]['Model'] = $strBuf;
            }
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\SystemFamily", $strBuf, false)) {
                $buffer[0]['SystemFamily'] = $strBuf;
            }
        }
        if (!$bufferp) {
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\BaseBoardProduct", $strBuf, false)) {
                $bufferp[0]['Product'] = $strBuf;
            }
        }
        if (!$bufferb) {
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\BIOSVersion", $strBuf, false)) {
                $bufferb[0]['SMBIOSBIOSVersion'] = $strBuf;
            }
            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\HARDWARE\\DESCRIPTION\\System\\BIOS\\BIOSReleaseDate", $strBuf, false)) {
                $bufferb[0]['ReleaseDate'] = $strBuf;
            }
        }

        if (defined('PSI_SHOW_VIRTUALIZER_INFO') && PSI_SHOW_VIRTUALIZER_INFO) {
            if (isset($buffer[0]['Manufacturer'])) {
                $this->_Manufacturer = $buffer[0]['Manufacturer'];
            }
            if (isset($buffer[0]['Model'])) {
                $this->_Model = $buffer[0]['Model'];
            }
        }

        $buf = "";
        $model = "";
        if ($buffer && isset($buffer[0])) {
            if (isset($buffer[0]['Manufacturer']) && !preg_match("/^To be filled by O\.E\.M\.$|^System manufacturer$|^Not Specified$/i", $buf2=trim($buffer[0]['Manufacturer'])) && ($buf2 !== "")) {
                $buf .= ' '.$buf2;
            }

            if (isset($buffer[0]['Model']) && !preg_match("/^To be filled by O\.E\.M\.$|^System Product Name$|^Not Specified$/i", $buf2=trim($buffer[0]['Model'])) && ($buf2 !== "")) {
                $model = $buf2;
                $buf .= ' '.$buf2;
            }
        }
        if ($bufferp && isset($bufferp[0])) {
            if (isset($bufferp[0]['Product']) && !preg_match("/^To be filled by O\.E\.M\.$|^BaseBoard Product Name$|^Not Specified$|^Default string$/i", $buf2=trim($bufferp[0]['Product'])) && ($buf2 !== "")) {
                if ($buf2 !== $model) {
                    $buf .= '/'.$buf2;
                } elseif (isset($buffer[0]['SystemFamily']) && !preg_match("/^To be filled by O\.E\.M\.$|^System Family$|^Not Specified$/i", $buf2=trim($buffer[0]['SystemFamily'])) && ($buf2 !== "")) {
                    $buf .= '/'.$buf2;
                }
            }
        }
        if ($bufferb && isset($bufferb[0])) {
            $bver = "";
            $brel = "";
            if (isset($bufferb[0]['SMBIOSBIOSVersion']) && (($buf2=trim($bufferb[0]['SMBIOSBIOSVersion'])) !== "")) {
                $bver .= ' '.$buf2;
            }
            if (isset($bufferb[0]['ReleaseDate'])) {
                if (preg_match("/^(\d{4})(\d{2})(\d{2})\d{6}\.\d{6}\+\d{3}$/", $bufferb[0]['ReleaseDate'], $dateout)) {
                    $brel .= ' '.$dateout[2].'/'.$dateout[3].'/'.$dateout[1];
                } elseif (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $bufferb[0]['ReleaseDate'])) {
                    $brel .= ' '.$bufferb[0]['ReleaseDate'];
                }
            }
            if ((trim($bver) !== "") || (trim($brel) !== "")) {
                $buf .= ', BIOS'.$bver.$brel;
            }
        }

        if (trim($buf) != "") {
            $this->sys->setMachine(trim($buf));
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
                if (($pciDev['Manufacturer'] !== null) && preg_match("/^@[^\.]+\.inf,%([^%]+)%$/i", trim($pciDev['Manufacturer']), $mbuff)) {
                   $dev->setManufacturer($mbuff[1]);
                } else {
                    $dev->setManufacturer($pciDev['Manufacturer']);
                }
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
        if (self::$_wmi) {
            $buffer = $this->_get_Win32_OperatingSystem();
            if ($buffer && isset($buffer[0]) && isset($buffer[0]['Version']) && version_compare($buffer[0]['Version'], "6.2", ">=")) { // minimal windows 2012 or windows 8
                $allDevices = self::getWMI(self::$_wmi, 'Win32_PerfRawData_Tcpip_NetworkAdapter', array('Name', 'BytesSentPersec', 'BytesTotalPersec', 'BytesReceivedPersec', 'PacketsReceivedErrors', 'PacketsReceivedDiscarded', 'CurrentBandwidth'));
            } else {
                $allDevices = self::getWMI(self::$_wmi, 'Win32_PerfRawData_Tcpip_NetworkInterface', array('Name', 'BytesSentPersec', 'BytesTotalPersec', 'BytesReceivedPersec', 'PacketsReceivedErrors', 'PacketsReceivedDiscarded', 'CurrentBandwidth'));
            }
            if ($allDevices) {
                $aliases = array();
                $hkey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Network\\{4D36E972-E325-11CE-BFC1-08002BE10318}";
                if (self::enumKey($this->_reg, $hkey, $arrBuf, false)) {
                    foreach ($arrBuf as $netID) {
                        if (self::readReg($this->_reg, $hkey."\\".$netID."\\Connection\\PnPInstanceId", $strInstanceID, false)) {
                            if (self::readReg($this->_reg, "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Enum\\".$strInstanceID."\\FriendlyName", $strName, false)) {
                                $cname = str_replace(array('(', ')', '#', '/'), array('[', ']', '_', '_'), $strName); //convert to canonical
                                if (!isset($aliases[$cname])) { // duplicate checking
                                    $aliases[$cname]['id'] = $netID;
                                    $aliases[$cname]['name'] = $strName;
                                    if (self::readReg($this->_reg, $hkey."\\".$netID."\\Connection\\Name", $strCName, false)
                                       && (str_replace(array('(', ')', '#', '/'), array('[', ']', '_', '_'), $strCName) !== $cname)) {
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
                if (self::enumKey($this->_reg, $hkey, $arrBuf, false)) {
                    foreach ($arrBuf as $netCount) {
                        if (self::readReg($this->_reg, $hkey."\\".$netCount."\\Description", $strName, false)
                            && self::readReg($this->_reg, $hkey."\\".$netCount."\\ServiceName", $strGUID, false)) {
                            $cname = str_replace(array('(', ')', '#', '/'), array('[', ']', '_', '_'), $strName); //convert to canonical
                            if (!isset($aliases2[$cname])) { // duplicate checking
                                $aliases2[$cname]['id'] = $strGUID;
                                $aliases2[$cname]['name'] = $strName;
                            } else {
                                $aliases2[$cname]['id'] = '';
                            }
                        }
                    }
                }

                $allNetworkAdapterConfigurations = self::getWMI(self::$_wmi, 'Win32_NetworkAdapterConfiguration', array('SettingID', /*'Description',*/ 'MACAddress', 'IPAddress'));
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
                                if (isset($NetworkAdapterConfiguration['MACAddress']) && trim($NetworkAdapterConfiguration['MACAddress']) !== "") $macexist = true;
                                if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS) {
                                    if (isset($ali[$name]['netname'])) $dev->setInfo(str_replace(';', ':', $ali[$name]['netname']));
                                    if ((!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR)
                                       && $macexist) $dev->setInfo(($dev->getInfo()?$dev->getInfo().';':'').str_replace(':', '-', strtoupper(trim($NetworkAdapterConfiguration['MACAddress']))));
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
        if (self::$_wmi) {
            $buffer = $this->_get_Win32_OperatingSystem();
            if ($buffer) {
                $this->sys->setMemTotal($buffer[0]['TotalVisibleMemorySize'] * 1024);
                $this->sys->setMemFree($buffer[0]['FreePhysicalMemory'] * 1024);
                $this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
            }
            $buffer = self::getWMI(self::$_wmi, 'Win32_PageFileUsage');
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
        $buffer = self::getWMI(self::$_wmi, 'Win32_LogicalDisk', array('Name', 'Size', 'FreeSpace', 'FileSystem', 'DriveType', 'MediaType'));
        foreach ($buffer as $filesystem) {
            $dev = new DiskDevice();
            $dev->setMountPoint($filesystem['Name']);
            if (isset($filesystem['FileSystem'])) {
                $dev->setFsType($filesystem['FileSystem']);
            }
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
        if (!$buffer && !defined('PSI_EMU_HOSTNAME')) {
            $letters = array();
            if (CommonFunctions::executeProgram('fsutil', 'fsinfo drives 2>nul', $out_value, false) && ($out_value !== '') && preg_match('/^Drives:\s*(.+)$/i', $out_value, $disks)) {
                $diskarr = preg_split('/ /', $disks[1], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($diskarr as $disk) if (preg_match('/^(\w):\\\\$/', $disk, $diskletter)) {
                    $letters[] = $diskletter[1];
                }
            }
            if (count($letters) == 0) for ($letter='A'; $letter!='AA'; $letter++) {
                $letters[] = $letter;
            }
            if ((substr($this->sys->getDistribution(), 0, 7)=="ReactOS") && CommonFunctions::executeProgram('cmd', '/c free 2>nul', $out_value, false)) {
                foreach ($letters as $letter) if (CommonFunctions::executeProgram('cmd', '/c free '.$letter.': 2>nul', $out_value, false)) {
                    $values = preg_replace('/[^\d\n]/', '', $out_value);
                    if (preg_match('/\n(\d+)\n(\d+)\n(\d+)$/', $values, $out_dig)) {
                        $size = $out_dig[1];
                        $used = $out_dig[2];
                        $free = $out_dig[3];
                        if ($used + $free == $size) {
                            $dev = new DiskDevice();
                            $dev->setMountPoint($letter.":");
                            if (CommonFunctions::executeProgram('fsutil', 'fsinfo volumeinfo '.$letter.':\ 2>nul', $out_value, false) && ($out_value !== '') && preg_match('/\nFile System Name\s*:\s*(\S+)/im', $out_value, $fsname)) {
                                $dev->setFsType($fsname[1]);
                            } else {
                                $dev->setFsType('Unknown');
                            }
                            $dev->setName('Unknown');
                            $dev->setTotal($size);
                            $dev->setUsed($used);
                            $dev->setFree($free);
                            $this->sys->setDiskDevices($dev);
                        }
                    }
                }
            } else {
                if (substr($this->sys->getDistribution(), 0, 7)=="ReactOS") {
                    $disksep = ':\\';
                } else {
                    $disksep = ':';
                }
                foreach ($letters as $letter) {
                    $size = disk_total_space($letter.':\\');
                    $free = disk_free_space($letter.':\\');
                    if (($size !== false) && ($free !== false) && ($size >= 0) && ($free >= 0) && ($size >= $free)) {
                        $dev = new DiskDevice();
                        $dev->setMountPoint($letter.":");
                        if (CommonFunctions::executeProgram('fsutil', 'fsinfo volumeinfo '.$letter.$disksep.' 2>nul', $out_value, false) && ($out_value !== '') && preg_match('/\nFile System Name\s*:\s*(\S+)/im', $out_value, $fsname)) {
                            $dev->setFsType($fsname[1]);
                        } else {
                            $dev->setFsType('Unknown');
                        }
                        $dev->setName('Unknown');
                        $dev->setTotal($size);
                        $dev->setUsed($size - $free);
                        $dev->setFree($free);
                       $this->sys->setDiskDevices($dev);
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
        if (!defined('PSI_EMU_HOSTNAME') && CommonFunctions::executeProgram('qprocess', '*', $strBuf, false) && (strlen($strBuf) > 0)) {
            $lines = preg_split('/\n/', $strBuf);
            $processes['*'] = (count($lines)-1) - 3 ; //correction for process "qprocess *"
        }
        if ($processes['*'] <= 0) {
            $buffer = self::getWMI(self::$_wmi, 'Win32_Process', array('Caption'));
            $processes['*'] = count($buffer);
        }
        $processes[' '] = $processes['*'];
        $this->sys->setProcesses($processes);
    }

    /**
     * MEM information
     *
     * @return void
     */
    private function _meminfo()
    {
        $allMems = self::_get_Win32_PhysicalMemory();
        if ($allMems) {
            $reg = false;
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                $arrMems = self::getWMI(self::$_wmi, 'Win32_PhysicalMemoryArray', array('MemoryErrorCorrection'));
                $reg = (count($arrMems) == 1) && isset($arrMems[0]['MemoryErrorCorrection']) && ($arrMems[0]['MemoryErrorCorrection'] == 6);
            }
            foreach ($allMems as $mem) {
                $dev = new HWDevice();
                $name = '';
                if (isset($mem['PartNumber']) && !preg_match("/^PartNum\d+$/", $part = $mem['PartNumber']) && ($part != '') && ($part != 'None') && ($part != 'N/A') && ($part != 'NOT AVAILABLE')) {
                    $name = $part;
                 }
                if (isset($mem['DeviceLocator']) && (($dloc = $mem['DeviceLocator']) != '') && ($dloc != 'None') && ($dloc != 'N/A')) {
                    if ($name != '') {
                        $name .= ' - '.$dloc;
                    } else {
                        $name = $dloc;
                    }
                }
                if (isset($mem['BankLabel']) && (($bank = $mem['BankLabel']) != '') && ($bank != 'None') && ($bank != 'N/A')) {
                    if ($name != '') {
                        $name .= ' in '.$bank;
                    } else {
                        $name = 'Physical Memory in '.$bank;
                    }
                }
                if ($name != '') {
                    $dev->setName(trim($name));
                } else {
                    $dev->setName('Physical Memory');
                }
                if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                    if (isset($mem['Manufacturer']) && !preg_match("/^([A-F\d]{4}|[A-F\d]{12}|[A-F\d]{16})$/", $manufacturer = $mem['Manufacturer']) && !preg_match("/^Manufacturer\d+$/", $manufacturer) && !preg_match("/^Mfg \d+$/", $manufacturer) && ($manufacturer != '') && ($manufacturer != 'None') && ($manufacturer != 'N/A') && ($manufacturer != 'UNKNOWN')) {
                        $dev->setManufacturer($manufacturer);
                    }
                    if (isset($mem['Capacity'])) {
                        $dev->setCapacity($mem['Capacity']);
                    }
                    $memtype = '';
                    if (isset($mem['MemoryType']) && (($memval = $mem['MemoryType']) != 0)) {
                        switch ($memval) {
//                        case 0: $memtype = 'Unknown'; break;
//                        case 1: $memtype = 'Other'; break;
                        case 2: $memtype = 'DRAM'; break;
                        case 3: $memtype = 'Synchronous DRAM'; break;
                        case 4: $memtype = 'Cache DRAM'; break;
                        case 5: $memtype = 'EDO'; break;
                        case 6: $memtype = 'EDRAM'; break;
                        case 7: $memtype = 'VRAM'; break;
                        case 8: $memtype = 'SRAM'; break;
                        case 9: $memtype = 'RAM'; break;
                        case 10: $memtype = 'ROM'; break;
                        case 11: $memtype = 'Flash'; break;
                        case 12: $memtype = 'EEPROM'; break;
                        case 13: $memtype = 'FEPROM'; break;
                        case 14: $memtype = 'EPROM'; break;
                        case 15: $memtype = 'CDRAM'; break;
                        case 16: $memtype = '3DRAM'; break;
                        case 17: $memtype = 'SDRAM'; break;
                        case 18: $memtype = 'SGRAM'; break;
                        case 19: $memtype = 'RDRAM'; break;
                        case 20: $memtype = 'DDR'; break;
                        case 21: $memtype = 'DDR2'; break;
                        case 22: $memtype = 'DDR2 FB-DIMM'; break;
                        case 24: $memtype = 'DDR3'; break;
                        case 25: $memtype = 'FBD2'; break;
                        case 26: $memtype = 'DDR4';
                        }
                    } elseif (isset($mem['SMBIOSMemoryType'])) {
                        switch ($mem['SMBIOSMemoryType']) {
//                        case 0: $memtype = 'Invalid'; break;
//                        case 1: $memtype = 'Other'; break;
//                        case 2: $memtype = 'Unknown'; break;
                        case 3: $memtype = 'DRAM'; break;
                        case 4: $memtype = 'EDRAM'; break;
                        case 5: $memtype = 'VRAM'; break;
                        case 6: $memtype = 'SRAM'; break;
                        case 7: $memtype = 'RAM'; break;
                        case 8: $memtype = 'ROM'; break;
                        case 9: $memtype = 'FLASH'; break;
                        case 10: $memtype = 'EEPROM'; break;
                        case 11: $memtype = 'FEPROM'; break;
                        case 12: $memtype = 'EPROM'; break;
                        case 13: $memtype = 'CDRAM'; break;
                        case 14: $memtype = '3DRAM'; break;
                        case 15: $memtype = 'SDRAM'; break;
                        case 16: $memtype = 'SGRAM'; break;
                        case 17: $memtype = 'RDRAM'; break;
                        case 18: $memtype = 'DDR'; break;
                        case 19: $memtype = 'DDR2'; break;
                        case 20: $memtype = 'DDR2 FB-DIMM'; break;
                        case 24: $memtype = 'DDR3'; break;
                        case 25: $memtype = 'FBD2'; break;
                        case 26: $memtype = 'DDR4'; break;
                        case 27: $memtype = 'LPDDR'; break;
                        case 28: $memtype = 'LPDDR2'; break;
                        case 29: $memtype = 'LPDDR3'; break;
                        case 30: $memtype = 'DDR3'; break;
                        case 31: $memtype = 'FBD2'; break;
                        case 32: $memtype = 'Logical non-volatile device'; break;
                        case 33: $memtype = 'HBM2'; break;
                        case 34: $memtype = 'DDR5'; break;
                        case 35: $memtype = 'LPDDR5';
                        }
                    }
                    if (isset($mem['Speed']) && (($speed = $mem['Speed']) > 0) && (preg_match('/^(DDR\d*)(.*)/', $memtype, $dr) || preg_match('/^(SDR)AM(.*)/', $memtype, $dr))) {
                        if (isset($mem['MinVoltage']) && isset($mem['MaxVoltage']) && (($minv = $mem['MinVoltage']) > 0) && (($maxv = $mem['MaxVoltage']) > 0) && ($minv < $maxv)) {
                            $lv = 'L';
                        } else {
                            $lv = '';
                        }
                        if (isset($dr[2])) {
                            $memtype = $dr[1].$lv.'-'.$speed.' '.$dr[2];
                        } else {
                            $memtype = $dr[1].$lv.'-'.$speed;
                        }
                    }
                    if (isset($mem['FormFactor'])) {
                        switch ($mem['FormFactor']) {
//                        case 0: $memtype .= ' Unknown'; break;
//                        case 1: $memtype .= ' Other'; break;
                        case 2: $memtype .= ' SIP'; break;
                        case 3: $memtype .= ' DIP'; break;
                        case 4: $memtype .= ' ZIP'; break;
                        case 5: $memtype .= ' SOJ'; break;
                        case 6: $memtype .= ' Proprietary'; break;
                        case 7: $memtype .= ' SIMM'; break;
                        case 8: $memtype .= ' DIMM'; break;
                        case 9: $memtype .= ' TSOPO'; break;
                        case 10: $memtype .= ' PGA'; break;
                        case 11: $memtype .= ' RIM'; break;
                        case 12: $memtype .= ' SODIMM'; break;
                        case 13: $memtype .= ' SRIMM'; break;
                        case 14: $memtype .= ' SMD'; break;
                        case 15: $memtype .= ' SSMP'; break;
                        case 16: $memtype .= ' QFP'; break;
                        case 17: $memtype .= ' TQFP'; break;
                        case 18: $memtype .= ' SOIC'; break;
                        case 19: $memtype .= ' LCC'; break;
                        case 20: $memtype .= ' PLCC'; break;
                        case 21: $memtype .= ' BGA'; break;
                        case 22: $memtype .= ' FPBGA'; break;
                        case 23: $memtype .= ' LGA';
                        }
                    }
                    if (isset($mem['DataWidth']) && isset($mem['TotalWidth']) && (($dataw = $mem['DataWidth']) > 0) && (($totalw = $mem['TotalWidth']) > 0) && ($dataw < $totalw)) {
                        $memtype .= ' ECC';
                    }
                    if ($reg) {
                        $memtype .= ' REG';
                    }
                    if (($memtype = trim($memtype)) != '') {
                        $dev->setProduct($memtype);
                    }
                    if (isset($mem['ConfiguredClockSpeed']) && (($clock = $mem['ConfiguredClockSpeed']) > 0)) {
                        $dev->setSpeed($clock);
                    }
                    if (isset($mem['ConfiguredVoltage']) && (($voltage = $mem['ConfiguredVoltage']) > 0)) {
                        $dev->setVoltage($voltage/1000);
                    }
                    if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL &&
                       isset($mem['SerialNumber']) && !preg_match("/^SerNum\d+$/", $serial = $mem['SerialNumber']) && ($serial != '') && ($serial != 'None')) {
                        $dev->setSerial($serial);
                    }
                }
                $this->sys->setMemDevices($dev);
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
        $this->_distro(); // share getDistribution()
        if (substr($this->sys->getDistribution(), 0, 7)=="ReactOS") {
            $this->error->addWarning("The ReactOS version of phpSysInfo is a work in progress, some things currently don't work");
        }
        if (!$this->blockname || $this->blockname==='vitals') {
            $this->_hostname();
            $this->_users();
            $this->_uptime();
            $this->_loadavg();
            $this->_processes();
        }
        if (!$this->blockname || $this->blockname==='hardware') {
            $this->_machine();
            $this->_cpuinfo();
            $this->_virtualizer();
            $this->_meminfo();
            $this->_hardware();
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
    }
}
