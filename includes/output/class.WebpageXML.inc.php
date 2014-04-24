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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
     * only plugin xml
     *
     * @var boolean
     */
    private $_pluginRequest = false;

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
     * generate the output
     *
     * @return void
     */
    private function _prepare()
    {
        if (!$this->_pluginRequest) {
            // Figure out which OS we are running on, and detect support
            if (!file_exists(APP_ROOT.'/includes/os/class.'.PSI_OS.'.inc.php')) {
                $this->error->addError("file_exists(class.".PSI_OS.".inc.php)", PSI_OS." is not currently supported");
            }

            // check if there is a valid sensor configuration in config.php
            $foundsp = array();
            if ( defined('PSI_SENSOR_PROGRAM') && is_string(PSI_SENSOR_PROGRAM) ) {
                if (preg_match(ARRAY_EXP, PSI_SENSOR_PROGRAM)) {
                    $sensorprograms = eval(strtolower(PSI_SENSOR_PROGRAM));
                } else {
                    $sensorprograms = array(strtolower(PSI_SENSOR_PROGRAM));
                }
                foreach ($sensorprograms as $sensorprogram) {
                    if (!file_exists(APP_ROOT.'/includes/mb/class.'.$sensorprogram.'.inc.php')) {
                        $this->error->addError("file_exists(class.".htmlspecialchars($sensorprogram).".inc.php)", "specified sensor program is not supported");
                    } else {
                        $foundsp[] = $sensorprogram;
                    }
                }
            }

            /**
             * motherboard information
             *
             * @var serialized array
             */
            define('PSI_MBINFO', serialize($foundsp));

            // check if there is a valid hddtemp configuration in config.php
            $found = false;
            if (PSI_HDD_TEMP !== false) {
                $found = true;
            }
            /**
             * hddtemp information available or not
             *
             * @var boolean
             */
            define('PSI_HDDTEMP', $found);

            // check if there is a valid ups configuration in config.php
            $foundup = array();
            if ( defined('PSI_UPS_PROGRAM') && is_string(PSI_UPS_PROGRAM) ) {
                if (preg_match(ARRAY_EXP, PSI_UPS_PROGRAM)) {
                    $upsprograms = eval(strtolower(PSI_UPS_PROGRAM));
                } else {
                    $upsprograms = array(strtolower(PSI_UPS_PROGRAM));
                }
                foreach ($upsprograms as $upsprogram) {
                    if (!file_exists(APP_ROOT.'/includes/ups/class.'.$upsprogram.'.inc.php')) {
                        $this->error->addError("file_exists(class.".htmlspecialchars($upsprogram).".inc.php)", "specified UPS program is not supported");
                    } else {
                        $foundup[] = $upsprogram;
                    }
                }
            }
            /**
             * ups information
             *
             * @var serialized array
             */
            define('PSI_UPSINFO', serialize($foundup));

            // if there are errors stop executing the script until they are fixed
            if ($this->error->errorsExist()) {
                $this->error->errorsAsXML();
            }
        }

        // Create the XML
        if ($this->_pluginRequest) {
            $this->_xml = new XML(false, $this->_pluginName);
        } else {
            $this->_xml = new XML($this->_completeXML);
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
     * set parameters for the XML generation process
     *
     * @param boolean $completeXML switch for complete xml with all plugins
     * @param string  $plugin      name of the plugin
     *
     * @return void
     */
    public function __construct($completeXML, $plugin = null)
    {
        parent::__construct();
        if ($completeXML) {
            $this->_completeXML = true;
        }
        if ($plugin) {
            if (in_array(strtolower($plugin), CommonFunctions::getPlugins())) {
                $this->_pluginName = $plugin;
                $this->_pluginRequest = true;
            }
        }
        $this->_prepare();
    }
}
