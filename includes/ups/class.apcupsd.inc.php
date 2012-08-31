<?php 
/**
 * Apcupsd class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Apcupsd.inc.php 417 2011-01-14 12:57:16Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting ups information from apcupsd program
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @author    Artem Volk <artvolk@mail.ru>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Apcupsd extends UPS
{
    /**
     * internal storage for all gathered data
     *
     * @var Array
     */
    private $_output = array();
    
    /**
     * get all information from all configured ups in config.php and store output in internal array
     */
    public function __construct()
    {
        parent::__construct();
        $upses = preg_split('/,/', PSI_UPS_APCUPSD_LIST, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($upses as $ups) {
            CommonFunctions::executeProgram('apcaccess', 'status '.trim($ups), $temp);
            if (! empty($temp)) {
                $this->_output[] = $temp;
            }
        }
    }
    
    /**
     * parse the input and store data in resultset for xml generation
     *
     * @return Void
     */
    private function _info()
    {
        foreach ($this->_output as $ups) {
        
            $dev = new UPSDevice();
        
            // General info
            if (preg_match('/^UPSNAME\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setName(trim($data[1]));
            }
            if (preg_match('/^MODEL\s*:\s*(.*)$/m', $ups, $data)) {
                $model=trim($data[1]);
                if (preg_match('/^APCMODEL\s*:\s*(.*)$/m', $ups, $data)) {
                    $dev->setModel($model.' ('.trim($data[1]).')');
                } else {
                    $dev->setModel($model);
                }
            }
            if (preg_match('/^UPSMODE\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setMode(trim($data[1]));
            }
            if (preg_match('/^STARTTIME\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setStartTime(trim($data[1]));
            }
            if (preg_match('/^STATUS\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setStatus(trim($data[1]));
            }
            if (preg_match('/^ITEMP\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setTemperatur(trim($data[1]));
            }
            // Outages
            if (preg_match('/^NUMXFERS\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setOutages(trim($data[1]));
            }
            if (preg_match('/^LASTXFER\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setLastOutage(trim($data[1]));
            }
            if (preg_match('/^XOFFBATT\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setLastOutageFinish(trim($data[1]));
            }
            // Line
            if (preg_match('/^LINEV\s*:\s*(\d*\.\d*)(.*)$/m', $ups, $data)) {
                $dev->setLineVoltage(trim($data[1]));
            }
            if (preg_match('/^LOADPCT\s*:\s*(\d*\.\d*)(.*)$/m', $ups, $data)) {
                $dev->setLoad(trim($data[1]));
            }
            // Battery
            if (preg_match('/^BATTV\s*:\s*(\d*\.\d*)(.*)$/m', $ups, $data)) {
                $dev->setBatteryVoltage(trim($data[1]));
            }
            if (preg_match('/^BCHARGE\s*:\s*(\d*\.\d*)(.*)$/m', $ups, $data)) {
                $dev->setBatterCharge(trim($data[1]));
            }
            if (preg_match('/^TIMELEFT\s*:\s*(\d*\.\d*)(.*)$/m', $ups, $data)) {
                $dev->setTimeLeft(trim($data[1]));
            }
            $this->upsinfo->setUpsDevices($dev);
        }
    }
    
    /**
     * get the information
     *
     * @see PSI_Interface_UPS::build()
     *
     * @return Void
     */
    function build()
    {
        $this->_info();
    }
}
?>
