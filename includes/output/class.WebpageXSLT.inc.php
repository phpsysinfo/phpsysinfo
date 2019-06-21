<?php
/**
 * start page for webaccess
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Web
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.WebpageXSLT.inc.php 569 2012-04-16 06:08:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * generate a static webpage with xslt trasformation of the xml
 *
 * @category  PHP
 * @package   PSI_Web
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class WebpageXSLT extends WebpageXML implements PSI_Interface_Output
{
    /**
     * call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * generate the static page
     *
     * @return void
     */
    public function run()
    {
        CommonFunctions::checkForExtensions(array('xsl'));
        $xmlfile = $this->getXMLString();
        $xslfile = "phpsysinfo.xslt";
        $domxml = new DOMDocument();
        $domxml->loadXML($xmlfile);
        $domxsl = new DOMDocument();
        $domxsl->load($xslfile);
        $xsltproc = new XSLTProcessor;
        $xsltproc->importStyleSheet($domxsl);
        header("Cache-Control: no-cache, must-revalidate\n");
        echo $xsltproc->transformToXML($domxml);
    }
}
