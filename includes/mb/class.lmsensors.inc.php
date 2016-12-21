<?php
/**
 * lmsensor sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.lmsensors.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * getting information from lmsensor
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class LMSensors extends Sensors
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
        switch (defined('PSI_SENSOR_LMSENSORS_ACCESS')?strtolower(PSI_SENSOR_LMSENSORS_ACCESS):'command') {
        case 'command':
            if (CommonFunctions::executeProgram("sensors", "", $lines)) {
                // Martijn Stolk: Dirty fix for misinterpreted output of sensors,
                // where info could come on next line when the label is too long.
                $lines = str_replace(":\n", ":", $lines);
                $lines = str_replace("\n\n", "\n", $lines);
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        case 'data':
            if (CommonFunctions::rfts(APP_ROOT.'/data/lmsensors.txt', $lines)) {
                $lines = str_replace(":\n", ":", $lines);
                $lines = str_replace("\n\n", "\n", $lines);
                $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_LMSENSORS_ACCESS');
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
            $data = array();
            if (preg_match("/(.*):(.*).C\s*\((.*)=(.*).C,(.*)=(.*).C\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*).C\s*\((.*)=(.*).C,(.*)=(.*).C\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*).C\s*\((.*)=(.*).C\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*).C\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/(.*):(.*).C$/", $line, $data);
            }
            if (count($data)>2) {
                foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?(-?[0-9\.]+).?$/", trim($value), $newvalue)) {
                        $data[$key] = 0+trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                $dev = new SensorDevice();
                if (strlen($data[1]) == 4) {
                    if ($data[1][0] == "T") {
                        if ($data[1][1] == "A") {
                            $data[1] = $data[1] . " Ambient";
                        } elseif ($data[1][1] == "C") {
                            $data[1] = $data[1] . " CPU";
                        } elseif ($data[1][1] == "G") {
                            $data[1] = $data[1] . " GPU";
                        } elseif ($data[1][1] == "H") {
                            $data[1] = $data[1] . " Harddisk";
                        } elseif ($data[1][1] == "L") {
                            $data[1] = $data[1] . " LCD";
                        } elseif ($data[1][1] == "O") {
                            $data[1] = $data[1] . " ODD";
                        } elseif ($data[1][1] == "B") {
                            $data[1] = $data[1] . " Battery";
                        }

                        if ($data[1][3] == "H") {
                            $data[1] = $data[1] . " Heatsink";
                        } elseif ($data[1][3] == "P") {
                            $data[1] = $data[1] . " Proximity";
                        } elseif ($data[1][3] == "D") {
                            $data[1] = $data[1] . " Die";
                        }
                    }
                }

                $dev->setName($data[1]);
                $dev->setValue($data[2]);
                if (isset($data[6]) && $data[2] <= $data[6]) {
                    $dev->setMax(max($data[4], $data[6]));
                } elseif (isset($data[4]) && $data[2] <= $data[4]) {
                    $dev->setMax($data[4]);
                }
                if (preg_match("/\sALARM\s*$/", $line)) {
                    $dev->setEvent("Alarm");
                }
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
            $data = array();
            if (preg_match("/(.*):(.*) RPM\s*\((.*)=(.*) RPM,(.*)=(.*)\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) RPM\s*\((.*)=(.*) RPM,(.*)=(.*)\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) RPM\s*\((.*)=(.*) RPM\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) RPM\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/(.*):(.*) RPM$/", $line, $data);
            }
            if (count($data)>2) {
                 foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?(-?[0-9\.]+).?$/", trim($value), $newvalue)) {
                        $data[$key] = 0+trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                $dev = new SensorDevice();
                $dev->setName(trim($data[1]));
                $dev->setValue(trim($data[2]));
                if (isset($data[4])) {
                    $dev->setMin(trim($data[4]));
                }
                if (preg_match("/\sALARM\s*$/", $line)) {
                    $dev->setEvent("Alarm");
                }
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
            $data = array();
            if (preg_match("/(.*):(.*) V\s*\((.*)=(.*) V,(.*)=(.*) V\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) V\s*\((.*)=(.*) V,(.*)=(.*) V\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) V\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/(.*):(.*) V$/", $line, $data);
            }

            if (count($data)>2) {
                foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?(-?[0-9\.]+)$/", trim($value), $newvalue)) {
                        $data[$key] = 0+trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                $dev = new SensorDevice();
                $dev->setName($data[1]);
                $dev->setValue($data[2]);
                if (isset($data[4])) {
                    $dev->setMin($data[4]);
                }
                if (isset($data[6])) {
                    $dev->setMax($data[6]);
                }
                if (preg_match("/\sALARM\s*$/", $line)) {
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbVolt($dev);
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
            $data = array();
/* not tested yet
            if (preg_match("/(.*):(.*) W\s*\((.*)=(.*) W,(.*)=(.*) W\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) W\s*\((.*)=(.*) W,(.*)=(.*) W\)(.*)/", $line, $data)) {
                ;
            } else
*/
            if (preg_match("/(.*):(.*) W\s*\((.*)=(.*) W\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) W\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/(.*):(.*) W$/", $line, $data);
            }
            if (count($data)>2) {
                foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?(-?[0-9\.]+).?$/", trim($value), $newvalue)) {
                        $data[$key] = 0+trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                $dev = new SensorDevice();
                $dev->setName($data[1]);
                $dev->setValue($data[2]);

/* not tested yet
                if (isset($data[6]) && $data[2] <= $data[6]) {
                    $dev->setMax(max($data[4], $data[6]));
                } else
*/
                if (isset($data[4]) && $data[2] <= $data[4]) {
                    $dev->setMax($data[4]);
                }
                if (preg_match("/\sALARM\s*$/", $line)) {
                    $dev->setEvent("Alarm");
                }
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
            $data = array();
            if (preg_match("/(.*):(.*) A\s*\((.*)=(.*) A,(.*)=(.*) A\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) A\s*\((.*)=(.*) A,(.*)=(.*) A\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/(.*):(.*) A\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/(.*):(.*) A$/", $line, $data);
            }
            if (count($data)>2) {
                foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?([0-9\.]+).?$/", trim($value), $newvalue)) {
                        $data[$key] = trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                $dev = new SensorDevice();
                $dev->setName($data[1]);
                $dev->setValue($data[2]);
                if (isset($data[4])) {
                    $dev->setMin($data[4]);
                }
                if (isset($data[6])) {
                    $dev->setMax($data[6]);
                }
                if (preg_match("/\sALARM\s*$/", $line)) {
                    $dev->setEvent("Alarm");
                }
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
