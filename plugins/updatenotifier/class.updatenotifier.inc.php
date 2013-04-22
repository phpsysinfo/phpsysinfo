<?php
/**
 * UpdateNotifier Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_UpdateNotifier
 * @author    Damien ROTH <iysaak@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.updatenotifier.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * UpdateNotifier Plugin, which displays update notification from Ubuntu Landscape system
 *
 * @category  PHP
 * @package   PSI_Plugin_UpdateNotifier
 * @author    Damien ROTH <iysaak@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   $Id: class.updatenotifier.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class UpdateNotifier extends PSI_Plugin
{
    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = array();

    /**
     * variable, which holds the result before the xml is generated out of this array
     * @var array
     */
    private $_result = array();

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        CommonFunctions::rfts(PSI_PLUGIN_UPDATENOTIFIER_FILE, $buffer_info);
        // Remove blank lines
        $this->_filecontent = preg_split("/\n/", $buffer_info, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array
     *
     * @return void
     */
    public function execute()
    {
        if (empty($this->_filecontent)) {
            return;
        }

        if (PSI_PLUGIN_UPDATENOTIFIER_UBUNTU_LANDSCAPE_FORMAT === true) {
            /*
             Ubuntu Landscape format:
             - line 1: packages to update
             - line 2: security packages to update
             */
            if (count($this->_filecontent) == 2) {
                foreach ($this->_filecontent as $line) {
                    list($num, $text) = explode(" ", $line, 2);
                    $this->_result[] = $num;
                }
            } else {
                $this->global_error->addWarning("Unable to parse UpdateNotifier file");
            }
        } else {
            /*
             Universal format: A;B
             - A: packages to update
             - B: security packages to update
             */
            if (count($this->_filecontent) == 1 && strpos($this->_filecontent[0], ";") !== false) {
                $this->_result = explode(";", $this->_filecontent[0]);
            } else {
                $this->global_error->addWarning("Unable to parse UpdateNotifier file");
            }
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        if (!empty($this->_result)) {
            $xmluu = $this->xml->addChild("UpdateNotifier");
            $xmluu->addChild("packages", $this->_result[0]);
            $xmluu->addChild("security", $this->_result[1]);
        }

        return $this->xml->getSimpleXmlElement();
    }
}
