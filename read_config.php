<?php
if (!defined('PSI_CONFIG_FILE')) {
    /**
     * phpSysInfo version
     */
    define('PSI_VERSION', '3.2.x');
    /**
     * phpSysInfo configuration
     */
    define('PSI_CONFIG_FILE', APP_ROOT.'/phpsysinfo.ini');

    if (!file_exists(PSI_CONFIG_FILE) && file_exists(APP_ROOT.'/phpsysinfo.ini.new')) {
        @rename(APP_ROOT.'/phpsysinfo.ini.new', APP_ROOT.'/phpsysinfo.ini');
    }

    define('ARRAY_EXP', '/^return array \([^;]*\);$/'); //array expression search

    if (!is_readable(PSI_CONFIG_FILE) || !($config = @parse_ini_file(PSI_CONFIG_FILE, true))) {
        if (defined('PSI_INTERNAL_XML') && PSI_INTERNAL_XML === true) {
            echo "ERROR: phpsysinfo.ini does not exist or is not readable by the webserver in the phpsysinfo directory";
            die();
        }
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
                    if (strstr($value, ',')) {
                        define($name_prefix.strtoupper($param), 'return '.var_export(preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY), 1).';');
                    } else {
                        define($name_prefix.strtoupper($param), trim($value));
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

        if (!in_array($ip, $allowed, true)) {
            echo "Client IP address not allowed";
            die();
        }
    }

    /* default error handler */
    if (function_exists('errorHandlerPsi')) {
        restore_error_handler();
    }

    /* fatal errors only */
    $old_err_rep = error_reporting();
    error_reporting(E_ERROR);

    /* get git revision */
    if (file_exists(APP_ROOT.'/.git/HEAD')) {
        $contents = @file_get_contents(APP_ROOT.'/.git/HEAD');
        if ($contents && preg_match("/^ref:\s+(.*)\/([^\/\s]*)/m", $contents, $matches)) {
            $contents = @file_get_contents(APP_ROOT.'/.git/'.$matches[1]."/".$matches[2]);
            if ($contents && preg_match("/^([^\s]*)/m", $contents, $revision)) {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]."-".substr($revision[1], 0, 7));
            } else {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]);
            }
        }
    }
    /* get svn revision */
    if (!defined('PSI_VERSION_STRING') && file_exists(APP_ROOT.'/.svn/entries')) {
        $contents = @file_get_contents(APP_ROOT.'/.svn/entries');
        if ($contents && preg_match("/dir\n(.+)/", $contents, $matches)) {
            define('PSI_VERSION_STRING', PSI_VERSION."-r".$matches[1]);
        } else {
            define('PSI_VERSION_STRING', PSI_VERSION);
        }
    }
    if (!defined('PSI_VERSION_STRING')) {
        define('PSI_VERSION_STRING', PSI_VERSION);
    }

    if (!defined('PSI_OS')) { //if not overloaded in phpsysinfo.ini
        /* get Linux code page */
        if (PHP_OS == 'Linux') {
            if (file_exists('/etc/sysconfig/i18n')) {
                $contents = @file_get_contents('/etc/sysconfig/i18n');
            } elseif (file_exists('/etc/default/locale')) {
                $contents = @file_get_contents('/etc/default/locale');
            } elseif (file_exists('/etc/locale.conf')) {
                $contents = @file_get_contents('/etc/locale.conf');
            } elseif (file_exists('/etc/sysconfig/language')) {
                $contents = @file_get_contents('/etc/sysconfig/language');
            } elseif (file_exists('/etc/profile.d/lang.sh')) {
                $contents = @file_get_contents('/etc/profile.d/lang.sh');
            } elseif (file_exists('/etc/profile')) {
                $contents = @file_get_contents('/etc/profile');
            } else {
                $contents = false;
                if (file_exists('/system/build.prop')) { //Android
                    define('PSI_OS', 'Android');
                    if (!defined('PSI_MODE_POPEN')) { //if not overloaded in phpsysinfo.ini
                        if (!function_exists("proc_open")) { //proc_open function test by executing 'pwd' command
                            define('PSI_MODE_POPEN', true); //use popen() function - no stderr error handling
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

                                while (!(feof($pipes[1]) || feof($pipes[2]))) {
                                    $read = array($pipes[1], $pipes[2]);

                                    $n = stream_select($read, $w, $e, 5);

                                    if (($n === false) || ($n === 0)) {
                                        break;
                                    }

                                    foreach ($read as $r) {
                                        if ($r == $pipes[1]) {
                                            $out .= fread($r, 4096);
                                        }
                                        if ($r == $pipes[2]) {
                                            $err .= fread($r, 4096);
                                        }
                                    }
                                }

                                if (is_null($out) || (trim($out) == "") || (substr(trim($out), 0, 1) != "/")) {
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
                }
            }
            if (!(defined('PSI_SYSTEM_CODEPAGE') && defined('PSI_SYSTEM_LANG')) //also if both not overloaded in phpsysinfo.ini
               && $contents && (preg_match('/^(LANG="?[^"\n]*"?)/m', $contents, $matches)
               || preg_match('/^RC_(LANG="?[^"\n]*"?)/m', $contents, $matches)
               || preg_match('/^\s*export (LANG="?[^"\n]*"?)/m', $contents, $matches))) {
                if (!defined('PSI_SYSTEM_CODEPAGE') && @exec($matches[1].' locale -k LC_CTYPE 2>/dev/null', $lines)) { //if not overloaded in phpsysinfo.ini
                    foreach ($lines as $line) {
                        if (preg_match('/^charmap="?([^"]*)/', $line, $matches2)) {
                            define('PSI_SYSTEM_CODEPAGE', $matches2[1]);
                            break;
                        }
                    }
                }
                if (!defined('PSI_SYSTEM_LANG') && @exec($matches[1].' locale 2>/dev/null', $lines)) { //also if not overloaded in phpsysinfo.ini
                    foreach ($lines as $line) {
                        if (preg_match('/^LC_MESSAGES="?([^\."@]*)/', $line, $matches2)) {
                            $lang = "";
                            if (is_readable(APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(APP_ROOT.'/data/languages.ini', true))) {
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
                && @exec('locale -m 2>/dev/null', $lines)) {
                foreach ($lines as $line) {
                    if (preg_match('/^"?([^\."]*)\.?([^"]*)/', $line, $matches2)) {

                        if (!defined('PSI_SYSTEM_CODEPAGE') && isset($matches2[2]) && !is_null($matches2[2]) && (trim($matches2[2]) != "")) { //also if not overloaded in phpsysinfo.ini
                            define('PSI_SYSTEM_CODEPAGE', $matches2[2]);
                        }

                        if (!defined('PSI_SYSTEM_LANG')) { //if not overloaded in phpsysinfo.ini
                            $lang = "";
                            if (is_readable(APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(APP_ROOT.'/data/languages.ini', true))) {
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
        } elseif (PHP_OS == 'Darwin') {
            if (!defined('PSI_SYSTEM_LANG') //if not overloaded in phpsysinfo.ini
                && @exec('defaults read /Library/Preferences/.GlobalPreferences AppleLocale 2>/dev/null', $lines)) {
                $lang = "";
                if (is_readable(APP_ROOT.'/data/languages.ini') && ($langdata = @parse_ini_file(APP_ROOT.'/data/languages.ini', true))) {
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
        } else {
            define('PSI_SYSTEM_CODEPAGE', null);
        }
    }

    if (!defined('PSI_JSON_ISSUE')) { //if not overloaded in phpsysinfo.ini
        if (!extension_loaded("xml")) {
            die("phpSysInfo requires the xml extension to php in order to work properly.");
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
