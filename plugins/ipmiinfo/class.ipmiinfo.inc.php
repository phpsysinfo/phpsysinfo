<?php
/**
 * ipmiinfo Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_ipmiinfo
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.ipmiinfo.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
/**
 * ipmiinfo plugin, which displays all ipmi informations available
 *
 * @category  PHP
 * @package   PSI_Plugin_ipmiinfo
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class ipmiinfo extends PSI_Plugin
{
    private $_lines;

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);

        $this->_lines = array();
    }

    /**
     * get temperature information
     *
     * @return array temperatures in array with label
     */
    private function temperatures()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "degrees C" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                if ($buffer[8] != "na") $result[$i]['max'] = $buffer[8];
                $i++;
            }
        }

        return $result;
    }

    /**
     * get voltages information
     *
     * @return array voltage in array with label
     */
    private function voltages()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Volts" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                if ($buffer[5] != "na") $result[$i]['min'] = $buffer[5];
                if ($buffer[8] != "na") $result[$i]['max'] = $buffer[8];
                $i++;
            }
        }

        return $result;
    }

    /**
     * get fans information
     *
     * @return array fans in array with label
     */
    private function fans()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "RPM" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                if ($buffer[8] != "na") $result[$i]['min'] = $buffer[8];
                $i++;
            }
        }

        return $result;
    }

    /**
     * get powers information
     *
     * @return array misc in array with label
     */
    private function powers()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Watts" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                if ($buffer[8] != "na") $result[$i]['max'] = $buffer[8];
                $i++;
            }
        }

        return $result;
    }

    /**
     * get currents information
     *
     * @return array misc in array with label
     */
    private function currents()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "Amps" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                if ($buffer[8] != "na") $result[$i]['max'] = $buffer[8];
                $i++;
            }
        }

        return $result;
    }

    /**
     * get misc information
     *
     * @return array misc in array with label
     */
    private function misc()
    {
        $result = array ();
        $i = 0;
        foreach ($this->_lines as $line) {
            $buffer = preg_split("/\s*\|\s*/", $line);
            if ($buffer[2] == "discrete" && $buffer[3] != "na") {
                $result[$i]['label'] = $buffer[0];
                $result[$i]['value'] = $buffer[1];
                $result[$i]['state'] = $buffer[3];
                $i++;
            }
        }

        return $result;
    }

    public function execute()
    {
        $this->_lines = array();
        switch (strtolower(PSI_PLUGIN_IPMIINFO_ACCESS)) {
            case 'command':
                $lines = "";
                if (CommonFunctions::executeProgram('ipmitool', 'sensor', $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            case 'data':
                if (CommonFunctions::rfts(APP_ROOT."/data/ipmiinfo.txt", $lines) && !empty($lines))
                    $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
                break;
            default:
                $this->error->addConfigError('__construct()', 'PSI_PLUGIN_IPMIINFO_ACCESS');
                break;
        }
    }

    public function xml()
    {
        if ( empty($this->_lines))
        return $this->xml->getSimpleXmlElement();

        $arrBuff = $this->temperatures();
        if (sizeof($arrBuff) > 0) {
            $temp = $this->xml->addChild("Temperatures");
            foreach ($arrBuff as $arrValue) {
                $item = $temp->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
                if (isset($arrValue['Max'])) $item->addAttribute('Max', $arrValue['Max']);
            }
        }
        $arrBuff = $this->voltages();
        if (sizeof($arrBuff) > 0) {
            $volt = $this->xml->addChild('Voltages');
            foreach ($arrBuff as $arrValue) {
                $item = $volt->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
                if (isset($arrValue['Min'])) $item->addAttribute('Min', $arrValue['min']);
                if (isset($arrValue['Max'])) $item->addAttribute('Max', $arrValue['max']);
            }
        }
        $arrBuff = $this->fans();
        if (sizeof($arrBuff) > 0) {
            $fan = $this->xml->addChild('Fans');
            foreach ($arrBuff as $arrValue) {
                $item = $fan->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
                if (isset($arrValue['Min'])) $item->addAttribute('Min', $arrValue['min']);
            }
        }
        $arrBuff = $this->powers();
        if (sizeof($arrBuff) > 0) {
            $misc = $this->xml->addChild('Powers');
            foreach ($arrBuff as $arrValue) {
                $item = $misc->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
                if (isset($arrValue['Max'])) $item->addAttribute('Max', $arrValue['max']);
            }
        }
        $arrBuff = $this->currents();
        if (sizeof($arrBuff) > 0) {
            $misc = $this->xml->addChild('Currents');
            foreach ($arrBuff as $arrValue) {
                $item = $misc->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
                if (isset($arrValue['Max'])) $item->addAttribute('Max', $arrValue['max']);
            }
        }
        $arrBuff = $this->misc();
        if (sizeof($arrBuff) > 0) {
            $misc = $this->xml->addChild('Misc');
            foreach ($arrBuff as $arrValue) {
                $item = $misc->addChild('Item');
                $item->addAttribute('Label', $arrValue['label']);
                $item->addAttribute('Value', $arrValue['value']);
                $item->addAttribute('State', $arrValue['state']);
            }
        }

        return $this->xml->getSimpleXmlElement();
    }

}
