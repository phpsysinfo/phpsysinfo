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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
        CommonFunctions::executeProgram('pmset', '-g batt', $temp);
        if (! empty($temp)) {
            $this->_output[] = $temp;
        }
    }

    /**
     * parse the input and store data in resultset for xml generation
     *
     * @return array
     */
   private function _info()
    {
        $model = array();
        $percCharge = array();
        $lines = explode(PHP_EOL, implode($this->_output));
        $dev = new UPSDevice();
        $model = explode('FW:',  $lines[1]);
        if (strpos($model[0], 'InternalBattery') === FALSE) {
            $percCharge = explode(';',  $lines[1]);
            $dev->setName('UPS');
            if ($model !== FALSE) {
                $dev->setModel(substr(trim($model[0]), 1));
            }
            if ($percCharge !== FALSE) {
                $dev->setBatterCharge(trim(substr($percCharge[0], -4, 3)));
                $dev->setStatus(trim($percCharge[1]));
                if (isset($percCharge[2])) {
                    $time = explode(':', $percCharge[2]);
                    $hours = $time[0];
                    $minutes = $hours*60+substr($time[1], 0, 2);
                    $dev->setTimeLeft($minutes);
                }
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
    public function build()
    {
        $this->_info();
    }
}
