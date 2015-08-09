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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: js.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * application root path
 *
 * @var string
 */
define('APP_ROOT', dirname(__FILE__));

require_once APP_ROOT.'/includes/autoloader.inc.php';

require_once APP_ROOT.'/read_config.php';

$file = isset($_GET['name']) ? basename(htmlspecialchars($_GET['name'])) : null;
$plugin = isset($_GET['plugin']) ? basename(htmlspecialchars($_GET['plugin'])) : null;
$script = null;

if ($file != null && $plugin == null) {
    if (strtolower(substr($file, 0, 6)) == 'jquery') {
        $script = APP_ROOT.'/js/jQuery/'.$file;
    } elseif (strtolower(substr($file, 0, 10)) == 'phpsysinfo') {
        $script = APP_ROOT.'/js/phpSysInfo/'.$file;
    } else {
        $script = APP_ROOT.'/js/vendor/'.$file;
    }
} elseif ($file == null && $plugin != null) {
    $script = APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($plugin);
} elseif ($file != null && $plugin != null) {
    $script = APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($file);
}

if ($script != null) {
    $scriptjs = $script.'.js';
    $scriptmin = $script.'.min.js';
    $compression = false;

    header("content-type: application/x-javascript");

    if ((!defined("PSI_DEBUG") || (PSI_DEBUG !== true)) && defined("PSI_JS_COMPRESSION")) {
        $compression = strtolower(PSI_JS_COMPRESSION);
    }
    switch ($compression) {
        case "normal":
            if (file_exists($scriptmin) && is_readable($scriptmin)) {
                $filecontent = file_get_contents($scriptmin);
                echo $filecontent;
            } elseif (file_exists($scriptjs) && is_readable($scriptjs)) {
                $filecontent = file_get_contents($scriptjs);
                $packer = new JavaScriptPacker($filecontent);
                echo $packer->pack();
            }
            break;
        case "none":
            if (file_exists($scriptjs) && is_readable($scriptjs)) {
                $filecontent = file_get_contents($scriptjs);
                $packer = new JavaScriptPacker($filecontent, 0);
                echo $packer->pack();
            } elseif (file_exists($scriptmin) && is_readable($scriptmin)) {
                $filecontent = file_get_contents($scriptmin);
                echo $filecontent;
            }
            break;
        default:
            if (file_exists($scriptjs) && is_readable($scriptjs)) {
                $filecontent = file_get_contents($scriptjs);
            } elseif (file_exists($scriptmin) && is_readable($scriptmin)) {
                $filecontent = file_get_contents($scriptmin);
            } else break;

            echo $filecontent;
            break;
    }
}
