<?php
/**
 * Uprecords plugin, which displays all uprecords informations available
 *
 * @category  PHP
 * @package   PSI_Plugin_Uprecords
 * @author    Ambrus Sandor Olah <aolah76@freemail.hu>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 1.0
 * @link      http://phpsysinfo.sourceforge.net
 */

class uprecords extends PSI_Plugin
{
    private $_lines;

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_lines = array();
    }

    /**
     * get uprecords information
     *
     * @return array uprecords in array with label
     */

    private function getUprecords()
    {
        $result = array();
        $i = 0;

        foreach ($this->_lines as $line) {
            if (($i > 1) and (strpos($line, '---') === false)) {
                $buffer = preg_split("/\s*[ |]\s+/", ltrim(ltrim($line, '->'), ' '));
                if (defined('PSI_PLUGIN_UPRECORDS_SHORT_MODE') &&
                   (PSI_PLUGIN_UPRECORDS_SHORT_MODE === true) &&
                   !is_numeric($buffer[0])) {
                    break;
                }

                if (strpos($line, '->') !== false) {
                   if (defined('PSI_PLUGIN_UPRECORDS_DENOTE_BY_ASTERISK') && (PSI_PLUGIN_UPRECORDS_DENOTE_BY_ASTERISK === true)) {
                        $buffer[0] .= ' *';
                    } else {
                        $buffer[0] = '-> '.$buffer[0];
                    }
                }

                if (count($buffer) > 4) {
                    $buffer[3] = $buffer[3].' '.$buffer[4];
                }

                $result[$i]['hash'] = $buffer[0];
                $result[$i]['Uptime'] = $buffer[1];
                $result[$i]['System'] = $buffer[2];
                //Date formating
                $result[$i]['Bootup'] = preg_replace("/^(\S+)(\s+)/", "$1,$2", preg_replace("/^(\S+\s+\S+\s+)(\d)(\s+)/", "$1 0$2$3", trim($buffer[3])." GMT"));
            }
            $i++;
        }

        return $result;
    }

    public function execute()
    {
        $this->_lines = array();
        switch (strtolower(PSI_PLUGIN_UPRECORDS_ACCESS)) {
            case 'command':
                $lines = "";
                $options = "";
                if (defined('PSI_PLUGIN_UPRECORDS_MAX_ENTRIES')) {
                    if (($ment = max(intval(PSI_PLUGIN_UPRECORDS_MAX_ENTRIES), 0)) != 10) {
                        $options=" -m ".$ment;
                    }
                }
                if (defined('PSI_PLUGIN_UPRECORDS_SHORT_MODE') && (PSI_PLUGIN_UPRECORDS_SHORT_MODE === true)) {
                    $options .= " -s";
                }
                if (CommonFunctions::executeProgram('TZ=GMT uprecords', '-a -w'.$options, $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            case 'data':
                if (CommonFunctions::rfts(PSI_APP_ROOT."/data/uprecords.txt", $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            default:
                $this->global_error->addConfigError("execute()", "[uprecords] ACCESS");
                break;
        }
    }

    public function xml()
    {
        if (empty($this->_lines))
            return $this->xml->getSimpleXmlElement();

        $arrBuff = $this->getUprecords();
        if (sizeof($arrBuff) > 0) {
            $uprecords = $this->xml->addChild("Uprecords");
            foreach ($arrBuff as $arrValue) {
                $item = $uprecords->addChild('Item');
                $item->addAttribute('hash', $arrValue['hash']);
                $item->addAttribute('Uptime', $arrValue['Uptime']);
                $item->addAttribute('System', $arrValue['System']);
                $item->addAttribute('Bootup', $arrValue['Bootup']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
