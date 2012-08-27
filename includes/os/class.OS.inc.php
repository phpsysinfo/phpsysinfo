<?php 
/**
 * Basic OS Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_OS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.OS.inc.php 263 2009-06-22 13:01:52Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Basic OS functions for all OS classes
 *
 * @category  PHP
 * @package   PSI_OS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class OS implements PSI_Interface_OS
{
    /**
     * object for error handling
     *
     * @var Error
     */
    protected $error;
    
    /**
     * @var System
     */
    protected $sys;
    
    /**
     * build the global Error object
     */
    public function __construct()
    {
        $this->error = Error::singleton();
        $this->sys = new System();
    }
    
    /**
     * get os specific encoding
     *
     * @see PSI_Interface_OS::getEncoding()
     *
     * @return string
     */
    public function getEncoding()
    {
        return null;
    }
    
    /**
     * get the filled or unfilled (with default values) System object
     *
     * @see PSI_Interface_OS::getSys()
     *
     * @return System
     */
    public final function getSys()
    {
        $this->build();
        return $this->sys;
    }
}
?>
