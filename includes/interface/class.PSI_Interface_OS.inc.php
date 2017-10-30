<?php
/**
 * Basic OS Functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Interfaces
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.PSI_Interface_OS.inc.php 263 2009-06-22 13:01:52Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define which methods a os class for phpsysinfo must implement
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
interface PSI_Interface_OS
{
    /**
     * get a special encoding from os where phpsysinfo is running
     *
     * @return string
     */
    public function getEncoding();

    /**
     * build the os information
     *
     * @return void
     */
    public function build();

    /**
     * get the filled or unfilled (with default values) system object
     *
     * @return System
     */
    public function getSys();

    /**
     * get os specific language
     *
     * @return string
     */
    public function getLanguage();
}
