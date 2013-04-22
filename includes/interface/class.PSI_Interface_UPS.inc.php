<?php
/**
 * Basic UPS Functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Interfaces
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.PSI_Interface_UPS.inc.php 263 2009-06-22 13:01:52Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define which methods a ups class for phpsysinfo must implement
 * to be recognized and fully work without errors
 *
 * @category  PHP
 * @package   PSI_Interfaces
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
interface PSI_Interface_UPS
{
    /**
     * build the ups information
     *
     * @return void
     */
    public function build();

    /**
     * get the filled or unfilled (with default values) UPSInfo object
     *
     * @return UPSInfo
     */
    public function getUPSInfo();
}
