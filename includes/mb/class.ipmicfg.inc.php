<?php
/**
 * ipmicfg sensor class, getting information from ipmicfg -sdr
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2021 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class IPMIcfg extends Sensors
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
        if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) switch (defined('PSI_SENSOR_IPMICFG_ACCESS')?strtolower(PSI_SENSOR_IPMICFG_ACCESS):'command') {
        case 'command':
            if ((!defined('PSI_SENSOR_IPMICFG_SDR') || (PSI_SENSOR_IPMICFG_SDR!==false)) || 
                (!defined('PSI_SENSOR_IPMICFG_PSFRUINFO') || (PSI_SENSOR_IPMICFG_PSFRUINFO!==false)) ||
                (!defined('PSI_SENSOR_IPMICFG_PMINFO') || (PSI_SENSOR_IPMICFG_PMINFO!==false))) {
                $lines='';
                $first=true;
                if (!defined('PSI_SENSOR_IPMICFG_SDR') || (PSI_SENSOR_IPMICFG_SDR!==false)) {
                    $linestmp='';
                    if (CommonFunctions::executeProgram('ipmicfg', '-sdr', $linestmp)) {
                        $lines=$linestmp;
                    }
                    $first=false;
                }
                if (!defined('PSI_SENSOR_IPMICFG_PSFRUINFO') || (PSI_SENSOR_IPMICFG_PSFRUINFO!==false)) {
                    $linestmp='';
                    if (CommonFunctions::executeProgram('ipmicfg', '-psfruinfo', $linestmp, $first || PSI_DEBUG)) {
                        $lines.=$linestmp;
                    }
                    $first=false;
                }
                if (!defined('PSI_SENSOR_IPMICFG_PMINFO') || (PSI_SENSOR_IPMICFG_PMINFO!==false)) {
                    $linestmp='';
                    if (CommonFunctions::executeProgram('ipmicfg', '-pminfo', $linestmp, $first || PSI_DEBUG)) {
                        $lines.=$linestmp;
                    }
                }
                $this->_lines = preg_split("/\r?\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $this->error->addConfigError('__construct()', '[sensor_ipmicfg] Not defined: SDR or PSFRUINFO or PMINFO');
            }
            break;
        case 'data':
            if (!defined('PSI_EMU_PORT') && CommonFunctions::rftsdata('ipmicfg.tmp', $lines)) {
                $this->_lines = preg_split("/\r?\n/", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_ipmicfg] ACCESS');
        }
        
        if ($this->_lines===false) {
            $this->_lines = array();
        } else {
            $pmbus=false;
            for ($licz=count($this->_lines); $licz--; $licz>0) {
                $line=$this->_lines[$licz];
                if (preg_match("/^\s*PMBus Revision\s*\|/", $line)) {
                    $pmbus=true;
                } else if (preg_match("/^(\s*\[SlaveAddress = [\da..fA..F]+h\] \[)(Module )(\d+\])/", $line, $tmpbuf)) {
                    $this->_lines[$licz]=$tmpbuf[1].($pmbus?"PMBus ":"SMBus ").$tmpbuf[3];
                    $pmbus=false;
                } else {
                    $this->_lines[$licz]=preg_replace("/\|\s*$/", "", $line);
                }
            }
        }
    }

    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)==5) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff) && preg_match("/^\s*([-\d]+)C\/[-\d]+F\s*$/", $buffer[2], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName($namebuff[1]);
                if ($valbuff[1]<-128) $valbuff[1]+=256; //+256 correction
                $dev->setValue($valbuff[1]);
                if (preg_match("/^\s*([-\d]+)C\/[-\d]+F\s*$/", $buffer[3], $valbuffmin)) {
                    if ($valbuffmin[1]<-128) $valbuffmin[1]+=256; //+256 correction
                }
                if (preg_match("/^\s*([-\d]+)C\/[-\d]+F\s*$/", $buffer[4], $valbuffmax)) {
                    if ($valbuffmax[1]<-128) $valbuffmax[1]+=256; //+256 correction
                    $dev->setMax($valbuffmax[1]);
                }
                if ((isset($valbuffmin[1]) && ($valbuff[1]<=$valbuffmin[1])) || (isset($valbuffmax[1]) && ($valbuff[1]>=$valbuffmax[1]))) { //own range test due to errors with +256 correction
                    $dev->setEvent("Alarm");
                }
                $this->mbinfo->setMbTemp($dev);
            } elseif (($mdid!='') && (count($buffer)==2) && preg_match("/^\s*([-\d]+)C\/[-\d]+F\s*$/", $buffer[1], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($valbuff[1]);
                $this->mbinfo->setMBTemp($dev);
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
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)==5) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff) && preg_match("/^\s*([\d\.]+)\sV\s*$/", $buffer[2], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName($namebuff[1]);
                $dev->setValue($valbuff[1]);
                if (preg_match("/^\s*([\d\.].+)\sV\s*$/", $buffer[3], $valbuffmin)) {
                    $dev->setMin($valbuffmin[1]);
                }
                if (preg_match("/^\s*([\d\.].+)\sV\s*$/", $buffer[4], $valbuffmax)) {
                    $dev->setMax($valbuffmax[1]);
                }
                if (trim($buffer[0]) != "OK") $dev->setEvent(trim($buffer[0]));
                $this->mbinfo->setMbVolt($dev);
            } elseif (($mdid!='') && (count($buffer)==2) && preg_match("/^\s*([\d\.]+)\sV\s*$/", $buffer[1], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($valbuff[1]);
                $this->mbinfo->setMBVolt($dev);
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
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)==5) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff) && preg_match("/^\s*(\d+)\sRPM\s*$/", $buffer[2], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName($namebuff[1]);
                $dev->setValue($valbuff[1]);
                if (preg_match("/^\s*(\d+)\sRPM\s*$/", $buffer[3], $valbuffmin)) {
                    $dev->setMin($valbuffmin[1]);
                }
                if ((trim($buffer[0]) != "OK") && isset($valbuffmin[1])) {
                    $dev->setEvent(trim($buffer[0]));
                }
                $this->mbinfo->setMbFan($dev);
            } elseif (($mdid!='') && (count($buffer)==2) && preg_match("/^\s*(\d+)\sRPM\s*$/", $buffer[1], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($valbuff[1]);
                $this->mbinfo->setMBFan($dev);
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
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)==5) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff) && preg_match("/^\s*(\d+)\sWatts\s*$/", $buffer[2], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName($namebuff[1]);
                $dev->setValue($valbuff[1]);
                if (preg_match("/^\s*(\d+)\sWatts\s*$/", $buffer[4], $valbuffmax)) {
                    $dev->setMax($valbuffmax[1]);
                }
                if (trim($buffer[0]) != "OK") $dev->setEvent(trim($buffer[0]));
                $this->mbinfo->setMbPower($dev);
            } elseif (($mdid!='') && (count($buffer)==2) && preg_match("/^\s*(\d+)\sW\s*$/", $buffer[1], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($valbuff[1]);
                $this->mbinfo->setMBPower($dev);
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
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)==5) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff) && preg_match("/^\s*([\d\.]+)\sAmps\s*$/", $buffer[2], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName($namebuff[1]);
                $dev->setValue($valbuff[1]);
                if (preg_match("/^\s*([\d\.].+)\sAmps\s*$/", $buffer[3], $valbuffmin)) {
                    $dev->setMin($valbuffmin[1]);
                }
                if (preg_match("/^\s*([\d\.].+)\sAmps\s*$/", $buffer[4], $valbuffmax)) {
                    $dev->setMax($valbuffmax[1]);
                }
                if (trim($buffer[0]) != "OK") $dev->setEvent(trim($buffer[0]));
                $this->mbinfo->setMbCurrent($dev);
            } elseif (($mdid!='') && (count($buffer)==2) && preg_match("/^\s*([\d\.]+)\sA\s*$/", $buffer[1], $valbuff)) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($valbuff[1]);
                $this->mbinfo->setMBCurrent($dev);
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
        $mdid='';
        foreach ($this->_lines as $line) {
            if (preg_match("/^\s*\[SlaveAddress = [\da..fA..F]+h\] \[((PMBus \d+)|(SMBus \d+))\]/", $line, $mdidtmp)) {
                $mdid=$mdidtmp[1];
                continue;
            }
            $buffer = preg_split("/\s*\|\s*/", $line);
            if (($mdid=='') && (count($buffer)>=3) && preg_match("/^\s*\(\d+\)\s(.*)\s*$/", $buffer[1], $namebuff)) {
                $buffer[2]=trim($buffer[2]);
                if ((count($buffer)==3) &&
                   ($buffer[2]!=="Correctable ECC / other correctable memory error") &&
                   ($buffer[2]!=="Not Present") &&
                   ($buffer[2]!=="N/A") &&
                   (trim($buffer[0]) != "")) {
                    $dev = new SensorDevice();
                    $dev->setName($namebuff[1]);
                    $dev->setValue($buffer[2]);
                    if (trim($buffer[0]) != "OK") $dev->setEvent(trim($buffer[0]));
                    $this->mbinfo->setMbOther($dev);
                } elseif ((count($buffer)==5)&& preg_match("/(^\s*[\d\.]+\s*$)|(^\s*[\da-fA-F]{2}\s+[\da-fA-F]{2}\s+[\da-fA-F]{2}\s+[\da-fA-F]{2}\s*$)/", $buffer[2], $valbuff)) {
                    $dev = new SensorDevice();
                    $dev->setName($namebuff[1]);
                    $dev->setValue($buffer[0]);
                    $this->mbinfo->setMbOther($dev);
                }
            } elseif (($mdid!='') && (count($buffer)==2) && ((trim($buffer[0])=="Status") || (trim($buffer[0])=="Current Sharing Control"))) {
                $dev = new SensorDevice();
                $dev->setName(trim($buffer[0])." (".$mdid.")");
                $dev->setValue($buffer[1]);
                $this->mbinfo->setMbOther($dev);
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
        $this->_power();
        $this->_current();
        $this->_other();
    }
}
