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
require_once APP_ROOT.'/config.php';

$file = isset($_GET['name']) ? basename(htmlspecialchars($_GET['name'])) : null;
$plugin = isset($_GET['plugin']) ? basename(htmlspecialchars($_GET['plugin'])) : null;

if ($file != null && $plugin == null) {
    if (strtolower(substr($file, 0, 6)) == 'jquery') {
        $script = APP_ROOT.'/js/jQuery/'.$file.'.js';
    } else {
        $script = APP_ROOT.'/js/phpSysInfo/'.$file.'.js';
    }
}
if ($file == null && $plugin != null) {
    $script = APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($plugin).'.js';
}
if ($file != null && $plugin != null) {
    $script = APP_ROOT.'/plugins/'.strtolower($plugin).'/js/'.strtolower($file).'.js';
}

if ($script != null && file_exists($script) && is_readable($script)) {
    header("content-type: application/x-javascript");
    $filecontent = file_get_contents($script);
    if (defined("PSI_DEBUG") && PSI_DEBUG === true) {
        echo $filecontent;
    } else {
        if (defined("PSI_JS_COMPRESSION")) {
            switch (strtolower(PSI_JS_COMPRESSION)) {
                case "normal":
                    $packer = new JavaScriptPacker($filecontent);
                    echo $packer->pack();
                    break;
                case "none":
                    $packer = new JavaScriptPacker($filecontent,0);
                    echo $packer->pack();
                    break;
                default:
                    echo $filecontent;
                    break;
            }
        } else {
            echo $filecontent;
        }
    }
}
