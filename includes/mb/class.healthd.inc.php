<?php
/**
 * healthd sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.healthd.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from healthd
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
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
    private $_lines = array();

    /**
     * fill the private content var through command or data access
     */
    public function __construct()
    {
        parent::__construct();
        switch (defined('PSI_SENSOR_HEALTHD_ACCESS')?strtolower(PSI_SENSOR_HEALTHD_ACCESS):'command') {
        case 'command':
            $lines = "";
            CommonFunctions::executeProgram('healthdc', '-t', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'data':
            if (CommonFunctions::rfts(APP_ROOT.'/data/healthd.txt', $lines)) {
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_HEALTHD_ACCESS');
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
        $ar_buf = preg_split("/\t+/", $this->_lines);
        $dev1 = new SensorDevice();
        $dev1->setName('temp1');
        $dev1->setValue($ar_buf[1]);
//        $dev1->setMax(70);
        $this->mbinfo->setMbTemp($dev1);
        $dev2 = new SensorDevice();
        $dev2->setName('temp1');
        $dev2->setValue($ar_buf[2]);
//        $dev2->setMax(70);
        $this->mbinfo->setMbTemp($dev2);
        $dev3 = new SensorDevice();
        $dev3->setName('temp1');
        $dev3->setValue($ar_buf[3]);
//        $dev3->setMax(70);
        $this->mbinfo->setMbTemp($dev3);
    }

    /**
     * get fan information
     *
     * @return void
     */
    private function _fans()
    {
        $ar_buf = preg_split("/\t+/", $this->_lines);
        $dev1 = new SensorDevice();
        $dev1->setName('fan1');
        $dev1->setValue($ar_buf[4]);
//        $dev1->setMin(3000);
        $this->mbinfo->setMbFan($dev1);
        $dev2 = new SensorDevice();
        $dev2->setName('fan2');
        $dev2->setValue($ar_buf[5]);
//        $dev2->setMin(3000);
        $this->mbinfo->setMbFan($dev2);
        $dev3 = new SensorDevice();
        $dev3->setName('fan3');
        $dev3->setValue($ar_buf[6]);
//        $dev3->setMin(3000);
        $this->mbinfo->setMbFan($dev3);
    }

    /**
     * get voltage information
     *
     * @return array voltage in array with lable
     */
    private function _voltage()
    {
        $ar_buf = preg_split("/\t+/", $this->_lines);
        $dev1 = new SensorDevice();
        $dev1->setName('Vcore1');
        $dev1->setValue($ar_buf[7]);
        $this->mbinfo->setMbVolt($dev1);
        $dev2 = new SensorDevice();
        $dev2->setName('Vcore2');
        $dev2->setValue($ar_buf[8]);
        $this->mbinfo->setMbVolt($dev2);
        $dev3 = new SensorDevice();
        $dev3->setName('3volt');
        $dev3->setValue($ar_buf[9]);
        $this->mbinfo->setMbVolt($dev3);
        $dev4 = new SensorDevice();
        $dev4->setName('+5Volt');
        $dev4->setValue($ar_buf[10]);
        $this->mbinfo->setMbVolt($dev4);
        $dev5 = new SensorDevice();
        $dev5->setName('+12Volt');
        $dev5->setValue($ar_buf[11]);
        $this->mbinfo->setMbVolt($dev5);
        $dev6 = new SensorDevice();
        $dev6->setName('-12Volt');
        $dev6->setValue($ar_buf[12]);
        $this->mbinfo->setMbVolt($dev6);
        $dev7 = new SensorDevice();
        $dev7->setName('-5Volt');
        $dev7->setValue($ar_buf[13]);
        $this->mbinfo->setMbVolt($dev7);
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
