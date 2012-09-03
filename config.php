<?php 
if (!defined('PSI_CONFIG_FILE')){
    /**
     * phpSysInfo version
     */
    define('PSI_VERSION','3.1.x');
    /**
     * phpSysInfo configuration
     */
    define('PSI_CONFIG_FILE', APP_ROOT.'/phpsysinfo.ini');

    /* get git revision */ 
    if  (file_exists (APP_ROOT.'/.git/HEAD')){
        $contents = @file_get_contents(APP_ROOT.'/.git/HEAD');
        if ($contents && preg_match("/^ref:\s+(.*)\/([^\/\s]*)/m", $contents, $matches)) {
            $contents = @file_get_contents(APP_ROOT.'/.git/'.$matches[1]."/".$matches[2]);
            if ($contents && preg_match("/^([^\s]*)/m", $contents, $revision)) {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]."-".$revision[1]);
            } else {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]);
            }
        }
    }
    /* get svn revision */
    if ((!defined('PSI_VERSION_STRING'))&&(file_exists (APP_ROOT.'/.svn/entries'))){ 
        $contents = @file_get_contents(APP_ROOT.'/.svn/entries');
        if ($contents && preg_match("/dir\n(.+)/", $contents, $matches)) {
            define('PSI_VERSION_STRING', PSI_VERSION."-r".$matches[1]);
        } else {
            define('PSI_VERSION_STRING', PSI_VERSION);
        }
    }
    if (!defined('PSI_VERSION_STRING')){ 
        define('PSI_VERSION_STRING', PSI_VERSION);
    }

    /* get Linux charset */
    if (PHP_OS == 'Linux'){
        if  (file_exists ('/etc/sysconfig/i18n')){
            $contents = @file_get_contents('/etc/sysconfig/i18n');
        } else if  (file_exists ('/etc/default/locale')){
            $contents = @file_get_contents('/etc/default/locale');
        } else if  (file_exists ('/etc/locale.conf')){
            $contents = @file_get_contents('/etc/locale.conf');
        } else $contents = false;
        if ($contents && preg_match("/^(LANG=\".*\")/m", $contents, $matches)) {
            if (@exec($matches[1].' locale -k LC_CTYPE 2>/dev/null', $lines)) {
                foreach ($lines as $line) {
                    if ($contents && preg_match("/^charmap=\"(.*)\"/m", $line, $matches)) {
                        define('PSI_SYSTEM_CHARSET', $matches[1]);
                        break;
                    }
                }
            }
        }
    }
    if (!defined('PSI_SYSTEM_CHARSET')){ 
        define('PSI_SYSTEM_CHARSET', null);
    }


    define('ARRAY_EXP', '/^return array \([^;]*\);$/'); //array expression search

    if ((!is_readable(PSI_CONFIG_FILE)) || !($config = @parse_ini_file(PSI_CONFIG_FILE, true))){
        $tpl = new Template("/templates/html/error_config.html");
        echo $tpl->fetch();
        die();
    } else {
        foreach ($config as $name=>$group) {
            if (strtoupper($name)=="MAIN") {
                $name_prefix='PSI_';
            } else {
                $name_prefix='PSI_PLUGIN_'.strtoupper($name).'_';
            }
            foreach ($group as $param=>$value) {
                if ($value===""){
                    define($name_prefix.strtoupper($param), false);
                } else if ($value==1){
                    define($name_prefix.strtoupper($param), true);
                } else {
                    if (strstr($value, ',')) {
                        define($name_prefix.strtoupper($param), 'return '.var_export(preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY),1).';');
                    } else {
                        define($name_prefix.strtoupper($param), $value);
                    }
                }
            }
        }
    }
}
?>
