<?php
/**
 * HyperV Plugin, which displays Hyper-V machines state
 *
 * @category  PHP
 * @package   PSI_Plugin_HyperV
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2017 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   $Id: class.hyperv.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
class HyperV extends PSI_Plugin
{

    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = array();

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc target encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
    }

    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array<br>the array is build like a tree,
     * so that it is possible to get only a specific process with the childs
     *
     * @return void
     */
    public function execute()
    {
        switch (strtolower(PSI_PLUGIN_HYPERV_ACCESS)) {
        case 'command':
            if (PSI_OS == 'WINNT') {
                try {
                    $objLocator = new COM('WbemScripting.SWbemLocator');
                    $wmic = $objLocator->ConnectServer('', 'root\CIMv2');
                    $buffer = CommonFunctions::getWMI($wmic, 'Win32_OperatingSystem', array('Version'));
                    if ($buffer && isset($buffer[0]) && isset($buffer[0]['Version'])) {
                        if (version_compare($buffer[0]['Version'], "6.2", ">=")) { // minimal windows 2012 or windows 8
                            $wmi = $objLocator->ConnectServer('', 'root\virtualization\v2');
                        } elseif (version_compare($buffer[0]['Version'], "6.0", ">=")) { // minimal windows 2008
                            $wmi = $objLocator->ConnectServer('', 'root\virtualization');
                        } else {
                           $this->global_error->addError("HyperV plugin", "Unsupported Windows version");
                           break;
                        }
                    } else {
                        $this->global_error->addError("HyperV plugin", "Unsupported Windows version");
                        break;
                    }
                    $result = CommonFunctions::getWMI($wmi, 'MSVM_ComputerSystem', array('InstallDate', 'EnabledState', 'ElementName'));
                    if (is_array($result)) foreach ($result as $machine) {
                        if ($machine['InstallDate'] !== null) {
                            $this->_filecontent[] = array($machine['ElementName'], $machine['EnabledState']);
                        }
                    }
                } catch (Exception $e) {
                }
            }
            break;
        case 'data':
            CommonFunctions::rfts(PSI_APP_ROOT."/data/hyperv.txt", $buffer);
            $processes = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($processes as $process) {
                $ps = preg_split("/[\s]?\|[\s]?/", $process, -1, PREG_SPLIT_NO_EMPTY);
                if (count($ps) == 2) {
                    $this->_filecontent[] = array(trim($ps[0]), trim($ps[1]));
                }
            }
            break;
        default:
            $this->global_error->addConfigError("execute()", "[hyperv] ACCESS");
            break;
        }
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        foreach ($this->_filecontent as $machine) {
            $xmlmach = $this->xml->addChild("Machine");
            $xmlmach->addAttribute("Name", $machine[0]);
            $xmlmach->addAttribute("State", $machine[1]);
        }

        return $this->xml->getSimpleXmlElement();
    }
}
