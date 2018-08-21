<?php
/**
 * healthd sensor class, getting information from healthd
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
class Healthd extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_values = array();

    /**
     * fill the private content var through command or data access
     */
    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_HEALTHD_ACCESS')?strtolower(PSI_SENSOR_HEALTHD_ACCESS):'command') {
        case 'command':
            if (CommonFunctions::executeProgram('healthdc', '-t', $lines)) {
                $lines0 = preg_split("/\n/", $lines, 1, PREG_SPLIT_NO_EMPTY);
                if (count($lines0) == 1) {
                    $this->_values = preg_split("/\t+/", $lines0[0]);
                }
            }
            break;
        case 'data':
            if (CommonFunctions::rfts(PSI_APP_ROOT.'/data/healthd.txt', $lines)) {
                $lines0 = preg_split("/\n/", $lines, 1, PREG_SPLIT_NO_EMPTY);
                if (count($lines0) == 1) {
                    $this->_values = preg_split("/\t+/", $lines0[0]);
                }
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_healthd] ACCESS');
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
        if (count($this->_values) == 14) {
            $dev1 = new SensorDevice();
            $dev1->setName('temp1');
            $dev1->setValue($this->_values[1]);
//            $dev1->setMax(70);
            $this->mbinfo->setMbTemp($dev1);
            $dev2 = new SensorDevice();
            $dev2->setName('temp1');
            $dev2->setValue($this->_values[2]);
//            $dev2->setMax(70);
            $this->mbinfo->setMbTemp($dev2);
            $dev3 = new SensorDevice();
            $dev3->setName('temp1');
            $dev3->setValue($this->_values[3]);
//            $dev3->setMax(70);
            $this->mbinfo->setMbTemp($dev3);
        }
    }

    /**
     * get fan information
     *
     * @return void
     */
    private function _fans()
    {
        if (count($this->_values) == 14) {
            $dev1 = new SensorDevice();
            $dev1->setName('fan1');
            $dev1->setValue($this->_values[4]);
//            $dev1->setMin(3000);
            $this->mbinfo->setMbFan($dev1);
            $dev2 = new SensorDevice();
            $dev2->setName('fan2');
            $dev2->setValue($this->_values[5]);
//            $dev2->setMin(3000);
            $this->mbinfo->setMbFan($dev2);
            $dev3 = new SensorDevice();
            $dev3->setName('fan3');
            $dev3->setValue($this->_values[6]);
//            $dev3->setMin(3000);
            $this->mbinfo->setMbFan($dev3);
        }
    }

    /**
     * get voltage information
     *
     * @return void
     */
    private function _voltage()
    {
        if (count($this->_values) == 14) {
            $dev1 = new SensorDevice();
            $dev1->setName('Vcore1');
            $dev1->setValue($this->_values[7]);
            $this->mbinfo->setMbVolt($dev1);
            $dev2 = new SensorDevice();
            $dev2->setName('Vcore2');
            $dev2->setValue($this->_values[8]);
            $this->mbinfo->setMbVolt($dev2);
            $dev3 = new SensorDevice();
            $dev3->setName('3volt');
            $dev3->setValue($this->_values[9]);
            $this->mbinfo->setMbVolt($dev3);
            $dev4 = new SensorDevice();
            $dev4->setName('+5Volt');
            $dev4->setValue($this->_values[10]);
            $this->mbinfo->setMbVolt($dev4);
            $dev5 = new SensorDevice();
            $dev5->setName('+12Volt');
            $dev5->setValue($this->_values[11]);
            $this->mbinfo->setMbVolt($dev5);
            $dev6 = new SensorDevice();
            $dev6->setName('-12Volt');
            $dev6->setValue($this->_values[12]);
            $this->mbinfo->setMbVolt($dev6);
            $dev7 = new SensorDevice();
            $dev7->setName('-5Volt');
            $dev7->setValue($this->_values[13]);
            $this->mbinfo->setMbVolt($dev7);
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
        $this->_fans();
        $this->_voltage();
    }
}
