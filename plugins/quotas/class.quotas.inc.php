<?php
/**
 * Quotas Plugin, which displays all quotas on the machine
 * display all quotas in a sortable table with the current values which are determined by
 * calling the "repquota" command line utility, another way is to provide
 * a file with the output of the repquota utility, so there is no need to run a execute by the
 * webserver, the format of the command is written down in the phpsysinfo.ini file, where also
 * the method of getting the information is configured
 *
 * @category  PHP
 * @package   PSI_Plugin_Quotas
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Quotas extends PSI_Plugin
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
        $buffer = "";
        if ((PSI_OS != 'WINNT') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT'))) switch (strtolower(PSI_PLUGIN_QUOTAS_ACCESS)) {
        case 'command':
            CommonFunctions::executeProgram("repquota", "-au", $buffer, PSI_DEBUG);
            break;
        case 'data':
            if (!defined('PSI_EMU_HOSTNAME')) {
                CommonFunctions::rftsdata("quotas.tmp", $buffer);
            }
            break;
        default:
            $this->global_error->addConfigError("__construct()", "[quotas] ACCESS");
        }
        if (trim($buffer) != "") {
            $this->_filecontent = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
            unset($this->_filecontent[0]);
        } else {
            $this->_filecontent = array();
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
        $i = 0;
        $quotas = array();
        foreach ($this->_filecontent as $thisline) {
            $thisline = preg_replace("/([\s][-+][-+])/", "", $thisline);
            $thisline = preg_split("/(\s)/", $thisline, -1, PREG_SPLIT_NO_EMPTY);
            $params = array();
            foreach ($thisline as $param) if (is_numeric($param)) {
                $params[] = $param;
            }
            if (($paramscount = count($params)) == 6) {
                $quotas[$i]['user'] = trim($thisline[0], " \t+-");
                $quotas[$i]['byte_used'] = $params[0] * 1024;
                $quotas[$i]['byte_soft'] = $params[1] * 1024;
                $quotas[$i]['byte_hard'] = $params[2] * 1024;
                if ($quotas[$i]['byte_hard'] != 0) {
                    $quotas[$i]['byte_percent_used'] = round((($quotas[$i]['byte_used'] / $quotas[$i]['byte_hard']) * 100), 1);
                } else {
                    $quotas[$i]['byte_percent_used'] = 0;
                }
                $quotas[$i]['file_used'] = $params[3];
                $quotas[$i]['file_soft'] = $params[4];
                $quotas[$i]['file_hard'] = $params[5];
                if ($quotas[$i]['file_hard'] != 0) {
                    $quotas[$i]['file_percent_used'] = round((($quotas[$i]['file_used'] / $quotas[$i]['file_hard']) * 100), 1);
                } else {
                    $quotas[$i]['file_percent_used'] = 0;
                }
                $i++;
            }
        }
        $this->_result = $quotas;
    }

    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        foreach ($this->_result as $quota) {
            $quotaChild = $this->xml->addChild("Quota");
            $quotaChild->addAttribute("User", $quota['user']);
            $quotaChild->addAttribute("ByteUsed", $quota['byte_used']);
            $quotaChild->addAttribute("ByteSoft", $quota['byte_soft']);
            $quotaChild->addAttribute("ByteHard", $quota['byte_hard']);
            $quotaChild->addAttribute("BytePercentUsed", $quota['byte_percent_used']);
            $quotaChild->addAttribute("FileUsed", $quota['file_used']);
            $quotaChild->addAttribute("FileSoft", $quota['file_soft']);
            $quotaChild->addAttribute("FileHard", $quota['file_hard']);
            $quotaChild->addAttribute("FilePercentUsed", $quota['file_percent_used']);
        }

        return $this->xml->getSimpleXmlElement();
    }
}
