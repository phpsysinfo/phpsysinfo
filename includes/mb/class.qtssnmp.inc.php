<?php
/**
 * qtstemp sensor class, getting hardware temperature information through snmpwalk
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2016 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class QTSsnmp extends Sensors
{
    /**
     * get temperature information
     *
     * @return void
     */
    private function _temperature()
    {
        if (CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." 127.0.0.1 .1.3.6.1.4.1.24681.1.2.5.0", $buffer, PSI_DEBUG)
           && preg_match('/^[\.\d]+ = STRING:\s\"?(\d+)\sC/', $buffer, $data)) {
            $dev = new SensorDevice();
            $dev->setName("CPU");
            $dev->setValue($data[1]);
            $this->mbinfo->setMbTemp($dev);
        }

        if (CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." 127.0.0.1 .1.3.6.1.4.1.24681.1.2.6.0", $buffer, PSI_DEBUG)
           && preg_match('/^[\.\d]+ = STRING:\s\"?(\d+)\sC/', $buffer, $data)) {
            $dev = new SensorDevice();
            $dev->setName("System");
            $dev->setValue($data[1]);
            $this->mbinfo->setMbTemp($dev);
        }

        if (CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." 127.0.0.1 .1.3.6.1.4.1.24681.1.2.11.1.3", $buffer, PSI_DEBUG)) {
            $lines = preg_split('/\r?\n/', $buffer);
            foreach ($lines as $line) if (preg_match('/^[\.\d]+\.(\d+) = STRING:\s\"?(\d+)\sC/', $line, $data)) {
                $dev = new SensorDevice();
                $dev->setName("HDD ".$data[1]);
                $dev->setValue($data[2]);
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
        if (CommonFunctions::executeProgram("snmpwalk", "-Ona -c public -v 1 -t ".PSI_SNMP_TIMEOUT_INT." -r ".PSI_SNMP_RETRY_INT." 127.0.0.1 .1.3.6.1.4.1.24681.1.2.15.1.3", $buffer, PSI_DEBUG)) {
            $lines = preg_split('/\r?\n/', $buffer);
            foreach ($lines as $line) if (preg_match('/^[\.\d]+\.(\d+) = STRING:\s\"?(\d+)\sRPM/', $line, $data)) {
                $dev = new SensorDevice();
                $dev->setName("Fan ".$data[1]);
                $dev->setValue($data[2]);
                $this->mbinfo->setMbFan($dev);
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
        $this->_fans();
    }
}
