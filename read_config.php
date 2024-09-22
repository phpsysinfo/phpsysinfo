<?php
if (!defined('PSI_CONFIG_FILE')) {
    /**
     * phpSysInfo version
     */
    define('PSI_VERSION', '3.4.4');
    /**
     * phpSysInfo configuration
     */
    define('PSI_CONFIG_FILE', PSI_APP_ROOT.'/phpsysinfo.ini');

    define('ARRAY_EXP', '/^return array \([^;]*\);$/'); //array expression search

    if (!is_readable(PSI_CONFIG_FILE)) {
        echo "ERROR: phpsysinfo.ini does not exist or is not readable by the webserver in the phpsysinfo directory";
        die();
    } elseif (!($config = @parse_ini_file(PSI_CONFIG_FILE, true))) {
        echo "ERROR: phpsysinfo.ini file is not parsable";
        die();
    } else {
        foreach ($config as $name=>$group) {
            if (strtoupper($name)=="MAIN") {
                $name_prefix='PSI_';
            } elseif (strtoupper(substr($name, 0, 7))=="SENSOR_") {
                $name_prefix='PSI_'.strtoupper($name).'_';
            } else {
                $name_prefix='PSI_PLUGIN_'.strtoupper($name).'_';
            }
            foreach ($group as $param=>$value) {
                if ((trim($value)==="") || (trim($value)==="0")) {
                    define($name_prefix.strtoupper($param), false);
                } elseif (trim($value)==="1") {
                    define($name_prefix.strtoupper($param), true);
                } else {
                    if ((($paramup = strtoupper($param)) !== 'WMI_PASSWORD') && ($paramup !== 'SSH_PASSWORD') && strstr($value, ',')) {
                        define($name_prefix.$paramup, 'return '.var_export(preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY), 1).';');
                    } else {
                        define($name_prefix.$paramup, trim($value));
                    }
                }
            }
        }
    }

    if (defined('PSI_ALLOWED') && is_string(PSI_ALLOWED)) {
        if (preg_match(ARRAY_EXP, PSI_ALLOWED)) {
            $allowed = eval(strtolower(PSI_ALLOWED));
        } else {
            $allowed = array(strtolower(PSI_ALLOWED));
        }

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        }
        $ip = preg_replace("/^::ffff:/", "", strtolower($ip));

        $ip_decimal = ip2long($ip);
        if ($ip_decimal === false) {
            echo "Client IP wrong address (".$ip."). Client not allowed.";
            die();
        }

        // code based on https://gist.github.com/tott/7684443
        $was = false;
        foreach ($allowed as $allow) {
            if (strpos($allow, '/') === false) {
                    $was = ($allow === $ip);
            } else {
                  list($allow, $netmask) = explode('/', $allow, 2);
                  $allow_decimal = ip2long($allow);
                  $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
                  $netmask_decimal = ~$wildcard_decimal;
                $was = (($ip_decimal & $netmask_decimal) === ($allow_decimal & $netmask_decimal));
            }
            if ($was) {
               break;
            }
        }

        if (!$was) {
            echo "Client IP address (".$ip.") not allowed.";
            die();
        }
    }

    if (isset($_GET['jsonp']) && (!defined('PSI_JSONP') || !PSI_JSONP)) {
        echo "JSONP data mode not enabled in phpsysinfo.ini.";
        die();
    }

    /* default error handler */
    if (function_exists('errorHandlerPsi')) {
        restore_error_handler();
    }

    /* fatal errors only */
    $old_err_rep = error_reporting();
    error_reporting(E_ERROR);

    /* get git revision */
    if (file_exists(PSI_APP_ROOT.'/.git/HEAD')) {
        $contents = @file_get_contents(PSI_APP_ROOT.'/.git/HEAD');
        if ($contents && preg_match("/^ref:\s+(.*)\/([^\/\s]*)/m", $contents, $matches)) {
            $contents = @file_get_contents(PSI_APP_ROOT.'/.git/'.$matches[1]."/".$matches[2]);
            if ($contents && preg_match("/^([^\s]*)/m", $contents, $revision)) {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]."-".substr($revision[1], 0, 7));
            } else {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]);
            }
        }
    }
    /* get svn revision */
    if (!defined('PSI_VERSION_STRING') && file_exists(PSI_APP_ROOT.'/.svn/entries')) {
        $contents = @file_get_contents(PSI_APP_ROOT.'/.svn/entries');
        if ($contents && preg_match("/dir\n(.+)/", $contents, $matches)) {
            define('PSI_VERSION_STRING', PSI_VERSION."-r".$matches[1]);
        } else {
            define('PSI_VERSION_STRING', PSI_VERSION);
        }
    }
    if (!defined('PSI_VERSION_STRING')) {
        define('PSI_VERSION_STRING', PSI_VERSION);
    }

    if (defined('PSI_ROOTFS') && is_string(PSI_ROOTFS) && (PSI_ROOTFS !== '') && (PSI_ROOTFS !== '/')) {
        $rootfs = PSI_ROOTFS;
        if ($rootfs[0] === '/') {
            define('PSI_ROOT_FILESYSTEM', $rootfs);
        } else {
            define('PSI_ROOT_FILESYSTEM', '');
        }
    } else {
        define('PSI_ROOT_FILESYSTEM', '');
    }

    if (!defined('PSI_OS')) { //if not overloaded in phpsysinfo.ini
        /* get Linux code page */
        if ((PHP_OS == 'Linux') || (PHP_OS == 'GNU')) {
            if (file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/sysconfig/i18n')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/default/locale')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/locale.conf')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/sysconfig/language')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/profile.d/lang.sh')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/profile.d/i18n.sh')
               || file_exists($fname = PSI_ROOT_FILESYSTEM.'/etc/profile')) {
                $contents = @file_get_contents($fname);
            } else {
                $contents = false;
                if (PHP_OS == 'Linux') {
                    if (file_exists(PSI_ROOT_FILESYSTEM.'/system/build.prop')) { //Android
                        define('PSI_OS', 'Android');
                        if ((PSI_ROOT_FILESYSTEM === '') && function_exists('exec') && @exec('uname -o 2>/dev/null', $unameo) && (sizeof($unameo)>0) && (($unameo0 = trim($unameo[0])) != "")) {
                            define('PSI_UNAMEO', $unameo0); // is Android on Termux
                        }
                        if ((PSI_ROOT_FILESYSTEM === '') && !defined('PSI_MODE_POPEN')) { //if not overloaded in phpsysinfo.ini
                            if (!function_exists("proc_open")) { //proc_open function test by executing 'pwd' bbbmand
                                define('PSI_MODE_POPEN', true); //use popen() function - no stderr error handling (but with problems with timeout)
                            } else {
                                $out = '';
                                $err = '';
                                $pipes = array();
                                $descriptorspec = array(0=>array("pipe", "r"), 1=>array("pipe", "w"), 2=>array("pipe", "w"));
                                $process = proc_open("pwd 2>/dev/null ", $descriptorspec, $pipes);
                                if (!is_resource($process)) {
                                    define('PSI_MODE_POPEN', true);
                                } else {
                                    $w = null;
                                    $e = null;

                                    while (!(feof($pipes[1]) && feof($pipes[2]))) {
                                        $read = array($pipes[1], $pipes[2]);

                                        $n = stream_select($read, $w, $e, 5);

                                        if (($n === false) || ($n === 0)) {
                                            break;
                                        }

                                        foreach ($read as $r) {
                                            if ($r == $pipes[1]) {
                                                $out .= fread($r, 4096);
                                            } elseif (feof($pipes[1]) && ($r == $pipes[2])) {//read STDERR after STDOUT
                                                $err .= fread($r, 4096);
                                            }
                                        }
                                    }

                                    if (($out === null) || (trim($out) == "") || (substr(trim($out), 0, 1) != "/")) {
                                        define('PSI_MODE_POPEN', true);
                                    }
                                    fclose($pipes[0]);
                                    fclose($pipes[1]);
                                    fclose($pipes[2]);
                                    // It is important that you close any pipes before calling
                                    // proc_close in order to avoid a deadlock
                                    proc_close($process);
                                }
                            }
                        }
                    } elseif (file_exists(PSI_ROOT_FILESYSTEM.'/var/mobile/Library/Cydia/metadata.cb0')) { //jailbroken iOS with Cydia
                        define('PSI_OS', 'Darwin');
                    }
                }
            }
            if (!(defined('PSI_SYSTEM_CODEPAGE') && defined('PSI_SYSTEM_LANG')) //also if both not overloaded in phpsysinfo.ini
               && $contents && (preg_match('/^(LANG="?[^"\n]*"?)/m', $contents, $matches)
               || preg_match('/^RC_(LANG="?[^"\n]*"?)/m', $contents, $matches)
               || preg_match('/^\s*export (LANG="?[^"\n]*"?)/m', $contents, $matches))) {
                if (!defined('PSI_SYSTEM_CODEPAGE')) {
                    if (file_exists($vtfname = PSI_ROOT_FILESYSTEM.'/sys/module/vt/parameters/default_utf8')
                       && (trim(@file_get_contents($vtfname)) === "1")) {
                        define('PSI_SYSTEM_CODEPAGE', 'UTF-8');
                    } elseif ((PSI_ROOT_FILESYSTEM === '') && function_exists('exec') && @exec($matches[1].' locale -k LC_CTYPE 2>/dev/null', $lines)) { //if not overloaded in phpsysinfo.ini
                        foreach ($lines as $line) {
                            if (preg_match('/^charmap="?([^"]*)/', $line, $matches2)) {
                                define('PSI_SYSTEM_CODEPAGE', $matches2[1]);
                                break;
                            }
                        }
                    }
                }
                if ((PSI_ROOT_FILESYSTEM === '') && !defined('PSI_SYSTEM_LANG') && function_exists('exec') && @exec($matches[1].' locale 2>/dev/null', $lines2)) { //also if not overloaded in phpsysinfo.ini
                    foreach ($lines2 as $line) {
                        if (preg_match('/^LC_MESSAGES="?([^\."@]*)/', $line, $matches2)) {
                            $lang = "";
                            if (is_readable(PSI_APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(PSI_APP_ROOT.'/data/languages.ini', true))) {
                                if (isset($langdata['Linux']['_'.$matches2[1]])) {
                                    $lang = $langdata['Linux']['_'.$matches2[1]];
                                }
                            }
                            if ($lang == "") {
                                $lang = 'Unknown';
                            }
                            define('PSI_SYSTEM_LANG', $lang.' ('.$matches2[1].')');
                            break;
                        }
                    }
                }
            }
        } elseif (PHP_OS == 'Haiku') {
            if (!(defined('PSI_SYSTEM_CODEPAGE') && defined('PSI_SYSTEM_LANG')) //also if both not overloaded in phpsysinfo.ini
                && (PSI_ROOT_FILESYSTEM === '') && function_exists('exec') && @exec('locale --message 2>/dev/null', $lines)) {
                foreach ($lines as $line) {
                    if (preg_match('/^"?([^\."]*)\.?([^"]*)/', $line, $matches2)) {

                        if (!defined('PSI_SYSTEM_CODEPAGE') && isset($matches2[2]) && ($matches2[2] !== null) && (trim($matches2[2]) != "")) { //also if not overloaded in phpsysinfo.ini
                            define('PSI_SYSTEM_CODEPAGE', $matches2[2]);
                        }

                        if (!defined('PSI_SYSTEM_LANG')) { //if not overloaded in phpsysinfo.ini
                            $lang = "";
                            if (is_readable(PSI_APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(PSI_APP_ROOT.'/data/languages.ini', true))) {
                                if (isset($langdata['Linux']['_'.$matches2[1]])) {
                                    $lang = $langdata['Linux']['_'.$matches2[1]];
                                }
                            }
                            if ($lang == "") {
                                $lang = 'Unknown';
                            }
                            define('PSI_SYSTEM_LANG', $lang.' ('.$matches2[1].')');
                        }
                        break;
                    }
                }
            }
        } elseif ((PHP_OS == 'Darwin') || (defined('PSI_OS') && (PSI_OS == 'Darwin'))){
            if (!defined('PSI_SYSTEM_LANG') //if not overloaded in phpsysinfo.ini
                && (PSI_ROOT_FILESYSTEM === '') && function_exists('exec') && @exec('defaults read /Library/Preferences/.GlobalPreferences AppleLocale 2>/dev/null', $lines)) {
                $lang = "";
                if (is_readable(PSI_APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(PSI_APP_ROOT.'/data/languages.ini', true))) {
                    if (isset($langdata['Linux']['_'.$lines[0]])) {
                        $lang = $langdata['Linux']['_'.$lines[0]];
                    }
                }
                if ($lang == "") {
                    $lang = 'Unknown';
                }
                define('PSI_SYSTEM_LANG', $lang.' ('.$lines[0].')');
            }
        }
    }

    /* maximum time in seconds a script is allowed to run before it is terminated by the parser */
    if (defined('PSI_MAX_TIMEOUT')) {
        ini_set('max_execution_time', max(intval(PSI_MAX_TIMEOUT), 0));
    } else {
        ini_set('max_execution_time', 30);
    }

    /* executeProgram() timeout value in seconds */
    if (defined('PSI_EXEC_TIMEOUT')) {
        define('PSI_EXEC_TIMEOUT_INT', max(intval(PSI_EXEC_TIMEOUT), 1));
    } else {
        define('PSI_EXEC_TIMEOUT_INT', 30);
    }

    /* snmprealwalk() and executeProgram("snmpwalk") number of seconds until the first timeout */
    if (defined('PSI_SNMP_TIMEOUT')) {
        define('PSI_SNMP_TIMEOUT_INT', max(intval(PSI_SNMP_TIMEOUT), 1));
    } else {
        define('PSI_SNMP_TIMEOUT_INT', 3);
    }

    /* snmprealwalk() and executeProgram("snmpwalk") number of times to retry if timeouts occur */
    if (defined('PSI_SNMP_RETRY')) {
        define('PSI_SNMP_RETRY_INT', max(intval(PSI_SNMP_RETRY), 0));
    } else {
        define('PSI_SNMP_RETRY_INT', 0);
    }

    if (!defined('PSI_OS')) {
        define('PSI_OS', PHP_OS);
    }

    if (!defined('PSI_SYSTEM_LANG')) {
        define('PSI_SYSTEM_LANG', null);
    }
    if (!defined('PSI_SYSTEM_CODEPAGE')) { //if not overloaded in phpsysinfo.ini
        if ((PSI_OS=='Android') || (PSI_OS=='Darwin')) {
            define('PSI_SYSTEM_CODEPAGE', 'UTF-8');
        } elseif (PSI_OS=='Minix') {
            define('PSI_SYSTEM_CODEPAGE', 'CP437');
        } elseif (PSI_OS!='WINNT') {
            define('PSI_SYSTEM_CODEPAGE', null);
        }
    }

    if (!defined('PSI_JSON_ISSUE')) { //if not overloaded in phpsysinfo.ini
        if (!extension_loaded("simplexml")) {
            die("phpSysInfo requires the simplexml extension to php in order to work properly.");
        }
        if (simplexml_load_string("<A><B><C/></B>\n</A>") !== simplexml_load_string("<A><B><C/></B></A>")) { // json_encode issue test
            define('PSI_JSON_ISSUE', true); // Problem must be solved
        }
    }

    /* restore error level */
    error_reporting($old_err_rep);

    /* restore error handler */
    if (function_exists('errorHandlerPsi')) {
        set_error_handler('errorHandlerPsi');
    }
}
