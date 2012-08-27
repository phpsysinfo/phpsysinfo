<?php 
/**
 * hwsensors sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.HWSensors.inc.php 287 2009-06-26 12:11:59Z bigmichi1 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from hwsensors
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class HWSensors extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_lines = array();
    
    /**
     * fill the private content var through tcp or file access
     */
    function __construct()
    {
        parent::__construct();
        switch (strtolower(PSI_SENSOR_ACCESS)) {
        case 'tcp':
            $lines = "";
            CommonFunctions::executeProgram('sysctl', '-w hw.sensors', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_ACCESS');
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
            $ar_buf = preg_split("/[\s,]+/", $line);
            if (isset($ar_buf[3]) && $ar_buf[2] == 'temp') {
                $dev = new SensorDevice();
                $dev->setName($ar_buf[1]);
                $dev->setValue($ar_buf[3]);
                $dev->setMax(70);
                $this->mbinfo->setMbTemp($dev);
            }
        }
    }
    
    /**
     * get fan information
     *
     * @return void
     */
    private function _fans()
    {
        foreach ($this->_lines as $line) {
            $ar_buf = preg_split("/[\s,]+/", $line);
            if (isset($ar_buf[3]) && $ar_buf[2] == 'fanrpm') {
                $dev = new SensorDevice();
                $dev->setName($ar_buf[1]);
                $dev->setValue($ar_buf[3]);
                $this->mbinfo->setMbFan($dev);
            }
        }
    }
    
    /**
     * get voltage information
     *
     * @return void
     */
    private function _voltage()
    {
        foreach ($this->_lines as $line) {
            $ar_buf = preg_split("/[\s,]+/", $line);
            if (isset($ar_buf[3]) && $ar_buf[2] == 'volts_dc') {
                $dev = new SensorDevice();
                $dev->setName($ar_buf[1]);
                $dev->setValue($ar_buf[3]);
                $this->mbinfo->setMbVolt($dev);
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
        $this->_voltage();
        $this->_fans();
    }
}
?>
