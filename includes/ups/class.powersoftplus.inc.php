<?php
/**
 * PowerSoftPlus class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.powersoftplus.inc.php 661 2014-06-13 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
*/
/**
 * getting ups information from powersoftplus program
 *
 * @category  PHP
 * @package   PSI_UPS
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2014 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
*/
class PowerSoftPlus extends UPS
{
    /**
     * internal storage for all gathered data
     *
     * @var Array
     */
    private $_output = array();

    /**
     * get all information from all configured ups in phpsysinfo.ini and store output in internal array
     */
    public function __construct()
    {
        parent::__construct();
        CommonFunctions::executeProgram('powersoftplus', '-p', $temp);
        if (! empty($temp)) {
            $this->_output[] = $temp;
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
            $dev->setName("EVER");
            $dev->setMode("PowerSoftPlus");
            $maxpwr = 0;
            $load = null;
            if (preg_match('/^Identifier: UPS Model\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setModel(trim($data[1]));
                if (preg_match('/\s(\d*)[^\d]*$/', trim($data[1]), $number)) {
                    $maxpwr=$number[1]*0.65;
                }
            }
            if (preg_match('/^Current UPS state\s*:\s*(.*)$/m', $ups, $data)) {
                $dev->setStatus(trim($data[1]));
            }
            if (preg_match('/^Output load\s*:\s*(.*)\s\[\%\]$/m', $ups, $data)) {
               $load = trim($data[1]);
            }
            //wrong Output load issue
            if (($load == 0) && ($maxpwr != 0) && preg_match('/^Effective power\s*:\s*(.*)\s\[W\]$/m', $ups, $data)) {
                $load = 100.0*trim($data[1])/$maxpwr;
            }
            if ($load != null) {
                $dev->setLoad($load);
            }
            // Battery
            if (preg_match('/^Battery voltage\s*:\s*(.*)\s\[Volt\]$/m', $ups, $data)) {
                $dev->setBatteryVoltage(trim($data[1]));
            }
            if (preg_match('/^Battery state\s*:\s*(.*)$/m', $ups, $data)) {
                if (preg_match('/^At full capacity$/', trim($data[1]))) {
                    $dev->setBatterCharge(100);
                } elseif (preg_match('/^(Discharged)|(Depleted)$/', trim($data[1]))) {
                    $dev->setBatterCharge(0);
                }
            }
            // Line
            if (preg_match('/^Input voltage\s*:\s*(.*)\s\[Volt\]$/m', $ups, $data)) {
                $dev->setLineVoltage(trim($data[1]));
            }
            if (preg_match('/^Input frequency\s*:\s*(.*)\s\[Hz\]$/m', $ups, $data)) {
                $dev->setLineFrequency(trim($data[1]));
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
