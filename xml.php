<?php
header('Access-Control-Allow-Origin: *');
/**
 * generate the xml
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: xml.php 614 2012-07-28 09:02:59Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */

 /**
 * application root path
 *
 * @var string
 */
define('PSI_APP_ROOT', dirname(__FILE__));

require_once PSI_APP_ROOT.'/includes/autoloader.inc.php';

if ((isset($_GET['json']) || isset($_GET['jsonp'])) && !extension_loaded("json")) {
    echo '<Error Message="The json extension to php required!" Function="ERROR"/>';
} else {
    // check what xml part should be generated
    if (isset($_GET['plugin'])) {
        if (($_GET['plugin'] !== "") && !preg_match('/[^A-Za-z]/', $_GET['plugin'])) {
            $output = new WebpageXML($_GET['plugin']);
        } else {
            unset($output);
        }
    } else {
        $output = new WebpageXML();
    }
    // if $output is correct generate output in proper type
    if (isset($output) && is_object($output)) {
        if (isset($_GET['json']) || isset($_GET['jsonp'])) {
            header("Cache-Control: no-cache, must-revalidate\n");
            $json = $output->getJsonString();
            if (isset($_GET['jsonp'])) {
                header("Content-Type: application/javascript\n\n");
                echo (!preg_match('/[^\w\?]/', $_GET['callback'])?$_GET['callback']:'') . '('.$json.')';
            } else {
                header("Content-Type: application/json\n\n");
                echo $json;
            }
        } else {
            $output->run();
        }
    }
}
