<?php
/**
 * BAT Plugin Config File
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_BAT
 * @author    Erkan VALENTIN <jacky672@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: BAT.config.php 334 2009-09-16 15:21:39Z jacky672 $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define how to access the battery statistic data
 * - 'command' read /proc/acpi/battery/BAT0/info and read /proc/acpi/battery/BAT0/state
 * - 'data' (a file must be available in the data directory of the phpsysinfo installation with the filename "bat_info.txt" and "bat_state.txt"; content is the output from "cat /proc/acpi/battery/BAT0/info" and "cat /proc/acpi/battery/BAT0/state")
 */
define('PSI_PLUGIN_BAT_ACCESS', 'command');

 /**
 * define the battery device
 */
define('PSI_PLUGIN_BAT_DEVICE', 'BAT0');

?>
