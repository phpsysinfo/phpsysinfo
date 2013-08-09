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
        $temp = file_get_contents('/sys/class/thermal/thermal_zone0/temp');
        $temp_max = file_get_contents('/sys/class/thermal/thermal_zone0/trip_point_0_temp');
        $temp = $temp /1000;
        $temp_max = $temp_max/1000;
        $dev = new SensorDevice();
        $dev->setName("CPU ".(1));
        $dev->setValue($temp);
        $dev->setMax($temp_max);
        $this->mbinfo->setMbTemp($dev);
    }

    public function build()
    {
        $this->_temperature();
    }
}
