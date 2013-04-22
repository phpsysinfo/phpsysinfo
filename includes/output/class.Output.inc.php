<?php
/**
 * basic output functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Output
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Output.inc.php 569 2012-04-16 06:08:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * basic output functions for all output formats
 *
 * @category  PHP
 * @package   PSI_Output
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class Output
{
    /**
     * error object for logging errors
     *
     * @var Error
     */
    protected $error;

    /**
     * call the parent constructor and check for needed extensions
     */
    public function __construct()
    {
        $this->error = Error::singleton();
        $this->_checkConfig();
        CommonFunctions::checkForExtensions();
//        $this->error = Error::singleton();
//        $this->_checkConfig();
    }

    /**
     * read the config file and check for existence
     *
     * @return void
     */
    private function _checkConfig()
    {
        if (!is_readable(APP_ROOT.'/config.php')) {
            $this->error->addError('file_exists(config.php)', 'config.php does not exist or is not readable by the webserver in the phpsysinfo directory.');
        } else {
            include_once APP_ROOT.'/config.php';
        }
        if ($this->error->errorsExist()) {
            $this->error->errorsAsXML();
        }
    }
}
