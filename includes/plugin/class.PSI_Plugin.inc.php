<?php
/**
 * Basic Plugin Functions
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.PSI_Plugin.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * basic functions to get a plugin working in phpSysinfo
 * every plugin must implement this abstract class to be a valid plugin, main tasks
 * of this class are reading the configuration file and check for the required files
 * (*.js, lang/en.xml) to get everything working, if we have errors here we log them
 * to our global error object
 *
 * @category  PHP
 * @package   PSI_Plugin
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
abstract class PSI_Plugin implements PSI_Interface_Plugin
{
    /**
     * name of the plugin (classname)
     *
     * @var string
     */
    private $_plugin_name = "";

    /**
     * full directory path of the plugin
     *
     * @var string
     */
    private $_plugin_base = "";

    /**
     * global object for error handling
     *
     * @var Error
     */
    protected $global_error = "";

    /**
     * xml tamplate with header
     *
     * @var SimpleXMLExtended
     */
    protected $xml;

    /**
     * build the global Error object, read the configuration and check if all files are available
     * for a minimalistic function of the plugin
     *
     * @param  string $plugin_name name of the plugin
     * @param  string $enc         target encoding
     * @return void
     */
    public function __construct($plugin_name, $enc)
    {
        $this->global_error = PSI_Error::Singleton();
        if (trim($plugin_name) != "") {
            $this->_plugin_name = $plugin_name;
            $this->_plugin_base = PSI_APP_ROOT."/plugins/".strtolower($this->_plugin_name)."/";
            $this->_checkfiles();
            $this->_getconfig();
        } else {
            $this->global_error->addError("__construct()", "Parent constructor called without Plugin-Name!");
        }
        $this->_createXml($enc);
    }

    /**
     * read the plugin configuration file, if we have one in the plugin directory
     *
     * @return void
     */
    private function _getconfig()
    {
        if ((strtoupper($this->_plugin_name) !== 'DISKLOAD') &&
           (!defined('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_ACCESS')) &&
           (!defined('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_FILE')) &&
           (!defined('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_SHOW_SERIAL'))) {
            $this->global_error->addError("phpsysinfo.ini", "Config for plugin ".$this->_plugin_name." not exist!");
        }
    }

    /**
     * check if there is a default translation file availabe and also the required js files for
     * appending the content of the plugin to the main webpage
     *
     * @return void
     */
    private function _checkfiles()
    {
        if (!file_exists($filename = $this->_plugin_base."js/".strtolower($this->_plugin_name)."_dynamic.js")) {
            $this->global_error->addError("file_exists(".$filename.")", "JS-File for Plugin ".$this->_plugin_name." is missing!");
        } elseif (!is_readable($filename)) {
            $this->global_error->addError("is_readable(".$filename.")", "JS-File for Plugin ".$this->_plugin_name." is present but is not readable!");
        }
        if (!file_exists($filename = $this->_plugin_base."js/".strtolower($this->_plugin_name)."_bootstrap.js")) {
            $this->global_error->addError("file_exists(".$filename.")", "JS-File for Plugin ".$this->_plugin_name." is missing!");
        } elseif (!is_readable($filename)) {
            $this->global_error->addError("is_readable(".$filename.")", "JS-File for Plugin ".$this->_plugin_name." is present but is not readable!");
        }
        if (!file_exists($filename = $this->_plugin_base."lang/en.xml")) {
            $this->global_error->addError("file_exists(".$filename.")", "At least an english translation must exist for the plugin!");
        } elseif (!is_readable($filename)) {
            $this->global_error->addError("is_readable(".$filename.")", "The english translation is present but is not readable!");
        }
    }

    /**
     * create the xml template where plugin information are added to
     *
     * @param string $enc target encoding
     *
     * @return void
     */
    private function _createXml($enc)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("Plugin_".$this->_plugin_name);
        $dom->appendChild($root);
        $this->xml = new SimpleXMLExtended(simplexml_import_dom($dom), $enc);
        $plugname = strtoupper($this->_plugin_name);
        if ((PSI_OS == 'Linux') && defined('PSI_PLUGIN_'.$plugname.'_SSH_HOSTNAME') &&
           (!defined('PSI_SSH_HOSTNAME') || (PSI_SSH_HOSTNAME != constant('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_SSH_HOSTNAME')))) {
            $this->xml->addAttribute('Hostname', constant('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_SSH_HOSTNAME'));
        } elseif (((PSI_OS == 'WINNT') || (PSI_OS == 'Linux')) && defined('PSI_PLUGIN_'.$plugname.'_WMI_HOSTNAME') &&
           (!defined('PSI_WMI_HOSTNAME') || (PSI_WMI_HOSTNAME != constant('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_WMI_HOSTNAME')))) {
            $this->xml->addAttribute('Hostname', constant('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_WMI_HOSTNAME'));
        }
    }
}
