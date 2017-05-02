<?php
/**
 * Pmset class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Robert Pelletier <drizzt@menzonet.org>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.nut.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting ups information from pmset program
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Robert Pelletier <drizzt@menzonet.org>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Pmset extends UPS
{
    /**
     * internal storage for all gathered data
     *
     * @var array
     */
    private $_output = array();

    /**
     * get all information from all configured ups and store output in internal array
     */
    public function __construct()
    {
        parent::__construct();
        $temp = "";
        if (CommonFunctions::executeProgram('pmset', '-g batt', $temp) && !empty($temp)) {
            $this->_output[] = $temp;
        }
    }

    /**
     * parse the input and store data in resultset for xml generation
     *
     * @return void
     */
   private function _info()
    {
        if (empty($this->_output)) {
            return;
        }
        $model = array();
        $percCharge = array();
        $lines = explode(PHP_EOL, implode($this->_output));
        if (count($lines)>1) {
            $model = explode('FW:', $lines[1]);
            if (strpos($model[0], 'InternalBattery') === false) {
                $dev = new UPSDevice();
                $percCharge = explode(';', $lines[1]);
                $dev->setName('UPS');
                if ($model !== false) {
                    $dev->setModel(substr(trim($model[0]), 1));
                }
                if ($percCharge !== false) {
                    $dev->setBatterCharge(trim(substr($percCharge[0], -4, 3)));
                    $dev->setStatus(trim($percCharge[1]));
                    if (isset($percCharge[2])) {
                        $time = explode(':', $percCharge[2]);
                        $hours = $time[0];
                        $minutes = $hours*60+substr($time[1], 0, 2);
                        $dev->setTimeLeft($minutes);
                    }
                }
                $dev->setMode("pmset");
                $this->upsinfo->setUpsDevices($dev);
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_UPS::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_info();
    }
}
