<?php
/**
 * fortisensor sensor class, getting hardware sensors information from Fortinet devices
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2022 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class FortiSensor extends Sensors
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
        $lines = "";
        if (defined('PSI_EMU_PORT') && CommonFunctions::executeProgram('execute', 'sensor list', $resulte, false) && ($resulte !== "")
           && preg_match('/^(.*[\$#]\s*)/', $resulte, $resulto, PREG_OFFSET_CAPTURE)) {
            $resulti = substr($resulte, strlen($resulto[1][0]));
            if (preg_match('/(\n.*[\$#])$/', $resulti, $resulto, PREG_OFFSET_CAPTURE)) {
                $lines = substr($resulti, 0, $resulto[1][1]);
            }
        }
        $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        foreach ($this->_lines as $line) {
            if (preg_match('/^\s*\d+\s(.+)\sTemperature\s+([\d\.]+)\s\S*C\s*$/', $line, $data)) {
                $dev = new SensorDevice();
                $dev->setName($data[1]);
                $dev->setValue($data[2]);
                $this->mbinfo->setMbTemp($dev);
            } elseif (preg_match('/^\s*\d+\s(.+)\s+alarm=(\d)\s+value=(\d+)\s/', $line, $data)
               && !preg_match('/fan| vin/i', $data[1])) {
                $dev = new SensorDevice();
                $dev->setName(trim($data[1]));
                $dev->setValue($data[3]);
                if ($data[2] != 0) {
                    $dev->setEvent("Alarm");
                }
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
            if (preg_match('/^\s*\d+\s(.+)\s+alarm=(\d)\s+value=([\d\.]+)\s/', $line, $data)
               && preg_match('/\./', $data[3])
               && !preg_match('/fan|temp/i', $data[1])) {
                $dev = new SensorDevice();
                $dev->setName(trim($data[1]));
                $dev->setValue($data[3]);
                if ($data[2] != 0) {
                    $dev->setEvent("Alarm");
                }
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
            if (preg_match('/^\s*\d+\s(.+)\s+alarm=(\d)\s+value=(\d+)\s/', $line, $data)
               && preg_match('/fan/i', $data[1])) {
                $dev = new SensorDevice();
                $dev->setName(trim($data[1]));
                $dev->setValue($data[3]);
                if ($data[2] != 0) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbFan($dev);
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
