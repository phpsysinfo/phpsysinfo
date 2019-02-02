<?php
/**
 * PingTime Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_PingTest
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2017 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.pingtest.inc.php 1 2017-09-01 09:01:15Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class PingTest extends PSI_Plugin
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
     * @param String $enc target encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        if (defined('PSI_PLUGIN_PINGTEST_ADDRESSES') && is_string(PSI_PLUGIN_PINGTEST_ADDRESSES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_PINGTEST_ADDRESSES)) {
                $addresses = eval(PSI_PLUGIN_PINGTEST_ADDRESSES);
            } else {
                $addresses = array(PSI_PLUGIN_PINGTEST_ADDRESSES);
            }

            switch (strtolower(PSI_PLUGIN_PINGTEST_ACCESS)) {
            case 'command':
                if (PSI_OS == 'WINNT') {
                    $params = "-n 1";
                    if (defined('PSI_PLUGIN_PINGTEST_TIMEOUT')) {
                        if (($tout = max(intval(PSI_PLUGIN_PINGTEST_TIMEOUT), 0)) > 0) {
                            $params .= " -w ".(1000*$tout);
                        }
                    } else {
                        $params .= " -w 2000";
                    }

                } else {
                    $params = "-c 1";
                    if (defined('PSI_PLUGIN_PINGTEST_TIMEOUT')) {
                        if (($tout = max(intval(PSI_PLUGIN_PINGTEST_TIMEOUT), 0)) > 0) {
                            $params .= " -W ".$tout;
                        }
                    } else {
                        $params .= " -W 2";
                    }
                }
                foreach ($addresses as $address) {
                    CommonFunctions::executeProgram("ping".((strpos($address, ':') === false)?'':((PSI_OS !== 'WINNT')?'6':'')), $params." ".$address, $buffer, PSI_DEBUG);
                    if ((strlen($buffer) > 0) && preg_match("/[=<]([\d\.]+)\s*ms/", $buffer, $tmpout)) {
                        $this->_filecontent[] = array($address, $tmpout[1]);
                    }
                }
                break;
            case 'data':
                CommonFunctions::rfts(PSI_APP_ROOT."/data/pingtest.txt", $buffer);
                $addresses = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($addresses as $address) {
                    $pt = preg_split("/[\s]?\|[\s]?/", $address, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($pt) == 2) {
                        $this->_filecontent[] = array(trim($pt[0]), trim($pt[1]));
                    }
                }
                break;
            default:
                $this->global_error->addConfigError("__construct()", "[pingtest] ACCESS");
                break;
            }
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
        if (defined('PSI_PLUGIN_PINGTEST_ADDRESSES') && is_string(PSI_PLUGIN_PINGTEST_ADDRESSES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_PINGTEST_ADDRESSES)) {
                $addresses = eval(PSI_PLUGIN_PINGTEST_ADDRESSES);
            } else {
                $addresses = array(PSI_PLUGIN_PINGTEST_ADDRESSES);
            }
            foreach ($addresses as $address) {
                $this->_result[] = array($address, $this->address_inarray($address, $this->_filecontent));
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
        foreach ($this->_result as $pt) {
            $xmlps = $this->xml->addChild("Ping");
            $xmlps->addAttribute("Address", $pt[0]);
            $xmlps->addAttribute("PingTime", $pt[1]);
        }

        return $this->xml->getSimpleXmlElement();
    }

    /**
     * checks an array if pingtest address is in
     *
     * @param mixed $needle   what to find
     * @param array $haystack where to find
     *
     * @return pingtime - found<br>"lost" - not found
     */
    private function address_inarray($needle, $haystack)
    {
        foreach ($haystack as $stalk) {
            if ($needle === $stalk[0]) {
                return $stalk[1];
            }
        }

        return "lost";
    }
}
