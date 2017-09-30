<?php
/**
 * Basic Plugin Functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Interfaces
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.PSI_Interface_Plugin.inc.php 273 2009-06-24 11:40:09Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define which methods a plugin class for phpsysinfo must implement
 * to be recognized and fully work without errors, these are the methods which
 * are called from outside to include the information in the main application
 *
 * @category  PHP
 * @package   PSI_Interfaces
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
interface PSI_Interface_Plugin
{
    /**
     * doing all tasks before the xml can be build
     *
     * @return void
     */
    public function execute();

    /**
     * build the xml
     *
     * @return SimpleXMLElement entire XML content for the plugin which than can be appended to the main XML
     */
    public function xml();
}
