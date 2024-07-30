<?php
/**
 * lmsensor sensor class, getting information from lmsensor
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
class LMSensors extends Sensors
{
    /**
     * array of values
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
        $lines = "";
        if ((PSI_OS == 'Linux') && (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT'))) switch (defined('PSI_SENSOR_LMSENSORS_ACCESS')?strtolower(PSI_SENSOR_LMSENSORS_ACCESS):'command') {
        case 'command':
            CommonFunctions::executeProgram("sensors", "", $lines);
            break;
        case 'data':
            if (!defined('PSI_EMU_PORT')) {
                CommonFunctions::rftsdata('lmsensors.tmp', $lines);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_lmsensors] ACCESS');
        }

        if (trim($lines) !== "") {
            $lines = str_replace("\r\n", "\n", $lines);
            $lines = str_replace(":\n", ":", $lines);
            $lines = str_replace("\n\n", "\n", $lines);
            $lines = preg_replace("/\n\s+\(/m", " (", $lines);
            $_lines = preg_split("/\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);

            $tmpvalue=array();
            $applesmc = false;
            $sname = '';
            foreach ($_lines as $line) {
                if ((trim($line) !== "") && (strpos($line, ':') === false)) {
                    if (sizeof($tmpvalue)>0) {
                        $this->_values[] = $tmpvalue;
                        $tmpvalue = array();
                    }
                    $sname = trim($line);
                    $applesmc =  ($sname === "applesmc-isa-0300");
                    if (preg_match('/^([^-]+)-/', $sname, $snamebuf)) {
                        $sname = $snamebuf[1];
                    } else {
                        $sname = '';
                    }
                } else {
                    if (preg_match("/^(.+):(.+)$/", trim($line), $data) && ($data[1]!=="Adapter")) {
                        if ($applesmc && (strlen($data[1]) == 4) && ($data[1][0] == "T")) {
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
                    

                        $arrtemp=array();
                        if ($sname !== "" ) {
                            $arrtemp["name"] = $data[1]." (".$sname.")";
                        } else {
                            $arrtemp["name"] = $data[1];
                        }
                        if (preg_match("/^([^\(]+)\s+\(/", $data[2], $tmp) || preg_match("/^(.+)\s+ALARM$/", $data[2], $tmp)) {
                            if (($tmp[1] = trim($tmp[1])) == "") {
                                $arrtemp["value"] = "ALARM";
                            } else {
                                $arrtemp["value"] = $tmp[1];
                            }
                            if (preg_match("/\s(ALARM)\s*$/", $data[2]) || preg_match("/\s(ALARM)\s+\(/", $data[2]) || preg_match("/\s(ALARM)\s+sensor\s+=/", $data[2])) {
                                $arrtemp["alarm"]="ALARM";
                            }

                            if (preg_match_all("/\(([^\)]+\s+=\s+[^\)]+)\)/", $data[2], $tmp2)) foreach ($tmp2[1] as $tmp3) {
                                    $arrtmp3 = preg_split('/,/', $tmp3);
                                foreach ($arrtmp3 as $tmp4) if (preg_match("/^(\S+)\s+=\s+(.*)$/", trim($tmp4), $tmp5)) {
                                    $arrtemp[$tmp5[1]]=trim($tmp5[2]);
                                }
                            }
                        } else {
                            $arrtemp["value"] = trim($data[2]);
                        }
                        $tmpvalue[] = $arrtemp;
                    }
                }
            }
            if (sizeof($tmpvalue)>0) $this->_values[] = $tmpvalue;
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                $limit = "";
                if (preg_match("/^\+?(-?[0-9\.]+)[^\w\r\n\t]+C$/", $sensor["value"], $tmpbuf) || 
                   ((isset($sensor[$limit="crit"]) || isset($sensor[$limit="high"]) || isset($sensor[$limit="hyst"])) && preg_match("/^\+?(-?[0-9\.]+)[^\w\r\n\t]+C$/", $sensor[$limit]))) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    if ($limit != "") {
                        $dev->setValue($sensor["value"]);
                        $dev->setEvent("FAULT");
                    } else {
                        if ($tmpbuf[1] == -110.8) {
                            $dev->setValue("FAULT");
                            $dev->setEvent("FAULT");
                        } else {
                            $dev->setValue(floatval($tmpbuf[1]));
                            if (isset($sensor["alarm"])) $dev->setEvent("ALARM");
                        }
                    }

                    if (isset($sensor[$limit="crit"]) && preg_match("/^\+?(-?[0-9\.]+)[^\w\r\n\t]+C$/", $sensor[$limit], $tmpbuf) && (($tmpbuf[1]=floatval($tmpbuf[1])) > 0)) {
                        $dev->setMax(floatval($tmpbuf[1]));
                    } elseif (isset($sensor[$limit="high"]) && preg_match("/^\+?(-?[0-9\.]+)[^\w\r\n\t]+C$/", $sensor[$limit], $tmpbuf) && (($tmpbuf[1]=floatval($tmpbuf[1])) > 0) && ($tmpbuf[1]<65261.8)) {
                        $dev->setMax(floatval($tmpbuf[1]));
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
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                $limit = "";
                if (preg_match("/^([0-9]+) RPM$/", $sensor["value"], $tmpbuf) ||
                   ((isset($sensor[$limit="min"]) || isset($sensor[$limit="max"])) && preg_match("/^([0-9]+) RPM$/", $sensor[$limit]))) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    if ($limit != "") {
                        $dev->setValue($sensor["value"]);
                        $dev->setEvent("FAULT");
                    } else {
                        $dev->setValue($tmpbuf[1]);
                    }
                    if (isset($sensor["alarm"])) $dev->setEvent("ALARM");

                    if (isset($sensor[$limit="min"]) && preg_match("/^([0-9]+) RPM$/", $sensor[$limit], $tmpbuf) && ($tmpbuf[1] > 0)) {
                        $dev->setMin($tmpbuf[1]);
                    }

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
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                $limit = "";
                if (preg_match("/^\+?(-?[0-9\.]+) (m?)V$/", $sensor["value"], $tmpbuf) ||
                   ((isset($sensor[$limit="min"]) || isset($sensor[$limit="max"])) && preg_match("/^\+?(-?[0-9\.]+) (m?)V$/", $sensor[$limit]))) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    if ($limit != "") {
                        $dev->setValue($sensor["value"]);
                        $dev->setEvent("FAULT");
                    } else {
                        if ($tmpbuf[2] == "m") { 
                            $dev->setValue(floatval($tmpbuf[1])/1000);
                        } else {
                            $dev->setValue(floatval($tmpbuf[1]));
                        }
                    }
                    if (isset($sensor["alarm"])) $dev->setEvent("ALARM");

                    if (isset($sensor[$limit="min"]) && preg_match("/^\+?(-?[0-9\.]+) (m?)V$/", $sensor[$limit], $tmpbuf)) {
                        $dev->setMin(floatval($tmpbuf[1]));
                    }

                    if (isset($sensor[$limit="max"]) && preg_match("/^\+?(-?[0-9\.]+) (m?)V$/", $sensor[$limit], $tmpbuf)) {
                       $dev->setMax(floatval($tmpbuf[1]));
                    }

                    $this->mbinfo->setMbVolt($dev);
                }
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
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                $limit = "";
                if (preg_match("/^\+?(-?[0-9\.]+) W$/", $sensor["value"], $tmpbuf) ||
                   (isset($sensor[$limit="crit"]) && preg_match("/^\+?(-?[0-9\.]+) W$/", $sensor[$limit]))) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    if ($limit != "") {
                        $dev->setValue($sensor["value"]);
                        $dev->setEvent("FAULT");
                    } else {
                        $dev->setValue(floatval($tmpbuf[1]));
                    }
                    if (isset($sensor["alarm"])) $dev->setEvent("ALARM");

                    if (isset($sensor[$limit="crit"]) && preg_match("/^\+?(-?[0-9\.]+) W$/", $sensor[$limit], $tmpbuf)) {
                       $dev->setMax(floatval($tmpbuf[1]));
                    }

                    $this->mbinfo->setMbPower($dev);
                }
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
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                $limit = "";
                if (preg_match("/^\+?(-?[0-9\.]+) A$/", $sensor["value"], $tmpbuf) ||
                   (isset($sensor[$limit="crit"]) && preg_match("/^\+?(-?[0-9\.]+) A$/", $sensor[$limit]))) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    if ($limit != "") {
                        $dev->setValue($sensor["value"]);
                        $dev->setEvent("FAULT");
                    } else {
                        $dev->setValue(floatval($tmpbuf[1]));
                    }
                    if (isset($sensor["alarm"])) $dev->setEvent("ALARM");

                    if (isset($sensor[$limit="min"]) && preg_match("/^\+?(-?[0-9\.]+) A$/", $sensor[$limit], $tmpbuf)) {
                       $dev->setMin(floatval($tmpbuf[1]));
                    }

                    if (isset($sensor[$limit="max"]) && preg_match("/^\+?(-?[0-9\.]+) A$/", $sensor[$limit], $tmpbuf)) {
                       $dev->setMax(floatval($tmpbuf[1]));
                    }

                    $this->mbinfo->setMbCurrent($dev);
                }
            }
        }
    }

    /**
     * get other information
     *
     * @return void
     */
    private function _other()
    {
        foreach ($this->_values as $sensors) foreach ($sensors as $sensor){
            if (isset($sensor["value"])) {
                if ((preg_match("/^([^\-\+\d\s].+)$/", $sensor["value"], $tmpbuf) || preg_match("/^(\d+)$/", $sensor["value"], $tmpbuf)) &&
                    !isset($sensor[$limit="min"]) && !isset($sensor[$limit="max"]) && !isset($sensor[$limit="crit"]) && !isset($sensor[$limit="high"]) && !isset($sensor[$limit="hyst"])) {
                    $dev = new SensorDevice();
                    $dev->setName($sensor["name"]);
                    $dev->setValue($sensor["value"]);
                    if (isset($sensor["alarm"])) $dev->setEvent("ALARM");

                    $this->mbinfo->setMbOther($dev);
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
        $this->_fans();
        $this->_voltage();
        $this->_power();
        $this->_current();
        $this->_other();
    }
}
