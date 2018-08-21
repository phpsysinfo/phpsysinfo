<?php
/**
 * basic output functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Output
 * @author    Damien Roth <iysaak@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.Output.inc.php 315 2009-09-02 15:48:31Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * basic output functions for all output formats
 *
 * @category  PHP
 * @package   PSI_Output
 * @author    Damien Roth <iysaak@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Template
{
    /**
     * Vars used in the template
     *
     * @var array
     */
    private $_vars;

    /**
     * Template file
     *
     * @var string
     */
    private $_file;

    /**
     * Constructor
     *
     * @param String $file the template file name
     */
    public function __construct($file=null)
    {
        $this->_file = $file;
        $this->_vars = array();
    }

    /**
     * Set a template variable.
     *
     * @param string                $name  variable name
     * @param string|array|Template $value variable value
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = is_object($value) ? $value->fetch() : $value;
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param string $file
     *
     * @return string
     */
    public function fetch($file=null)
    {
        if (!$file) {
            $file = $this->_file;
        }

        // Extract the vars to local namespace
        extract($this->_vars);

        // Start output buffering
        ob_start();

        include(PSI_APP_ROOT.$file);

        // Get the contents of the buffer
        $contents = ob_get_contents();

        // End buffering and discard
        ob_end_clean();

        return $contents;
    }
}
