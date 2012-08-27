<?php
/**
 * MDStatus Plugin Config File
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_MDStatus
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: MDStatus.config.php 639 2012-08-24 17:09:35Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define how to access the mdstat statistic data
 * - 'file' /proc/mdstat is read
 * - 'data' (a file must be available in the data directory of the phpsysinfo installation with the filename "mdstat.txt"; content is the output from "cat /proc/mdstat")
 */
define('PSI_PLUGIN_MDSTATUS_ACCESS', 'file');
?>
