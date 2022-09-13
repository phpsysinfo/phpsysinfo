<?php
/**
 * nvidiasmi sensor class, getting hardware temperature information and fan speed from nvidia-smi utility
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2020 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class NvidiaSMI extends Sensors
{
    /**
     * content to parse
     *
     * @var array
     */
    private $_gpus = array();

    /**
     * fill the private array
     */
    public function __construct()
    {
        parent::__construct();
        if (!defined('PSI_EMU_HOSTNAME') || defined('PSI_EMU_PORT')) switch (defined('PSI_SENSOR_NVIDIASMI_ACCESS')?strtolower(PSI_SENSOR_NVIDIASMI_ACCESS):'command') {
        case 'command':
            if (PSI_OS == 'WINNT') {
                $winnt_exe = (defined('PSI_SENSOR_NVIDIASMI_EXE_PATH') && is_string(PSI_SENSOR_NVIDIASMI_EXE_PATH))?strtolower(PSI_SENSOR_NVIDIASMI_EXE_PATH):"c:\\Program Files\\NVIDIA Corporation\\NVSMI\\nvidia-smi.exe";
                if (($_exe=realpath(trim($winnt_exe))) && preg_match("/^([a-zA-Z]:\\\\[^\\\\]+)/", $_exe, $out)) {
                    CommonFunctions::executeProgram('cmd', "/c set ProgramFiles=".$out[1]."^&\"".$_exe."\" -q", $lines);
                } else {
                    $this->error->addConfigError('__construct()', '[sensor_nvidiasmi] EXE_PATH="'.$winnt_exe.'"');
                }
            } else {
                CommonFunctions::executeProgram('nvidia-smi', '-q', $lines);
            }

            $this->_gpus = preg_split("/^(?=GPU )/m", $lines, -1, PREG_SPLIT_NO_EMPTY);
            break;
        case 'data':
            if (!defined('PSI_EMU_PORT') && CommonFunctions::rftsdata('nvidiasmi.tmp', $lines)) {
                $this->_gpus = preg_split("/^(?=GPU )/m", $lines, -1, PREG_SPLIT_NO_EMPTY);
            }
            break;
        default:
            $this->error->addConfigError('__construct()', '[sensor_nvidiasmi] ACCESS');
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
        $gpuc=count($this->_gpus);
        switch ($gpuc) {
        case 0:
            $this->error->addError("nvidia-smi", "No values");
            break;
        case 1:
            $this->error->addError("nvidia-smi", "Error: ".$this->_gpus[0]);
            break;
        default:
            for ($c = 0; $c < $gpuc; $c++) {
                if (preg_match("/^\s+GPU Current Temp\s+:\s*(\d+)\s*C\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." (nvidiasmi)");
                    $dev->setValue($out[1]);
                    if (preg_match("/^\s+GPU Shutdown Temp\s+:\s*(\d+)\s*C\s*$/m", $this->_gpus[$c], $out)) {
                        $dev->setMax($out[1]);
                    }
                    $this->mbinfo->setMbTemp($dev);
                }
                if (preg_match("/^\s+Power Draw\s+:\s*([\d\.]+)\s*W\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." (nvidiasmi)");
                    $dev->setValue($out[1]);
                    if (preg_match("/^\s+Power Limit\s+:\s*([\d\.]+)\s*W\s*$/m", $this->_gpus[$c], $out)) {
                        $dev->setMax($out[1]);
                    }
                    $this->mbinfo->setMbPower($dev);
                }
                if (preg_match("/^\s+Fan Speed\s+:\s*(\d+)\s*%\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $dev->setUnit("%");
                    $this->mbinfo->setMbFan($dev);
                }
                if (preg_match("/^\s+Performance State\s+:\s*(\S+)\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." Performance State (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $this->mbinfo->setMbOther($dev);
                }
                if (preg_match("/^\s+Gpu\s+:\s*(\d+)\s*%\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." Utilization (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $dev->setUnit("%");
                    $this->mbinfo->setMbOther($dev);
                }
                if (preg_match("/^\s+Memory\s+:\s*(\d+)\s*%\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." Memory Utilization (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $dev->setUnit("%");
                    $this->mbinfo->setMbOther($dev);
                }
                if (preg_match("/^\s+Encoder\s+:\s*(\d+)\s*%\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." Encoder Utilization (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $dev->setUnit("%");
                    $this->mbinfo->setMbOther($dev);
                }
                if (preg_match("/^\s+Decoder\s+:\s*(\d+)\s*%\s*$/m", $this->_gpus[$c], $out)) {
                    $dev = new SensorDevice();
                    $dev->setName("GPU ".($c)." Decoder Utilization (nvidiasmi)");
                    $dev->setValue($out[1]);
                    $dev->setUnit("%");
                    $this->mbinfo->setMbOther($dev);
                }
            }
        }
    }
}
