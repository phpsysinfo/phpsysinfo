<?php
/**
 * Viewer Plugin, which displays custom informations
 *
 * @category  PHP
 * @package   PSI_Plugin_Viewer
 * @author    erpomata
 * @copyright 2016 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 1.0
 * @link      http://phpsysinfo.sourceforge.net
 */

class Viewer extends PSI_Plugin
{
    private $_lines;

    private $name = "";

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_lines = array();
    }

    /**
     * get viewer information
     *
     * @return array viewer in array with label
     */

    private function getViewer()
    {
        $result = array();
        $i = 0;

        foreach ($this->_lines as $line) {
            $result[$i]['line'] = $line;
            $i++;
        }

        return $result;
    }

    public function execute()
    {
        $this->_lines = array();
        if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) switch (strtolower(PSI_PLUGIN_VIEWER_ACCESS)) {
        case 'command':
            if (defined('PSI_PLUGIN_VIEWER_COMMAND') && is_string(PSI_PLUGIN_VIEWER_COMMAND)) {
                if (defined('PSI_PLUGIN_VIEWER_PARAMS') && is_string(PSI_PLUGIN_VIEWER_PARAMS)) {
                    $params = PSI_PLUGIN_VIEWER_PARAMS;
                } else {
                    $params = "";
                }
                $this->name = trim(PSI_PLUGIN_VIEWER_COMMAND." ".$params);
                $lines = "";
                if ((PSI_OS == 'WINNT') && ($cp = CommonFunctions::getcp())) {
                    if (CommonFunctions::executeProgram('cmd', '/c chcp '.$cp.' >nul & '.$this->name, $lines) && !empty($lines))
                        $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                } else {
                    if (CommonFunctions::executeProgram(PSI_PLUGIN_VIEWER_COMMAND, $params, $lines) && !empty($lines))
                        $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                }
            } else {
                $this->global_error->addConfigError("execute()", "[viewer] COMMAND");
            }
            break;
        case 'data':
            if (!defined('PSI_EMU_HOSTNAME') && CommonFunctions::rftsdata("viewer.tmp", $lines) && !empty($lines))
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        default:
            $this->global_error->addConfigError("execute()", "[viewer] ACCESS");
        }
    }

    public function xml()
    {
        if (empty($this->_lines))
            return $this->xml->getSimpleXmlElement();

        $arrBuff = $this->getViewer();
        if (sizeof($arrBuff) > 0) {
            $viewer = $this->xml->addChild("Viewer");
            $viewer->addAttribute("Name", $this->name);
            foreach ($arrBuff as $arrValue) {
                $item = $viewer->addChild('Item');
                $item->addAttribute('Line', $arrValue['line']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
