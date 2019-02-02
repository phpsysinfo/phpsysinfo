<?php
/**
 * K8Temp sensor class, getting information from k8temp
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class K8Temp extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_lines = array();

    /**
     * fill the private array
     */
    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_K8TEMP_ACCESS')?strtolower(PSI_SENSOR_K8TEMP_ACCESS):'command') {
        case 'command':
            $lines = "";
            CommonFunctions::executeProgram('k8temp', '', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'data':
            if (CommonFunctions::rfts(PSI_APP_ROOT.'/data/k8temp.txt', $lines)) {
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_k8temp] ACCESS');
            break;
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        foreach ($this->_lines as $line) {
            if (preg_match('/(.*):\s*(\d*)/', $line, $data)) {
                if ($data[2] > 0) {
                    $dev = new SensorDevice();
                    $dev->setName($data[1]);
//                    $dev->setMax('70.0');
                    if ($data[2] < 250) {
                        $dev->setValue($data[2]);
                    }
                    $this->mbinfo->setMbTemp($dev);
                }
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_temperature();
    }
}
