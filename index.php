<?php 
/**
 * start page for webaccess
 * redirect the user to the supported page type by the users webbrowser (js available or not)
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: index.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define the application root path on the webserver
 * @var string
 */
define('APP_ROOT', dirname(__FILE__));

/**
 * internal xml or external
 * external is needed when running in static mode
 *
 * @var boolean
 */
define('PSI_INTERNAL_XML', false);

if (version_compare("5.2", PHP_VERSION, ">")) {
    die("PHP 5.2 or greater is required!!!");
}

require_once APP_ROOT.'/includes/autoloader.inc.php';
    
// Load configuration
if (!is_readable(APP_ROOT.'/config.php')) {
    $tpl = new Template("/templates/html/error_config.html");
    echo $tpl->fetch();
    die();
}
else {
    include_once APP_ROOT.'/config.php';
}

/*
if (!defined('PSI_DEBUG')){
    trigger_error("Error: phpSysInfo requires setting 'DEBUG' (true or false) in 'phpsysinfo.ini'", E_USER_ERROR);
}
if (PSI_DEBUG === true){
// Safe mode check
    $safe_mode = @ini_get("safe_mode") ? TRUE : FALSE;
    if ($safe_mode) {
         echo "Warning: phpSysInfo requires you to set off 'safe_mode' in 'php.ini'";
    }
// Include path check
    $include_path = @ini_get("include_path");
    if (($include_path)&&($include_path!="")) {
        $include_path = str_replace(":", "\n", $include_path);
        if (preg_match("/^\.$/m", $include_path)) {
            $include_path = ".";
        }
    }
    if ($include_path != "." ) {
         echo "Warning: phpSysInfo requires '.' inside the 'include_path' in php.ini";
    }
}
*/

// redirect to page with and without javascript
$display = isset($_GET['disp']) ? $_GET['disp'] : strtolower(PSI_DEFAULT_DISPLAY_MODE);
switch ($display) {
case "static":
    $webpage = new WebpageXSLT();
    $webpage->run();
    break;
case "dynamic":
    $webpage = new Webpage();
    $webpage->run();
    break;
case "xml":
    $webpage = new WebpageXML(true, null);
    $webpage->run();
    break;
default:
    $tpl = new Template("/templates/html/index_all.html");
    echo $tpl->fetch();
    break;
}
?>
