<?php 
/**
 * generate the xml
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: xml.php 614 2012-07-28 09:02:59Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 
 /**
 * application root path
 *
 * @var string
 */
define('APP_ROOT', dirname(__FILE__));

/**
 * internal xml or external
 * external is needed when running in static mode
 *
 * @var boolean
 */
define('PSI_INTERNAL_XML', true);

require_once APP_ROOT.'/includes/autoloader.inc.php';

// check what xml part should be generated
if (isset($_GET['plugin'])) {
    $plugin = basename(htmlspecialchars($_GET['plugin']));
    if ($plugin == "complete") {
        $output = new WebpageXML(true, null);
        show($output);
    } elseif ($plugin != "") {
        $output = new WebpageXML(false, $plugin);
        show($output);
    }
} else {
    $output = new WebpageXML(false, null);
    show($output);
}

function show(WebpageXML $output) 
{
    if (isset($_GET['json']) || isset($_GET['jsonp'])) {
        $json = json_encode(
            simplexml_load_string($output->getXMLString())
        );
        echo (isset($_GET['jsonp'])) ? $_GET['callback'] . '('.$json.')' : $json;
    } else {
        $output->run();
    }
}
?>
