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
        if (file_exists('/sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/temp1_input')) { //Banana Pi
            $temp = file_get_contents('/sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/temp1_input');
            $temp_max = -1;
        } else {
            $temp = file_get_contents('/sys/class/thermal/thermal_zone0/temp');
            $temp_max = file_get_contents('/sys/class/thermal/thermal_zone0/trip_point_0_temp');
        }
        $dev = new SensorDevice();
        $dev->setName("CPU 1");
        $dev->setValue($temp / 1000);
        if ($temp_max > 0) {
            $dev->setMax($temp_max / 1000);
        }
        $this->mbinfo->setMbTemp($dev);
    }

    public function build()
    {
        $this->_temperature();
    }
}
