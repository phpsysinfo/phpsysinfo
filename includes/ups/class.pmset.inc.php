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
        if (defined('PSI_UPS_PMSET_ACCESS') && (strtolower(trim(PSI_UPS_PMSET_ACCESS))==='data')) {
            if (CommonFunctions::rftsdata('upspmset.tmp', $temp)) {
                $this->_output[] = $temp;
            }
        } elseif (PSI_OS == 'Darwin') {
            if (CommonFunctions::executeProgram('pmset', '-g batt', $temp) && !empty($temp)) {
                $this->_output[] = $temp;
            }
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
            if (strpos($lines[1], 'InternalBattery') === false) {
                $dev = new UPSDevice();
                $dev->setName('UPS');
                $percCharge = explode(';', $lines[1]);
                $model = explode('FW:', $lines[1]);
                if ($model !== false) {
                    $dev->setModel(substr(trim($model[0]), 1));
                }
                if ($percCharge !== false) {
                    if (preg_match("/\s(\d+)\%$/", trim($percCharge[0]), $tmpbuf)) {
                        if ($tmpbuf[1]>100) {
                            $dev->setBatterCharge(100);
                        } else {
                            $dev->setBatterCharge($tmpbuf[1]);
                        }
                    }
                    $percCharge[1]=trim($percCharge[1]);
                    if (preg_match("/^(.+) present:/", $percCharge[1], $tmpbuf)) {
                        $dev->setStatus(trim($tmpbuf[1]));
                    } else {
                        $dev->setStatus($percCharge[1]);
                    }
                    if (isset($percCharge[2]) && preg_match("/\s(\d+):(\d+)\s/", $percCharge[2], $tmpbuf)) {
                         $dev->setTimeLeft($tmpbuf[1]*60+$tmpbuf[2]);
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
     * @return void
     */
    public function build()
    {
        $this->_info();
    }
}
