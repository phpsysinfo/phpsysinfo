<?php
/**
 * PSStatus Plugin, which displays the status of configured processes
 * a simple view which shows a process name and the status
 * status determined by calling the "pidof" command line utility, another way is to provide
 * a file with the output of the pidof utility, so there is no need to run a executeable by the
 * webserver, the format of the command is written down in the phpsysinfo.ini file, where also
 * the method of getting the information is configured
 * processes that should be checked are also defined in phpsysinfo.ini
 *
 * @category  PHP
 * @package   PSI_Plugin_PSStatus
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class PSStatus extends PSI_Plugin
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
     * variable, which holds the current codepage
     * @var string
     */
    private $_enc;

    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc target encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        $this->_enc = $enc;
        if (defined('PSI_PLUGIN_PSSTATUS_PROCESSES') && is_string(PSI_PLUGIN_PSSTATUS_PROCESSES)) {
            switch (strtolower(PSI_PLUGIN_PSSTATUS_ACCESS)) {
            case 'command':
                if (preg_match(ARRAY_EXP, PSI_PLUGIN_PSSTATUS_PROCESSES)) {
                    $processes = eval(PSI_PLUGIN_PSSTATUS_PROCESSES);
                } else {
                    $processes = array(PSI_PLUGIN_PSSTATUS_PROCESSES);
                }
                if ((PSI_OS == 'WINNT') || (defined('PSI_EMU_HOSTNAME') && !defined('PSI_EMU_PORT'))) {
                    $short = true;
                    if (strcasecmp($enc, "UTF-8") == 0) {
                        foreach ($processes as $process) {
                            if (mb_strlen($process, "UTF-8") > 15) {
                                $short = false;
                                break;
                            }
                        }
                    } else {
                        foreach ($processes as $process) {
                            if (strlen($process) > 15) {
                                $short = false;
                                break;
                            }
                        }
                    }
                    if (!defined('PSI_EMU_HOSTNAME') && $short && CommonFunctions::executeProgram('qprocess', '*', $strBuf, false) && (strlen($strBuf) > 0)) {
                        $psdata = preg_split("/\r?\n/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
                        if (!empty($psdata)) foreach ($psdata as $psline) {
                            $psvalues = preg_split("/ /", $psline, -1, PREG_SPLIT_NO_EMPTY);
                            if ((count($psvalues) == 5) && is_numeric($psvalues[3])) {
                                $this->_filecontent[] = array(strtolower($psvalues[4]), $psvalues[3]);
                            }
                        }
                    }

                    if (!$short || (count($this->_filecontent) == 0)) {
                        try {
                            $wmi = WINNT::getcimv2wmi();
                            $process_wmi = WINNT::getWMI($wmi, 'Win32_Process', array('Caption', 'ProcessId'));
                            foreach ($process_wmi as $process) {
                                $this->_filecontent[] = array(strtolower(trim($process['Caption'])), trim($process['ProcessId']));
                            }
                        } catch (Exception $e) {
                        }
                    }
                } elseif ((PSI_OS != 'WINNT') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT'))) {
                    if (defined('PSI_PLUGIN_PSSTATUS_USE_REGEX') && PSI_PLUGIN_PSSTATUS_USE_REGEX) {
                        foreach ($processes as $process) {
                            CommonFunctions::executeProgram("pgrep", "-n -x \"".$process."\"", $buffer, PSI_DEBUG);
                            if (strlen($buffer) > 0) {
                                $this->_filecontent[] = array($process, $buffer);
                            }
                        }
                    } else {
                        foreach ($processes as $process) {
                            CommonFunctions::executeProgram("pidof", "-s -x \"".$process."\"", $buffer, PSI_DEBUG);
                            if (strlen($buffer) > 0) {
                                $this->_filecontent[] = array($process, $buffer);
                            }
                        }
                    }
                }
                break;
            case 'data':
                if (!defined('PSI_EMU_HOSTNAME')) {
                    CommonFunctions::rftsdata("psstatus.tmp", $buffer);
                    $processes = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($processes as $process) {
                        $ps = preg_split("/[\s]?\|[\s]?/", $process, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($ps) == 2) {
                            $this->_filecontent[] = array(trim($ps[0]), trim($ps[1]));
                        }
                    }
                }
                break;
            default:
                $this->global_error->addConfigError("__construct()", "[psstatus] ACCESS");
            }
        }
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
        if (defined('PSI_PLUGIN_PSSTATUS_PROCESSES') && is_string(PSI_PLUGIN_PSSTATUS_PROCESSES)) {
            if (((PSI_OS == 'WINNT') || (defined('PSI_EMU_HOSTNAME') && !defined('PSI_EMU_PORT'))) &&
               (strtolower(PSI_PLUGIN_PSSTATUS_ACCESS) == 'command')) {
                $strBuf = PSI_PLUGIN_PSSTATUS_PROCESSES;
                if (defined('PSI_EMU_HOSTNAME')) {
                    WINNT::convertCP($strBuf, $this->_enc);
                }
                if (preg_match(ARRAY_EXP, $strBuf)) {
                    $processes = eval($strBuf);
                } else {
                    $processes = array($strBuf);
                }

                foreach ($processes as $process) {
                    $this->_result[] = array($process, $this->process_inarray(strtolower($process), $this->_filecontent));
                }
            } else {
                if (preg_match(ARRAY_EXP, PSI_PLUGIN_PSSTATUS_PROCESSES)) {
                    $processes = eval(PSI_PLUGIN_PSSTATUS_PROCESSES);
                } else {
                    $processes = array(PSI_PLUGIN_PSSTATUS_PROCESSES);
                }

                foreach ($processes as $process) {
                    $this->_result[] = array($process, $this->process_inarray($process, $this->_filecontent));
                }
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
        if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT') || !empty($this->_filecontent)) foreach ($this->_result as $ps) {
            $xmlps = $this->xml->addChild("Process");
            $xmlps->addAttribute("Name", $ps[0]);
            $xmlps->addAttribute("Status", $ps[1] ? 1 : 0);
        }

        return $this->xml->getSimpleXmlElement();
    }

    /**
     * checks an array if process name is in
     *
     * @param mixed $needle   what to find
     * @param array $haystack where to find
     *
     * @return boolean true - found<br>false - not found
     */
    private function process_inarray($needle, $haystack)
    {
        foreach ($haystack as $stalk) {
            if ($needle === $stalk[0]) {
                return true;
            }
        }

        return false;
    }
}
