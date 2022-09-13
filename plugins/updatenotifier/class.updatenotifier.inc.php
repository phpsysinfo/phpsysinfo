<?php
/**
 * UpdateNotifier Plugin, which displays update notification from Ubuntu Landscape system
 *
 * @category  PHP
 * @package   PSI_Plugin_UpdateNotifier
 * @author    Damien ROTH <iysaak@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
        $buffer_info = "";
        if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) switch (strtolower(PSI_PLUGIN_UPDATENOTIFIER_ACCESS)) {
        case 'command':
            if (defined('PSI_PLUGIN_UPDATENOTIFIER_UBUNTU_LANDSCAPE_FORMAT') && PSI_PLUGIN_UPDATENOTIFIER_UBUNTU_LANDSCAPE_FORMAT) {
                CommonFunctions::executeProgram("/usr/lib/update-notifier/apt-check", "--human-readable", $buffer_info);
            } else {
                CommonFunctions::executeProgram("/usr/lib/update-notifier/apt-check", "2>&1", $buffer_info);
            }
            break;
        case 'data':
            if (!defined('PSI_EMU_HOSTNAME')) {
                if (defined('PSI_PLUGIN_UPDATENOTIFIER_FILE') && is_string(PSI_PLUGIN_UPDATENOTIFIER_FILE)) {
                    CommonFunctions::rfts(PSI_PLUGIN_UPDATENOTIFIER_FILE, $buffer_info);
                } else {
                    CommonFunctions::rfts("/var/lib/update-notifier/updates-available", $buffer_info);
                }
            }
            break;
        default:
            $this->global_error->addConfigError("__construct()", "[updatenotifier] ACCESS");
        }

        if (trim($buffer_info) != "") {
            // Remove blank lines
            $this->_filecontent = preg_split("/\r?\n/", $buffer_info, -1, PREG_SPLIT_NO_EMPTY);
        }
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

        if (defined('PSI_PLUGIN_UPDATENOTIFIER_UBUNTU_LANDSCAPE_FORMAT') && PSI_PLUGIN_UPDATENOTIFIER_UBUNTU_LANDSCAPE_FORMAT) {
            /*
             Ubuntu Landscape format:
             - line 1: packages to update
             - line 2: security packages to update
             */
            if ((count($this->_filecontent) >= 1) || (count($this->_filecontent) <= 3)) {
                foreach ($this->_filecontent as $line) {
                   if (preg_match("/^(\d+)\s/", $line, $num) && !preg_match("/UA Infra/", $line)) {
                       $this->_result[] = $num[1];
                   }
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
        if (!empty($this->_result) && is_numeric($this->_result[0])) {
            $xmluu = $this->xml->addChild("UpdateNotifier");
            $xmluu->addChild("packages", $this->_result[0]);
            if (isset($this->_result[1]) && is_numeric($this->_result[1])) {
                $xmluu->addChild("security", $this->_result[1]);
            } else {
                $xmluu->addChild("security", '0');
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
