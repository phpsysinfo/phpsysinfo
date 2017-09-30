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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
        $lines = "";
        switch (defined('PSI_SENSOR_LMSENSORS_ACCESS')?strtolower(PSI_SENSOR_LMSENSORS_ACCESS):'command') {
        case 'command':
            CommonFunctions::executeProgram("sensors", "", $lines);
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT.'/data/lmsensors.txt', $lines);
            break;
        default:
            $this->error->addConfigError('__construct()', 'PSI_SENSOR_LMSENSORS_ACCESS');
            break;
        }

        if (trim($lines) !== "") {
            $lines = str_replace(":\n", ":", $lines);
            $lines = str_replace("\n\n", "\n", $lines);
            $this->_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        $applesmc = false;
        foreach ($this->_lines as $line) {
            if ((trim($line) !== "") && (strpos($line, ':') === false)) {
                //$applesmc = preg_match("/^applesmc-/", $line);
                $applesmc =  (trim($line) === "applesmc-isa-0300");
            }
            $data = array();
            if (preg_match("/^(.+):(.+).C\s*\((.+)=(.+).C,(.+)=(.+).C\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+).C\s*\((.+)=(.+).C,(.+)=(.+).C\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+).C\s*\((.+)=(.+).C\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+).C\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/^(.+):(.+).C$/", $line, $data);
            }
            if (count($data)>2) {
                foreach ($data as $key=>$value) {
                    if (preg_match("/^\+?(-?[0-9\.]+).?$/", trim($value), $newvalue)) {
                        $data[$key] = 0+trim($newvalue[1]);
                    } else {
                        $data[$key] = trim($value);
                    }
                }
                if ($applesmc && (strlen($data[1]) == 4)) {
                    if ($data[1][0] == "T") {
                        if ($data[1][1] == "A") {
                            $data[1] = $data[1] . " Ambient";
                        } elseif ($data[1][1] == "B") {
                            $data[1] = $data[1] . " Battery";
                        } elseif ($data[1][1] == "C") {
                            $data[1] = $data[1] . " CPU";
                        } elseif ($data[1][1] == "G") {
                            $data[1] = $data[1] . " GPU";
                        } elseif ($data[1][1] == "H") {
                            $data[1] = $data[1] . " Harddisk Bay";
                        } elseif ($data[1][1] == "h") {
                            $data[1] = $data[1] . " Heatpipe";
                        } elseif ($data[1][1] == "L") {
                            $data[1] = $data[1] . " LCD";
                        } elseif ($data[1][1] == "M") {
                            $data[1] = $data[1] . " Memory";
                        } elseif ($data[1][1] == "m") {
                            $data[1] = $data[1] . " Memory Contr.";
                        } elseif ($data[1][1] == "N") {
                            $data[1] = $data[1] . " Northbridge";
                        } elseif ($data[1][1] == "O") {
                            $data[1] = $data[1] . " Optical Drive";
                        } elseif ($data[1][1] == "p") {
                            $data[1] = $data[1] . " Power supply";
                        } elseif ($data[1][1] == "S") {
                            $data[1] = $data[1] . " Slot";
                        } elseif ($data[1][1] == "s") {
                            $data[1] = $data[1] . " Slot";
                        } elseif ($data[1][1] == "W") {
                            $data[1] = $data[1] . " Airport";
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

                $dev = new SensorDevice();
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
            if (preg_match("/^(.+):(.+) RPM\s*\((.+)=(.+) RPM,(.+)=(.+)\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) RPM\s*\((.+)=(.+) RPM,(.+)=(.+)\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) RPM\s*\((.+)=(.+) RPM\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) RPM\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/^(.+):(.+) RPM$/", $line, $data);
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
            if (preg_match("/^(.+):(.+) V\s*\((.+)=(.+) V,(.+)=(.+) V\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) V\s*\((.+)=(.+) V,(.+)=(.+) V\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) V\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/^(.+):(.+) V$/", $line, $data);
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
            if (preg_match("/^(.+):(.+) W\s*\((.+)=(.+) W,(.+)=(.+) W\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) W\s*\((.+)=(.+) W,(.+)=(.+) W\)(.*)/", $line, $data)) {
                ;
            } else
*/
            if (preg_match("/^(.+):(.+) W\s*\((.+)=(.+) W\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) W\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/^(.+):(.+) W$/", $line, $data);
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
            if (preg_match("/^(.+):(.+) A\s*\((.+)=(.+) A,(.+)=(.+) A\)(.*)\)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) A\s*\((.+)=(.+) A,(.+)=(.+) A\)(.*)/", $line, $data)) {
                ;
            } elseif (preg_match("/^(.+):(.+) A\s*\(/", $line, $data)) {
                ;
            } else {
                preg_match("/^(.+):(.+) A$/", $line, $data);
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
