<?php
/**
 * PSStatus Plugin Config File
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_PSStatus
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: PSStatus.config.php 639 2012-08-24 17:09:35Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define how to access the psstatus statistic data
 * - 'command' pidof command is run everytime the block gets refreshed or build / on WINNT information is retrieved everytime through WMI
 * - 'data' a file must be available in the data directory of the phpsysinfo installation with the filename "psstatus.txt"; content is the output from
 *   <code>ps=("apache2" "mysqld" "sshd"); for((i=0;i<${#ps};i++)); do echo ${ps[$i]} "|" `pidof -s ${ps[$i]}` ;done</code>
 *
 * @var string
 */
define('PSI_PLUGIN_PSSTATUS_ACCESS', 'command');

/**
 * controls which processes are checked if they are running
 *
 * @var string contains a list of process names that are checked, names are seperated by a comma (on WINNT names must end with '.exe')
 */
define('PSI_PLUGIN_PSSTATUS_PROCESSES', 'mysqld, sshd, explorer.exe');
?>
