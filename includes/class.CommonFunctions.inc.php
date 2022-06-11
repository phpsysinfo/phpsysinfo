<?php
/**
 * common Functions class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.CommonFunctions.inc.php 699 2012-09-15 11:57:13Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class with common functions used in all places
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class CommonFunctions
{
    /**
     * holds dmi memory data
     *
     * @var array
     */
    private static $_dmimd = null;

    private static function _parse_log_file($string)
    {
        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && ((substr(PSI_LOG, 0, 1)=="-") || (substr(PSI_LOG, 0, 1)=="+"))) {
            $log_file = substr(PSI_LOG, 1);
            if (file_exists($log_file)) {
                $contents = @file_get_contents($log_file);
                if ($contents && preg_match("/^\-\-\-[^-\r\n]+\-\-\- ".preg_quote($string, '/')."\r?\n/m", $contents, $matches, PREG_OFFSET_CAPTURE)) {
                    $findIndex = $matches[0][1];
                    if (preg_match("/\r?\n/m", $contents, $matches, PREG_OFFSET_CAPTURE, $findIndex)) {
                        $startIndex = $matches[0][1]+1;
                        if (preg_match("/^\-\-\-[^-\r\n]+\-\-\- /m", $contents, $matches, PREG_OFFSET_CAPTURE, $startIndex)) {
                            $stopIndex = $matches[0][1];

                            return substr($contents, $startIndex, $stopIndex-$startIndex);
                        } else {
                            return substr($contents, $startIndex);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find a system program, do also path checking when not running on WINNT
     * on WINNT we simply return the name with the exe extension to the program name
     *
     * @param string $strProgram name of the program
     *
     * @return string|null complete path and name of the program
     */
    public static function _findProgram($strProgram)
    {
        $path_parts = pathinfo($strProgram);
        if (empty($path_parts['basename'])) {
            return null;
        }
        $arrPath = array();

        if (empty($path_parts['dirname']) || ($path_parts['dirname'] == '.')) {
            if ((PSI_OS == 'WINNT') && empty($path_parts['extension'])) {
                $strProgram .= '.exe';
                $path_parts = pathinfo($strProgram);
            }
            if (PSI_OS == 'WINNT') {
                if (self::readenv('Path', $serverpath)) {
                    $arrPath = preg_split('/;/', $serverpath, -1, PREG_SPLIT_NO_EMPTY);
                }
            } else {
                if (self::readenv('PATH', $serverpath)) {
                    $arrPath = preg_split('/:/', $serverpath, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
            if (defined('PSI_UNAMEO') && (PSI_UNAMEO === 'Android') && !empty($arrPath)) {
                array_push($arrPath, '/system/bin'); // Termux patch
            }
            if (defined('PSI_ADD_PATHS') && is_string(PSI_ADD_PATHS)) {
                if (preg_match(ARRAY_EXP, PSI_ADD_PATHS)) {
                    $arrPath = array_merge(eval(PSI_ADD_PATHS), $arrPath); // In this order so $addpaths is before $arrPath when looking for a program
                } else {
                    $arrPath = array_merge(array(PSI_ADD_PATHS), $arrPath); // In this order so $addpaths is before $arrPath when looking for a program
                }
            }
        } else { //directory defined
            array_push($arrPath, $path_parts['dirname']);
            $strProgram = $path_parts['basename'];
        }

        //add some default paths if we still have no paths here
        if (empty($arrPath) && (PSI_OS != 'WINNT')) {
            if (PSI_OS == 'Android') {
                array_push($arrPath, '/system/bin');
            } else {
                array_push($arrPath, '/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
            }
        }

        $exceptPath = "";
        if ((PSI_OS == 'WINNT') && self::readenv('WinDir', $windir)) {
            foreach ($arrPath as $strPath) {
                if ((strtolower($strPath) == strtolower($windir)."\\system32") && is_dir($windir."\\SysWOW64")) {
                    if (is_dir($windir."\\sysnative\\drivers")) { // or strlen(decbin(~0)) == 32; is_dir($windir."\\sysnative") sometimes does not work
                        $exceptPath = $windir."\\sysnative"; //32-bit PHP on 64-bit Windows
                    } else {
                        $exceptPath = $windir."\\SysWOW64"; //64-bit PHP on 64-bit Windows
                    }
                    array_push($arrPath, $exceptPath);
                    break;
                }
            }
        } elseif (PSI_OS == 'Android') {
            $exceptPath = '/system/bin';
        }

        foreach ($arrPath as $strPath) {
            // Path with and without trailing slash
            if (PSI_OS == 'WINNT') {
                $strPath = rtrim($strPath, "\\");
                $strPathS = $strPath."\\";
            } else {
                $strPath = rtrim($strPath, "/");
                $strPathS = $strPath."/";
            }
            if (($strPath !== $exceptPath) && !is_dir($strPath)) {
                continue;
            }
            $strProgrammpath = $strPathS.$strProgram;
            if (is_executable($strProgrammpath) || ((PSI_OS == 'WINNT') && (strtolower($path_parts['extension']) == 'py') && is_file($strProgrammpath))) {
                return $strProgrammpath;
            }
        }

        return null;
    }

    /**
     * Execute a system program. return a trim()'d result.
     * does very crude pipe and multiple commands (on WinNT) checking.  you need ' | ' or ' & ' for it to work
     * ie $program = CommonFunctions::executeProgram('netstat', '-anp | grep LIST');
     * NOT $program = CommonFunctions::executeProgram('netstat', '-anp|grep LIST');
     *
     * @param string  $strProgramname name of the program
     * @param string  $strArguments   arguments to the program
     * @param string  &$strBuffer     output of the command
     * @param boolean $booErrorRep    en- or disables the reporting of errors which should be logged
     * @param int     $timeout        timeout value in seconds (default value is PSI_EXEC_TIMEOUT_INT)
     *
     * @return boolean command successfull or not
     */
    public static function executeProgram($strProgramname, $strArguments, &$strBuffer, $booErrorRep = true, $timeout = PSI_EXEC_TIMEOUT_INT, $separator = '')
    {
        if (PSI_ROOT_FILESYSTEM !== '') { // disabled if ROOTFS defined

            return false;
        }

        if ((PSI_OS != 'WINNT') && preg_match('/^([^=]+=[^ \t]+)[ \t]+(.*)$/', $strProgramname, $strmatch)) {
            $strSet = $strmatch[1].' ';
            $strProgramname = $strmatch[2];
        } else {
            $strSet = '';
        }
        $strAll = trim($strSet.$strProgramname.' '.$strArguments);

        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && ((substr(PSI_LOG, 0, 1)=="-") || (substr(PSI_LOG, 0, 1)=="+"))) {
            $out = self::_parse_log_file("Executing: ".$strAll);
            if ($out == false) {
                if (substr(PSI_LOG, 0, 1)=="-") {
                    $strBuffer = '';

                    return false;
                }
            } else {
                $strBuffer = $out;

                return true;
            }
        }

        $PathStr = '';
        if (defined('PSI_EMU_PORT') && !in_array($strProgramname, array('ping', 'snmpwalk'))) {
            if (defined('PSI_SUDO_COMMANDS') && is_string(PSI_SUDO_COMMANDS)) {
                if (preg_match(ARRAY_EXP, PSI_SUDO_COMMANDS)) {
                    $sudocommands = eval(PSI_SUDO_COMMANDS);
                } else {
                    $sudocommands = array(PSI_SUDO_COMMANDS);
                }
                if (in_array($strProgramname, $sudocommands)) {
                    $strAll = 'sudo '.$strAll;
                }
            }
            $strSet = '';
            $strProgramname = 'sshpass';
            $strOptions = '';
            if (defined('PSI_EMU_ADD_OPTIONS') && is_string(PSI_EMU_ADD_OPTIONS)) {
                if (preg_match(ARRAY_EXP, PSI_EMU_ADD_OPTIONS)) {
                    $arrParams = eval(PSI_EMU_ADD_OPTIONS);
                } else {
                    $arrParams = array(PSI_EMU_ADD_OPTIONS);
                }
                foreach ($arrParams as $Params) if (preg_match('/(\S+)\s*\=\s*(\S+)/', $Params, $obuf)) {
                    $strOptions = $strOptions.'-o '.$obuf[1].'='.$obuf[2].' ';
                }
            }
            if (defined('PSI_EMU_ADD_PATHS') && is_string(PSI_EMU_ADD_PATHS)) {
                if (preg_match(ARRAY_EXP, PSI_EMU_ADD_PATHS)) {
                    $arrPath = eval(PSI_EMU_ADD_PATHS);
                } else {
                    $arrPath = array(PSI_EMU_ADD_PATHS);
                }
                foreach ($arrPath as $Path) {
                    if ($PathStr === '') {
                        $PathStr = $Path;
                    } else {
                        $PathStr = $PathStr.':'.$Path;
                    }
                }
                if ($separator === '') {
                    $strArguments = '-e ssh -Tq '.$strOptions.'-o ConnectTimeout='.$timeout.' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT.' "PATH=\''.$PathStr.':$PATH\' '.$strAll.'"' ;
                } else {
                    $strArguments = '-e ssh -Tq '.$strOptions.'-o ConnectTimeout='.$timeout.' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT;
                }
            } else {
                if ($separator === '') {
                    $strArguments = '-e ssh -Tq '.$strOptions.'-o ConnectTimeout='.$timeout.' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT.' "'.$strAll.'"' ;
                } else {
                    $strArguments = '-e ssh -Tq '.$strOptions.'-o ConnectTimeout='.$timeout.' -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null '.PSI_EMU_USER.'@'.PSI_EMU_HOSTNAME.' -p '.PSI_EMU_PORT;
                }
            }
            $externally = true;
        } else {
            $externally = false;
        }

        $strProgram = self::_findProgram($strProgramname);
        $error = PSI_Error::singleton();
        if (!$strProgram) {
            if ($booErrorRep || $externally) {
                $error->addError('find_program("'.$strProgramname.'")', 'program not found on the machine');
            }

            return false;
        } else {
            if (preg_match('/\s/', $strProgram)) {
                $strProgram = '"'.$strProgram.'"';
            }
        }

        if ((PSI_OS != 'WINNT') && !defined('PSI_EMU_HOSTNAME') && defined('PSI_SUDO_COMMANDS') && is_string(PSI_SUDO_COMMANDS)) {
            if (preg_match(ARRAY_EXP, PSI_SUDO_COMMANDS)) {
                $sudocommands = eval(PSI_SUDO_COMMANDS);
            } else {
                $sudocommands = array(PSI_SUDO_COMMANDS);
            }
            if (in_array($strProgramname, $sudocommands)) {
                $sudoProgram = self::_findProgram("sudo");
                if (!$sudoProgram) {
                    $error->addError('find_program("sudo")', 'program not found on the machine');

                    return false;
                } else {
                    if (preg_match('/\s/', $sudoProgram)) {
                        $strProgram = '"'.$sudoProgram.'" '.$strProgram;
                    } else {
                        $strProgram = $sudoProgram.' '.$strProgram;
                    }
                }
            }
        }

        $strArgs = $strArguments;
        // see if we've gotten a | or &, if we have we need to do path checking on the cmd
        if ($strArgs) {
            $arrArgs = preg_split('/ /', $strArgs, -1, PREG_SPLIT_NO_EMPTY);
            for ($i = 0, $cnt_args = count($arrArgs); $i < $cnt_args; $i++) {
                if (($arrArgs[$i] == '|') || ($arrArgs[$i] == '&')) {
                    $strCmd = $arrArgs[$i + 1];
                    $strNewcmd = self::_findProgram($strCmd);
                    if (!$strNewcmd) {
                        if ($booErrorRep || $externally) {
                            $error->addError('find_program("'.$strCmd.'")', 'program not found on the machine');
                        }

                        return false;
                    }
                    if (preg_match('/\s/', $sudoProgram)) {
                        if ($arrArgs[$i] == '|') {
                            $strArgs = preg_replace('/\| '.$strCmd.'/', '| "'.$strNewcmd.'"', $strArgs);
                        } else {
                            $strArgs = preg_replace('/& '.$strCmd.'/', '& "'.$strNewcmd.'"', $strArgs);
                        }
                    } else {
                        if ($arrArgs[$i] == '|') {
                            $strArgs = preg_replace('/\| '.$strCmd.'/', '| '.$strNewcmd, $strArgs);
                        } else {
                            $strArgs = preg_replace('/& '.$strCmd.'/', '& '.$strNewcmd, $strArgs);
                        }
                    }
                }
            }
            $strArgs = ' '.$strArgs;
        }

        $strBuffer = '';
        $strError = '';
        $pipes = array();
        $descriptorspec = array(0=>array("pipe", "r"), 1=>array("pipe", "w"), 2=>array("pipe", "w"));
        if ($externally) {
            putenv('SSHPASS='.PSI_EMU_PASSWORD);
        }
        if (defined("PSI_MODE_POPEN") && PSI_MODE_POPEN) {
            if ($separator !== '') {
                $error->addError('executeProgram', 'wrong execution mode');

                return false;
            }
            if (PSI_OS == 'WINNT') {
                $process = $pipes[1] = popen($strSet.$strProgram.$strArgs." 2>nul", "r");
            } else {
                $process = $pipes[1] = popen($strSet.$strProgram.$strArgs." 2>/dev/null", "r");
            }
        } else {
            $process = proc_open($strSet.$strProgram.$strArgs, $descriptorspec, $pipes);
            if ($separator !== '') {
                if ($PathStr === '') {
                    fwrite($pipes[0], $strAll."\n  "); // spaces at end for handling 'more'
                } else {
                    fwrite($pipes[0], 'PATH=\''.$PathStr.':$PATH\' '.$strAll."\n");
                }
            }
        }
        if ($externally) {
            putenv('SSHPASS');
        }
        if (is_resource($process)) {
            $te = self::_timeoutfgets($pipes, $strBuffer, $strError, $timeout, $separator);
            if (defined("PSI_MODE_POPEN") && PSI_MODE_POPEN) {
                $return_value = pclose($pipes[1]);
            } else {
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock
                if ($te) {
                    proc_terminate($process); // proc_close tends to hang if the process is timing out
                    $return_value = 0;
                } else {
                    $return_value = proc_close($process);
                }
            }
        } else {
            if ($booErrorRep) {
                $error->addError($strProgram, "\nOpen process error");
            }

            return false;
        }
        $strError = trim($strError);
        $strBuffer = trim($strBuffer);
        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && (substr(PSI_LOG, 0, 1)!="-") && (substr(PSI_LOG, 0, 1)!="+")) {
            error_log("---".gmdate('r T')."--- Executing: ".$strAll."\n".$strBuffer."\n", 3, PSI_LOG);
        }
        if (! empty($strError)) {
            if ($booErrorRep) {
                $error->addError($strProgram, $strError."\nReturn value: ".$return_value);
            }

            return $return_value == 0;
        }

        return true;
    }

    /**
     * read a one-line value from a file with a similar name
     *
     * @return value if successfull or null if not
     */
    public static function rolv($similarFileName, $match = "//", $replace = "")
    {
        if (defined('PSI_EMU_PORT')) {
            return null;
        }

        $filename = preg_replace($match, $replace, $similarFileName);
        if (self::fileexists($filename) && self::rfts($filename, $buf, 1, 4096, false) && (($buf=trim($buf)) != "")) {
            return $buf;
        } else {
            return null;
        }
    }

    /**
     * read data from array $_SERVER
     *
     * @param string $strElem    element of array
     * @param string &$strBuffer output of the command
     *
     * @return string
     */
    public static function readenv($strElem, &$strBuffer)
    {
        $strBuffer = '';
        if (PSI_OS == 'WINNT') { //case insensitive
            if (isset($_SERVER)) {
                foreach ($_SERVER as $index=>$value) {
                    if (is_string($value) && (trim($value) !== '') && (strtolower($index) === strtolower($strElem))) {
                        $strBuffer = $value;

                        return true;
                    }
                }
            }
        } else {
            if (isset($_SERVER[$strElem]) && is_string($value = $_SERVER[$strElem]) && (trim($value) !== '')) {
                $strBuffer = $value;

                return true;
            }
        }

        return false;
    }

    /**
     * read a file and return the content as a string
     *
     * @param string  $strFileName name of the file which should be read
     * @param string  &$strRet     content of the file (reference)
     * @param int     $intLines    control how many lines should be read
     * @param int     $intBytes    control how many bytes of each line should be read
     * @param boolean $booErrorRep en- or disables the reporting of errors which should be logged
     *
     * @return boolean command successfull or not
     */
    public static function rfts($strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true)
    {
        if (defined('PSI_EMU_PORT')) {
            return false;
        }

        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && ((substr(PSI_LOG, 0, 1)=="-") || (substr(PSI_LOG, 0, 1)=="+"))) {
            $out = self::_parse_log_file("Reading: ".$strFileName);
            if ($out == false) {
                if (substr(PSI_LOG, 0, 1)=="-") {
                    $strRet = '';

                    return false;
                }
            } else {
                $strRet = $out;

                return true;
            }
        }

        if (PSI_ROOT_FILESYSTEM !== '') {
            $rfsinfo = "[".PSI_ROOT_FILESYSTEM."]";
        } else {
            $rfsinfo = '';
        }

        $strFile = "";
        $intCurLine = 1;
        $error = PSI_Error::singleton();
        if (file_exists(PSI_ROOT_FILESYSTEM.$strFileName)) {
            if (is_readable(PSI_ROOT_FILESYSTEM.$strFileName)) {
                if ($fd = fopen(PSI_ROOT_FILESYSTEM.$strFileName, 'r')) {
                    while (!feof($fd)) {
                        $strFile .= fgets($fd, $intBytes);
                        if ($intLines <= $intCurLine && $intLines != 0) {
                            break;
                        } else {
                            $intCurLine++;
                        }
                    }
                    fclose($fd);
                    $strRet = $strFile;
                    if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && (substr(PSI_LOG, 0, 1)!="-") && (substr(PSI_LOG, 0, 1)!="+")) {
                        if ((strlen($strRet)>0)&&(substr($strRet, -1)!="\n")) {
                            error_log("---".gmdate('r T')."--- Reading: ".$strFileName."\n".$strRet."\n", 3, PSI_LOG);
                        } else {
                            error_log("---".gmdate('r T')."--- Reading: ".$strFileName."\n".$strRet, 3, PSI_LOG);
                        }
                    }
                } else {
                    if ($booErrorRep) {
                        $error->addError('fopen('.$rfsinfo.$strFileName.')', 'file can not read by phpsysinfo');
                    }

                    return false;
                }
            } else {
                if ($booErrorRep) {
                    $error->addError('fopen('.$rfsinfo.$strFileName.')', 'file permission error');
                }

                return false;
            }
        } else {
            if ($booErrorRep) {
                $error->addError('file_exists('.$rfsinfo.$strFileName.')', 'the file does not exist on your machine');
            }

            return false;
        }

        return true;
    }

    /**
     * read a data file and return the content as a string
     *
     * @param string $strDataFileName name of the data file which should be read
     * @param string &$strRet         content of the data file (reference)
     *
     * @return boolean command successfull or not
     */
    public static function rftsdata($strDataFileName, &$strRet)
    {
        $strFile = "";
        $strFileName = PSI_APP_ROOT."/data/".$strDataFileName;
        $error = PSI_Error::singleton();
        if (file_exists($strFileName)) {
            if (is_readable($strFileName)) {
                if ($fd = fopen($strFileName, 'r')) {
                    while (!feof($fd)) {
                        $strFile .= fgets($fd, 4096);
                    }
                    fclose($fd);
                    $strRet = $strFile;
                } else {
                    $error->addError('fopen('.$strFileName.')', 'file can not read by phpsysinfo');

                    return false;
                }
            } else {
                $error->addError('fopen('.$strFileName.')', 'file permission error');

                return false;
            }
        } else {
            $error->addError('file_exists('.$strFileName.')', 'the file does not exist on your machine');

            return false;
        }

        return true;
    }

    /**
     * Find pathnames matching a pattern
     *
     * @param string $pattern the pattern. No tilde expansion or parameter substitution is done.
     * @param int    $flags
     *
     * @return an array containing the matched files/directories, an empty array if no file matched or false on error
     */
    public static function findglob($pattern, $flags = 0)
    {
        if (defined('PSI_EMU_PORT')) {
            return false;
        }

        $outarr = glob(PSI_ROOT_FILESYSTEM.$pattern, $flags);
        if (PSI_ROOT_FILESYSTEM == '') {
            return $outarr;
        } elseif ($outarr === false) {
            return false;
        } else {
            $len = strlen(PSI_ROOT_FILESYSTEM);
            $newoutarr = array();
            foreach ($outarr as $out) {
                $newoutarr[] = substr($out, $len); // path without ROOTFS
            }

            return $newoutarr;
        }
    }

    /**
     * file exists
     *
     * @param string $strFileName name of the file which should be check
     *
     * @return boolean command successfull or not
     */
    public static function fileexists($strFileName)
    {
        if (defined('PSI_EMU_PORT')) {
            return false;
        }

        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && ((substr(PSI_LOG, 0, 1)=="-") || (substr(PSI_LOG, 0, 1)=="+"))) {
            $log_file = substr(PSI_LOG, 1);
            if (file_exists($log_file)
                && ($contents = @file_get_contents($log_file))
                && preg_match("/^\-\-\-[^-\n]+\-\-\- ".preg_quote("Reading: ".$strFileName, '/')."\n/m", $contents)) {
                return true;
            } else {
                if (substr(PSI_LOG, 0, 1)=="-") {
                    return false;
                }
            }
        }

        $exists =  file_exists(PSI_ROOT_FILESYSTEM.$strFileName);
        if (defined('PSI_LOG') && is_string(PSI_LOG) && (strlen(PSI_LOG)>0) && (substr(PSI_LOG, 0, 1)!="-") && (substr(PSI_LOG, 0, 1)!="+")) {
            if ((substr($strFileName, 0, 5) === "/dev/") && $exists) {
                error_log("---".gmdate('r T')."--- Reading: ".$strFileName."\ndevice exists\n", 3, PSI_LOG);
            }
        }

        return $exists;
    }

    /**
     * reads a directory and return the name of the files and directorys in it
     *
     * @param string  $strPath     path of the directory which should be read
     * @param boolean $booErrorRep en- or disables the reporting of errors which should be logged
     *
     * @return array content of the directory excluding . and ..
     */
    public static function gdc($strPath, $booErrorRep = true)
    {
        $arrDirectoryContent = array();
        $error = PSI_Error::singleton();
        if (is_dir($strPath)) {
            if ($handle = opendir($strPath)) {
                while (($strFile = readdir($handle)) !== false) {
                    if ($strFile != "." && $strFile != "..") {
                        $arrDirectoryContent[] = $strFile;
                    }
                }
                closedir($handle);
            } else {
                if ($booErrorRep) {
                    $error->addError('opendir('.$strPath.')', 'directory can not be read by phpsysinfo');
                }
            }
        } else {
            if ($booErrorRep) {
                $error->addError('is_dir('.$strPath.')', 'directory does not exist on your machine');
            }
        }

        return $arrDirectoryContent;
    }

    /**
     * Check for needed php extensions
     *
     * We need that extensions for almost everything
     * This function will return a hard coded
     * XML string (with headers) if the SimpleXML extension isn't loaded.
     * Then it will terminate the script.
     * See bug #1787137
     *
     * @param array $arrExt additional extensions for which a check should run
     *
     * @return void
     */
    public static function checkForExtensions($arrExt = array())
    {
        if (defined('PSI_SYSTEM_CODEPAGE') && (PSI_SYSTEM_CODEPAGE !== null) && ((strcasecmp(PSI_SYSTEM_CODEPAGE, "UTF-8") == 0) || (strcasecmp(PSI_SYSTEM_CODEPAGE, "CP437") == 0)))
            $arrReq = array('simplexml', 'pcre', 'xml', 'dom');
        elseif (PSI_OS == 'WINNT')
            $arrReq = array('simplexml', 'pcre', 'xml', 'dom', 'mbstring', 'com_dotnet');
        else
            $arrReq = array('simplexml', 'pcre', 'xml', 'dom', 'mbstring');
        $extensions = array_merge($arrExt, $arrReq);
        $text = "";
        $error = false;
        $text .= "<?xml version='1.0'?>\n";
        $text .= "<phpsysinfo>\n";
        $text .= "  <Error>\n";
        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $text .= "    <Function>checkForExtensions</Function>\n";
                $text .= "    <Message>phpSysInfo requires the ".$extension." extension to php in order to work properly.</Message>\n";
                $error = true;
            }
        }
        $text .= "  </Error>\n";
        $text .= "</phpsysinfo>";
        if ($error) {
            header('Content-Type: text/xml');
            echo $text;
            die();
        }
    }

    /**
     * get the content of stdout/stderr with the option to set a timeout for reading
     *
     * @param array  $pipes   array of file pointers for stdin, stdout, stderr (proc_open())
     * @param string &$out    target string for the output message (reference)
     * @param string &$err    target string for the error message (reference)
     * @param int    $timeout timeout value in seconds
     *
     * @return boolean timeout expired or not
     */
    private static function _timeoutfgets($pipes, &$out, &$err, $timeout, $separator = '')
    {
        $w = null;
        $e = null;
        $te = false;

        if (defined("PSI_MODE_POPEN") && PSI_MODE_POPEN) {
            $pipe2 = false;
        } else {
            $pipe2 = true;
        }
        while (!(feof($pipes[1]) && (!$pipe2 || feof($pipes[2])))) {
            if ($pipe2) {
                $read = array($pipes[1], $pipes[2]);
            } else {
                $read = array($pipes[1]);
            }

            $n = stream_select($read, $w, $e, $timeout);

            if ($n === false) {
                error_log('stream_select: failed !');
                break;
            } elseif ($n === 0) {
                error_log('stream_select: timeout expired !');
//                if ($separator !== '') {
//                    fwrite($pipes[0], "q");
//                }
                $te = true;
                break;
            }

            foreach ($read as $r) {
                if ($r == $pipes[1]) {
                    $out .= fread($r, 4096);
                } elseif (feof($pipes[1]) && $pipe2 && ($r == $pipes[2])) {//read STDERR after STDOUT
                    $err .= fread($r, 4096);
                }
            }
//            if (($separator !== '') && preg_match('/'.$separator.'[^'.$separator.']+'.$separator.'/', $out)) {
            if (($separator !== '') && preg_match('/'.$separator.'[\s\S]+'.$separator.'/', $out)) {
                fwrite($pipes[0], "quit\n");
                $separator = ''; //only one time
              //  $te = true;
              //  break;
            }
        }

        return $te;
    }

    /**
     * get all configured plugins from phpsysinfo.ini (file must be included and processed before calling this function)
     *
     * @return array
     */
    public static function getPlugins()
    {
        if (defined('PSI_PLUGINS') && is_string(PSI_PLUGINS)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGINS)) {
                return eval(strtolower(PSI_PLUGINS));
            } else {
                return array(strtolower(PSI_PLUGINS));
            }
        } else {
            return array();
        }
    }

    /**
     * name natural compare function
     *
     * @return comprasion result
     */
    public static function name_natural_compare($a, $b)
    {
        return strnatcmp($a->getName(), $b->getName());
    }

    /**
     * get virtualizer from dmi data
     *
     * @return string|null
     */
    public static function decodevirtualizer($vendor_data)
    {
        if (gettype($vendor_data) === "array") {
            $vendarray = array(
                'KVM' => 'kvm', // KVM
                'OpenStack' => 'kvm', // KVM
                'Amazon EC2' => 'amazon', // Amazon EC2 Nitro using Linux KVM
                'QEMU' => 'qemu', // QEMU
                'VMware' => 'vmware', // VMware https://kb.vmware.com/s/article/1009458
                'VMW' => 'vmware',
                'innotek GmbH' => 'oracle', // Oracle VM VirtualBox
                'VirtualBox' => 'oracle',
                'Xen' => 'xen', // Xen hypervisor
                'Bochs' => 'bochs', // Bochs
                'Parallels' => 'parallels', // Parallels
                // https://wiki.freebsd.org/bhyve
                'BHYVE' => 'bhyve', // bhyve
                'Hyper-V' => 'microsoft', // Hyper-V
                'Microsoft Corporation Virtual Machine' => 'microsoft' // Hyper-V
            );
            for ($i = 0; $i < count($vendor_data); $i++) {
                foreach ($vendarray as $vend=>$virt) {
                    if (preg_match('/^'.$vend.'/', $vendor_data[$i])) {
                        return $virt;
                    }
                }
            }
        } elseif (gettype($vendor_data) === "string") {
            $vidarray = array(
                'bhyvebhyve' => 'bhyve', // bhyve
                'KVMKVMKVM' => 'kvm', // KVM
                'LinuxKVMHv' => 'hv-kvm', // KVM (KVM + HyperV Enlightenments)
                'MicrosoftHv' => 'microsoft', // Hyper-V
                'lrpepyhvr' => 'parallels', // Parallels
                'UnisysSpar64' => 'spar', // Unisys sPar
                'VMwareVMware' => 'vmware', // VMware
                'XenVMMXenVMM' => 'xen', // Xen hypervisor
                'ACRNACRNACRN' => 'acrn', // ACRN hypervisor
                'TCGTCGTCGTCG' => 'qemu', // QEMU
                'QNXQVMBSQG' => 'qnx', // QNX hypervisor
                'VBoxVBoxVBox' => 'oracle' // Oracle VM VirtualBox
            );
            $shortvendorid = trim(preg_replace('/[\s!\.]/', '', $vendor_data));
            if (($shortvendorid !== "") && isset($vidarray[$shortvendorid])) {
                return $vidarray[$shortvendorid];
            }
        }

        return null;
    }


    /**
     * readdmimemdata function
     *
     * @return array
     */
    public static function readdmimemdata()
    {
        if ((PSI_OS != 'WINNT') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) && (self::$_dmimd === null)) {
            self::$_dmimd = array();
            $buffer = '';
            if (defined('PSI_DMIDECODE_ACCESS') && (strtolower(PSI_DMIDECODE_ACCESS)==='data')) {
                self::rftsdata('dmidecode.tmp', $buffer);
            } elseif (self::_findProgram('dmidecode')) {
                self::executeProgram('dmidecode', '-t 17', $buffer, PSI_DEBUG);
            }
            if (!empty($buffer)) {
                $banks = preg_split('/^(?=Handle\s)/m', $buffer, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($banks as $bank) if (preg_match('/^Handle\s/', $bank)) {
                    $lines = preg_split("/\n/", $bank, -1, PREG_SPLIT_NO_EMPTY);
                    $mem = array();
                    foreach ($lines as $line) if (preg_match('/^\s+([^:]+):(.+)/', $line, $params)) {
                        if (preg_match('/^0x([A-F\d]+)/', $params2 = trim($params[2]), $buff)) {
                            $mem[trim($params[1])] = trim($buff[1]);
                        } elseif ($params2 != '') {
                            $mem[trim($params[1])] = $params2;
                        }
                    }
                    if (!empty($mem)) {
                        self::$_dmimd[] = $mem;
                    }
                }
            }
        }

        return self::$_dmimd;
    }
}
