<?php
/**
 * SNMPPInfo Plugin Config File
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_SNMPPInfo
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2011 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: SNMPPInfo.config.php 457 2011-04-15 23:07:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * define how to access the SNMP Printer Info statistic data
 * - 'php-snmp' execute php snmprealwalk function (php-snmp module must be installed)
 * - 'command' execute snmpwalk command
 * - 'data' a file must be available in the data directory of the
 * phpsysinfo installation with the filename "SNMPPInfo{printer_number}.txt";
 * content is the output from: 
    snmpwalk -On -c public -v 1 {printer_address} 1.3.6.1.2.1.1.5 > SNMPPInfo{printer_number}.txt
    snmpwalk -On -c public -v 1 {printer_address} 1.3.6.1.2.1.43.11.1.1 >> SNMPPInfo{printer_number}.txt 
 */
define('PSI_PLUGIN_SNMPPINFO_ACCESS', 'php-snmp');

 /**
 * define the Printer devices
 *
 *  @var string contains a list of printer addresses that are checked
 */
define('PSI_PLUGIN_SNMPPINFO_DEVICES', '192.168.0.5, 192.168.0.9');

?>
