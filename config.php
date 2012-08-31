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
        if (function_exists('errorHandlerPsi')) restore_error_handler();
        $contents = file_get_contents(APP_ROOT.'/.git/HEAD');
        if (function_exists('errorHandlerPsi')) set_error_handler('errorHandlerPsi');
        if ($contents && preg_match("/^ref:\s+(.*)\/([^\/\s]*)/m", $contents, $matches)) {
            if (function_exists('errorHandlerPsi')) restore_error_handler();
            $contents = file_get_contents(APP_ROOT.'/.git/'.$matches[1]."/".$matches[2]);
            if (function_exists('errorHandlerPsi')) set_error_handler('errorHandlerPsi');
            if ($contents && preg_match("/^([^\s]*)/m", $contents, $revision)) {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]."-".$revision[1]);
            } else {
                define('PSI_VERSION_STRING', PSI_VERSION ."-".$matches[2]);
            }
        }
    }
    /* get svn revision */
    if ((!defined('PSI_VERSION_STRING'))&&(file_exists (APP_ROOT.'/.svn/entries'))){ 
        if (function_exists('errorHandlerPsi')) restore_error_handler();
        $contents = file_get_contents(APP_ROOT.'/.svn/entries');
        if (function_exists('errorHandlerPsi')) set_error_handler('errorHandlerPsi');
        if ($contents && preg_match("/dir\n(.+)/", $contents, $matches)) {
            define('PSI_VERSION_STRING', PSI_VERSION."-r".$matches[1]);
        } else {
            define('PSI_VERSION_STRING', PSI_VERSION);
        }
    }
    if (!defined('PSI_VERSION_STRING')){ 
        define('PSI_VERSION_STRING', PSI_VERSION);
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
