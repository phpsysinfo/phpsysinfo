<?php
/**
 * SensorDevice TO class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.SensorDevice.inc.php 592 2012-07-03 10:55:51Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * SensorDevice TO class
 *
 * @category  PHP
 * @package   PSI_TO
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class SensorDevice
{
    /**
     * name of the sensor
     *
     * @var string
     */
    private $_name = "";

    /**
     * current value of the sensor
     *
     * @var int
     */
    private $_value = 0;

    /**
     * maximum value of the sensor
     *
     * @var int
     */
    private $_max = null;

    /**
     * minimum value of the sensor
     *
     * @var int
     */
    private $_min = null;

    /**
     * event of the sensor
     *
     * @var string
     */
    private $_event = "";

    /**
     * unit of values of the sensor
     *
     * @var string
     */
    private $_unit = "";

    /**
     * Returns $_max.
     *
     * @see Sensor::$_max
     *
     * @return int
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets $_max.
     *
     * @param int $max maximum value
     *
     * @see Sensor::$_max
     *
     * @return void
     */
    public function setMax($max)
    {
        $this->_max = $max;
    }

    /**
     * Returns $_min.
     *
     * @see Sensor::$_min
     *
     * @return int
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets $_min.
     *
     * @param int $min minimum value
     *
     * @see Sensor::$_min
     *
     * @return void
     */
    public function setMin($min)
    {
        $this->_min = $min;
    }

    /**
     * Returns $_name.
     *
     * @see Sensor::$_name
     *
     * @return String
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets $_name.
     *
     * @param String $name sensor name
     *
     * @see Sensor::$_name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns $_value.
     *
     * @see Sensor::$_value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Sets $_value.
     *
     * @param int $value current value
     *
     * @see Sensor::$_value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Returns $_event.
     *
     * @see Sensor::$_event
     *
     * @return String
     */
    public function getEvent()
    {
        return $this->_event;
    }

    /**
     * Sets $_event.
     *
     * @param String $event sensor event
     *
     * @see Sensor::$_event
     *
     * @return void
     */
    public function setEvent($event)
    {
        $this->_event = $event;
    }

    /**
     * Returns $_unit.
     *
     * @see Sensor::$_unit
     *
     * @return String
     */
    public function getUnit()
    {
        return $this->_unit;
    }

    /**
     * Sets $_unit.
     *
     * @param String $unit sensor unit
     *
     * @see Sensor::$_unit
     *
     * @return void
     */
    public function setUnit($unit)
    {
        $this->_unit = $unit;
    }
}
