<?php
/**
 * ipmiutil sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.ipmiutil.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from ipmi-sensors
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class IPMIutil extends Sensors
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
        case 'command':
            CommonFunctions::executeProgram('ipmiutil', 'sensor', $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'file':
            if (CommonFunctions::rfts(APP_ROOT.'/data/ipmiutil.txt', $lines)) {
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
            if (preg_match("/^\S+\sSDR\sFull\s.+\ssnum\s\S\S\s(.+)\s=.+\s\s(.+)\sdegrees\sC/", $line, $buffer)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[1]));
                $dev->setValue($buffer[2]);
                $this->mbinfo->setMbTemp($dev);
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
            if (preg_match("/^\S+\sSDR\sFull\s.+\ssnum\s\S\S\s(.+)\s=.+\s\s(.+)\sVolts/", $line, $buffer)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[1]));
                $dev->setValue($buffer[2]);
                $this->mbinfo->setMbVolt($dev);
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
            if (preg_match("/^\S+\sSDR\sFull\s.+\ssnum\s\S\S\s(.+)\s=.+\s\s(.+)\sRPM/", $line, $buffer)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[1]));
                $dev->setValue($buffer[2]);
                $this->mbinfo->setMbFan($dev);
            }
        }
    }

    /**
     * get power information
     *
     * @return void
     */
    private function _power()
    {
        foreach ($this->_lines as $line) {
            if (preg_match("/^\S+\sSDR\sFull\s.+\ssnum\s\S\S\s(.+)\s=.+\s\s(.+)\sWatts/", $line, $buffer)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[1]));
                $dev->setValue($buffer[2]);
                $this->mbinfo->setMbPower($dev);
            }
        }
    }

    /**
     * get current information
     *
     * @return void
     */
    private function _current()
    {
        foreach ($this->_lines as $line) {
            if (preg_match("/^\S+\sSDR\sFull\s.+\ssnum\s\S\S\s(.+)\s=.+\s\s(.+)\sAmps/", $line, $buffer)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[1]));
                $dev->setValue($buffer[2]);
                $this->mbinfo->setMbCurrent($dev);
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
        $this->_power();
        $this->_current();
    }
}
