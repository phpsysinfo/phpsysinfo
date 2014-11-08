<?php
/**
 * mbmon sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.mbmon.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from mbmon
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class MBMon extends Sensors
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
    public function __construct()
    {
        parent::__construct();
        switch (strtolower(PSI_SENSOR_ACCESS)) {
        case 'tcp':
            $fp = fsockopen("localhost", 411, $errno, $errstr, 5);
            if ($fp) {
                $lines = "";
                while (!feof($fp)) {
                    $lines .= fread($fp, 1024);
                }
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $this->error->addError("fsockopen()", $errno." ".$errstr);
            }
            break;
        case 'command':
            CommonFunctions::executeProgram('mbmon', '-c 1 -r', $lines, PSI_DEBUG);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'file':
            if (CommonFunctions::rfts(APP_ROOT.'/data/mbmon.txt', $lines)) {
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
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
            if (preg_match('/^(TEMP\d*)\s*:\s*(.*)$/D', $line, $data)) {
                if ($data[2] <> '0') {
                    $dev = new SensorDevice();
                    $dev->setName($data[1]);
                    $dev->setMax(70);
                    if ($data[2] < 250) {
                        $dev->setValue($data[2]);
                    }
                    $this->mbinfo->setMbTemp($dev);
                }
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
            if (preg_match('/^(FAN\d*)\s*:\s*(.*)$/D', $line, $data)) {
                if ($data[2] <> '0') {
                    $dev = new SensorDevice();
                    $dev->setName($data[1]);
                    $dev->setValue($data[2]);
                    $dev->setMax(3000);
                    $this->mbinfo->setMbFan($dev);
                }
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
            if (preg_match('/^(V.*)\s*:\s*(.*)$/D', $line, $data)) {
                if ($data[2] <> '+0.00') {
                    $dev = new SensorDevice();
                    $dev->setName($data[1]);
                    $dev->setValue($data[2]);
                    $this->mbinfo->setMbVolt($dev);
                }
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_Sensor::build()
     *
     * @return void
     */
    public function build()
    {
        $this->_temperature();
        $this->_voltage();
        $this->_fans();
    }
}
