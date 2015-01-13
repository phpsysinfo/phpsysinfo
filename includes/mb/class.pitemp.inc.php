<?php
/**
 * pitemp sensor class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Sensor
 * @author    Marc Hillesheim <hawkeyexp@gmail.com>
 * @copyright 2012 Marc Hillesheim
 * @link      http://pi.no-ip.biz
 */
class PiTemp extends Sensors
{
    private function _temperature()
    {
        $temp = null;
        $temp_max = null;
        if (!CommonFunctions::rfts('/sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/temp1_input',$temp, 0, 4096, false)) { // Not Banana Pi
            CommonFunctions::rfts('/sys/class/thermal/thermal_zone0/temp', $temp);
            CommonFunctions::rfts('/sys/class/thermal/thermal_zone0/trip_point_0_temp', $temp_max, 0, 4096, PSI_DEBUG);
        }
        if (!is_null($temp) && (trim($temp) != "")) {
            $dev = new SensorDevice();
            $dev->setName("CPU 1");
            $dev->setValue($temp / 1000);
            if (!is_null($temp_max) && (trim($temp_max) != "") && ($temp_max > 0)) {
                $dev->setMax($temp_max / 1000);
            }
            $this->mbinfo->setMbTemp($dev);
        }
    }

    private function _voltage()
    {
        $volt = null;
        if (CommonFunctions::rfts('/sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/axp20-supplyer.28/power_supply/ac/voltage_now',$volt, 0, 4096, false) && !is_null($volt) && (trim($volt) != "")) { // Banana Pi
            $dev = new SensorDevice();
            $dev->setName("Voltage 1");
            $dev->setValue($volt / 1000000);
            $this->mbinfo->setMbVolt($dev);
        }
    }

    private function _current()
    {
        $current = null;
        if (CommonFunctions::rfts('/sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/axp20-supplyer.28/power_supply/ac/current_now',$current, 0, 4096, false) && !is_null($current) && (trim($current) != "")) { // Banana Pi
            $dev = new SensorDevice();
            $dev->setName("Current 1");
            $dev->setValue($current / 1000000);
            $this->mbinfo->setMbCurrent($dev);
        }
    }

    public function build()
    {
        $this->_temperature();
        $this->_voltage();
        $this->_current();
    }
}
