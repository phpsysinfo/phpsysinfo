<?php
/**
 * XML Generator class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.WebpageXML.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for xml output
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class WebpageXML extends Output implements PSI_Interface_Output
{
    /**
     * xml object that holds the generated xml
     *
     * @var XML
     */
    private $_xml;

    /**
     * complete xml
     *
     * @var boolean
     */
    private $_completeXML = false;

    /**
     * name of the plugin
     *
     * @var string
     */
    private $_pluginName = null;

    /**
     * name of the block
     *
     * @var string
     */
    private $_blockName = null;

    /**
     * generate the output
     *
     * @return void
     */
    private function _prepare()
    {
        if ($this->_pluginName === null) {
            // Figure out which OS we are running on, and detect support
            if (!file_exists(PSI_APP_ROOT.'/includes/os/class.'.PSI_OS.'.inc.php')) {
                $this->error->addError("file_exists(class.".PSI_OS.".inc.php)", PSI_OS." is not currently supported");
            }

            if (!defined('PSI_MBINFO') && (!$this->_blockName || in_array($this->_blockName, array('voltage','current','temperature','fans','power','other')))) {
                // check if there is a valid sensor configuration in phpsysinfo.ini
                $foundsp = array();
                if (defined('PSI_SENSOR_PROGRAM') && is_string(PSI_SENSOR_PROGRAM)) {
                    if (preg_match(ARRAY_EXP, PSI_SENSOR_PROGRAM)) {
                        $sensorprograms = eval(strtolower(PSI_SENSOR_PROGRAM));
                    } else {
                        $sensorprograms = array(strtolower(PSI_SENSOR_PROGRAM));
                    }
                    foreach ($sensorprograms as $sensorprogram) {
                        if (!file_exists(PSI_APP_ROOT.'/includes/mb/class.'.$sensorprogram.'.inc.php')) {
                            $this->error->addError("file_exists(class.".htmlspecialchars($sensorprogram).".inc.php)", "specified sensor program is not supported");
                        } else {
                            $foundsp[] = $sensorprogram;
                        }
                    }
                }

                /**
                 * motherboard information
                 *
                 * @var string serialized array
                 */
                define('PSI_MBINFO', serialize($foundsp));
            }

            if (!defined('PSI_UPSINFO') && (!$this->_blockName || ($this->_blockName==='ups'))) {
                // check if there is a valid ups configuration in phpsysinfo.ini
                $foundup = array();
                if (defined('PSI_UPS_PROGRAM') && is_string(PSI_UPS_PROGRAM)) {
                    if (preg_match(ARRAY_EXP, PSI_UPS_PROGRAM)) {
                        $upsprograms = eval(strtolower(PSI_UPS_PROGRAM));
                    } else {
                        $upsprograms = array(strtolower(PSI_UPS_PROGRAM));
                    }
                    foreach ($upsprograms as $upsprogram) {
                        if (!file_exists(PSI_APP_ROOT.'/includes/ups/class.'.$upsprogram.'.inc.php')) {
                            $this->error->addError("file_exists(class.".htmlspecialchars($upsprogram).".inc.php)", "specified UPS program is not supported");
                        } else {
                            $foundup[] = $upsprogram;
                        }
                    }
                }
                /**
                 * ups information
                 *
                 * @var string serialized array
                 */
                define('PSI_UPSINFO', serialize($foundup));
            }

            // if there are errors stop executing the script until they are fixed
            if ($this->error->errorsExist()) {
                $this->error->errorsAsXML();
            }

            // Create the XML
            $this->_xml = new XML($this->_completeXML, '', $this->_blockName);
        } else {
            // Create the XML
            $this->_xml = new XML(false, $this->_pluginName);
        }
    }

    /**
     * render the output
     *
     * @return void
     */
    public function run()
    {
        header("Cache-Control: no-cache, must-revalidate\n");
        header("Content-Type: text/xml\n\n");
        $xml = $this->_xml->getXml();
        echo $xml->asXML();
    }

    /**
     * get XML as pure string
     *
     * @return string
     */
    public function getXMLString()
    {
        $xml = $this->_xml->getXml();

        return $xml->asXML();
    }

    /**
     * get json string
     *
     * @return string
     */
    public function getJsonString()
    {
        if (defined('PSI_JSON_ISSUE') && (PSI_JSON_ISSUE)) {
            return json_encode(simplexml_load_string(str_replace(">", ">\n", $this->getXMLString()))); // solving json_encode issue
        } else {
            return json_encode(simplexml_load_string($this->getXMLString()));
        }
    }

    /**
     * get array
     *
     * @return array
     */
    public function getArray()
    {
        return json_decode($this->getJsonString());
    }

    /**
     * set parameters for the XML generation process
     *
     * @param string $plugin name of the plugin, block or 'complete' for all plugins
     *
     * @return void
     */
    public function __construct($plugin = "")
    {
        parent::__construct();

        if (is_string($plugin) && ($plugin !== "")) {
            $plugin = strtolower($plugin);
            if ($plugin === "complete") {
                $this->_completeXML = true;
            } else {
                $validblocks = array('vitals','hardware','memory','filesystem','network','voltage','current','temperature','fans','power','other','ups');
                if (in_array($plugin, $validblocks)) {
                    $this->_blockName = $plugin;
                } elseif (in_array($plugin, CommonFunctions::getPlugins())) {
                    $this->_pluginName = $plugin;
                } else {
                    $this->_blockName = ' '; //disable all blocks
                }
            }
        }
        $this->_prepare();
    }
}
