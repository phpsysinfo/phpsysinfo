<?php
/**
 * compress js files and send them to the browser on the fly
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_JS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: js.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * application root path
 *
 * @var string
 */
define('PSI_APP_ROOT', dirname(__FILE__));

require_once PSI_APP_ROOT.'/includes/autoloader.inc.php';

require_once PSI_APP_ROOT.'/read_config.php';

$file = isset($_GET['name']) ? basename(htmlspecialchars(trim($_GET['name']))) : null;
$plugin = isset($_GET['plugin']) ? basename(htmlspecialchars(trim($_GET['plugin']))) : null;
$script = null;

if ($file != null && $plugin == null) {
    if (strtolower(substr($file, 0, 10)) == 'phpsysinfo') {
        $script = PSI_APP_ROOT.'/js/'.$file;
    } elseif (strtolower($file) == 'jquery') {
        $script = PSI_APP_ROOT.'/js/common/jquery';
    } elseif (strtolower(substr($file, 0, 7)) == 'jquery.') {
        $script = PSI_APP_ROOT.'/js/dynamic/'.$file;
    } else {
        $script = PSI_APP_ROOT.'/js/bootstrap/'.$file;
    }
} elseif ($file == null && $plugin != null) {
    $script = PSI_APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($plugin);
} elseif ($file != null && $plugin != null) {
    $script = PSI_APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($file);
}

if ($script != null) {
    $scriptjs = $script.'.js';
    $scriptmin = $script.'.min.js';
    $compression = false;

    header('content-type: application/x-javascript');

    if ((!defined("PSI_DEBUG") || (PSI_DEBUG !== true)) && defined("PSI_JS_COMPRESSION")) {
        $compression = strtolower(PSI_JS_COMPRESSION);
    }
    switch ($compression) {
    case "normal":
        if (file_exists($scriptmin) && is_readable($scriptmin)) {
            echo file_get_contents($scriptmin);
        } elseif (file_exists($scriptjs) && is_readable($scriptjs)) {
            $packer = new JavaScriptPacker(file_get_contents($scriptjs));
            echo $packer->pack();
        }
        break;
    case "none":
        if (file_exists($scriptjs) && is_readable($scriptjs)) {
           $packer = new JavaScriptPacker(file_get_contents($scriptjs), 0);
            echo $packer->pack();
        } elseif (file_exists($scriptmin) && is_readable($scriptmin)) {
           echo file_get_contents($scriptmin);
        }
        break;
    default:
        if (file_exists($scriptjs) && is_readable($scriptjs)) {
            echo file_get_contents($scriptjs);
        } elseif (file_exists($scriptmin) && is_readable($scriptmin)) {
            echo file_get_contents($scriptmin);
        }
    }
}
