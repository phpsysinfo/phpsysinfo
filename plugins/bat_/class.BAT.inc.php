<?php 
/**
 * BAT Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_BAT
 * @author    Erkan VALENTIN <jacky672@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.BAT.inc.php 334 2009-09-16 15:21:39Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * BAT Plugin, which displays battery state
 *
 * @category  PHP
 * @package   PSI_Plugin_BAT
 * @author    Erkan VALENTIN <jacky672@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   $Id: class.BAT.inc.php 334 2009-09-16 15:21:39Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
class BAT extends PSI_Plugin
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
        switch (PSI_PLUGIN_BAT_ACCESS) {
        case 'command':
            CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/info', $buffer_info);
            CommonFunctions::rfts('/proc/acpi/battery/'.PSI_PLUGIN_BAT_DEVICE.'/state', $buffer_state);
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/bat_info.txt", $buffer_info);
            CommonFunctions::rfts(APP_ROOT."/data/bat_state.txt", $buffer_state);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_BAT_ACCESS");
            break;
        }
        $this->_filecontent['info'] = preg_split("/\n/", $buffer_info, -1, PREG_SPLIT_NO_EMPTY);
        $this->_filecontent['state'] = preg_split("/\n/", $buffer_state, -1, PREG_SPLIT_NO_EMPTY);
    }
    
    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array
     *
     * @return void
     */
    public function execute()
    {
        if ( empty($this->_filecontent)) {
            return;
        }
        foreach ($this->_filecontent['info'] as $roworig) {
            if (preg_match('/^design capacity\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_capacity'] = $data[1];
            }
            if (preg_match('/^design voltage\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['design_voltage'] = $data[1];
            }
        }
        foreach ($this->_filecontent['state'] as $roworig) {
            if (preg_match('/^remaining capacity\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['remaining_capacity'] = $data[1];
            }
            if (preg_match('/^present voltage\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['present_voltage'] = $data[1];
            }
            if (preg_match('/^charging state\s*:\s*(.*)$/m', trim($roworig), $data)) {
                $bat['charging_state'] = $data[1];
            }
        }
        $this->_result[0] = $bat;
    }
    
    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        foreach ($this->_result as $bat_item) {
            $xmlbat = $this->xml->addChild("Bat");
            $xmlbat->addAttribute("DesignCapacity", $bat_item['design_capacity']);
            $xmlbat->addAttribute("DesignVoltage", $bat_item['design_voltage']);
            $xmlbat->addAttribute("RemainingCapacity", $bat_item['remaining_capacity']);
            $xmlbat->addAttribute("PresentVoltage", $bat_item['present_voltage']);
            $xmlbat->addAttribute("ChargingState", $bat_item['charging_state']);
        }
        return $this->xml->getSimpleXmlElement();
    }
}
?>
