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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
     * @param String $plugin_name name of the plugin
     * @param String $enc         target encoding
     *
     * @return void
     */
    public function __construct($plugin_name, $enc)
    {
        $this->global_error = Error::Singleton();
        if (trim($plugin_name) != "") {
            $this->_plugin_name = $plugin_name;
            $this->_plugin_base = APP_ROOT."/plugins/".strtolower($this->_plugin_name)."/";
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
        if ((!defined('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_ACCESS')) &&
             (!defined('PSI_PLUGIN_'.strtoupper($this->_plugin_name).'_FILE'))) {
                $this->global_error->addError("config.ini", "Config for plugin ".$this->_plugin_name." not exist!");
        }
    }

    /**
     * check if there is a default translation file availabe and also the required js file for
     * appending the content of the plugin to the main webpage
     *
     * @return void
     */
    private function _checkfiles()
    {
        if (!file_exists($this->_plugin_base."js/".strtolower($this->_plugin_name).".js")) {
            $this->global_error->addError("file_exists(".$this->_plugin_base."js/".strtolower($this->_plugin_name).".js)", "JS-File for Plugin '".$this->_plugin_name."' is missing!");
        } else {
            if (!is_readable($this->_plugin_base."js/".strtolower($this->_plugin_name).".js")) {
                $this->global_error->addError("is_readable(".$this->_plugin_base."js/".strtolower($this->_plugin_name).".js)", "JS-File for Plugin '".$this->_plugin_name."' is not readable but present!");
            }
        }
        if (!file_exists($this->_plugin_base."lang/en.xml")) {
            $this->global_error->addError("file_exists(".$this->_plugin_base."lang/en.xml)", "At least an english translation must exist for the plugin!");
        } else {
            if (!is_readable($this->_plugin_base."lang/en.xml")) {
                $this->global_error->addError("is_readable(".$this->_plugin_base."js/".$this->_plugin_name.".js)", "The english translation can't be read but is present!");
            }
        }
    }

    /**
     * create the xml template where plugin information are added to
     *
     * @param String $enc target encoding
     *
     * @return Void
     */
    private function _createXml($enc)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("Plugin_".$this->_plugin_name);
        $dom->appendChild($root);
        $this->xml = new SimpleXMLExtended(simplexml_import_dom($dom), $enc);
    }
}
