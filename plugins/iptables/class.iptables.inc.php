<?php
/**
 * Iptables Plugin, which displays all iptables informations available
 *
 * @category  PHP
 * @package   PSI_Plugin_Iptables
 * @author    erpomata
 * @copyright 2016 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 1.0
 * @link      http://phpsysinfo.sourceforge.net
 */

class iptables extends PSI_Plugin
{
    private $_lines;

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_lines = array();
    }

    /**
     * get iptables information
     *
     * @return array iptables in array with label
     */

    private function getIptables()
    {
        $result = array();
        $i = 0;

        foreach ($this->_lines as $line) {
            $result[$i]['rule'] = $line;
            $i++;
        }

        return $result;
    }

    public function execute()
    {
        $this->_lines = array();
        switch (strtolower(PSI_PLUGIN_IPTABLES_ACCESS)) {
            case 'command':
                $lines = "";
                if (CommonFunctions::executeProgram('iptables-save', "", $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            case 'data':
                if (CommonFunctions::rfts(PSI_APP_ROOT."/data/iptables.txt", $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            default:
                $this->global_error->addConfigError("execute()", "[iptables] ACCESS");
                break;
        }
    }

    public function xml()
    {
        if (empty($this->_lines))
            return $this->xml->getSimpleXmlElement();

        $arrBuff = $this->getIptables();
        if (sizeof($arrBuff) > 0) {
            $iptables = $this->xml->addChild("iptables");
            foreach ($arrBuff as $arrValue) {
                $item = $iptables->addChild('Item');
                $item->addAttribute('Rule', $arrValue['rule']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }
}
