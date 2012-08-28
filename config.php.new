<?php 
/**
 * PSI Config File
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: config.php.new 622 2012-08-04 20:39:40Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 
// ********************************
//        MAIN PARAMETERS
// ********************************

/**
 * Turn on debugging of some functions and include errors and warnings in xml and provide a popup for displaying errors
 * - false : no debug information are stored in xml or displayed
 * - true : debug information stored in xml and displayed *be careful if set this to true, may include sensitive information from your pc*
 */
define('PSI_DEBUG', false);

/**
 * Turn on/off compression for JavaScript file
 * - define('PSI_JS_COMPRESSION', false); //no compression (recommended with slow processor)
 * - define('PSI_JS_COMPRESSION', 'None'); //code minimizing
 * - define('PSI_JS_COMPRESSION', 'Normal'); //code packing
 */
define('PSI_JS_COMPRESSION', 'Normal');

/**
 * Additional paths where to look for installed programs
 * Example : define('PSI_ADD_PATHS', '/opt/bin,/opt/sbin');
 */
define('PSI_ADD_PATHS', false);

/**
 * Plugins that should be included in xml and output (!!!plugin names are case-sensitive!!!)
 * List of plugins should look like "plugin,plugin,plugin". See /plugins directory
 * - define('PSI_PLUGINS', 'MDStatus,PS'); // list of plugins
 * - define('PSI_PLUGINS', false); //no plugins
 * included plugins:
 * - MDStatus       - show the raid status and whats currently going on
 * - PS             - show a process tree of all running processes
 * - PSStatus       - show a graphical representation if a process is running or not
 * - Quotas         - show a table with all quotas that are active and there current state
 * - SMART          - show S.M.A.R.T. information from drives that support it
 * - BAT            - show battery state on a laptop
 * - ipmi           - show IPMI status
 * - UpdateNotifier - show update notifications (only for Ubuntu server)
 * - SNMPPInfo      - show printers info via SNMP
 */
define('PSI_PLUGINS', false);


// ********************************
//       DISPLAY PARAMETERS
// ********************************

/**
 * Define the default display mode
 * auto: let user browser choose the mode
 * dynamic: use javascript to refresh data
 * static: static page (use metatag to reload page)
 */
define('PSI_DEFAULT_DISPLAY_MODE', 'auto');

/**
 * Define the default language
 */
define('PSI_DEFAULT_LANG', 'en');

/**
 * Define the default template
 */
define('PSI_DEFAULT_TEMPLATE', 'phpsysinfo');

/**
 * Show or hide language picklist
 */
define('PSI_SHOW_PICKLIST_LANG', true);

/**
 * Show or hide template picklist
 */
define('PSI_SHOW_PICKLIST_TEMPLATE', true);

/**
 * Define the interval for refreshing data in ms
 * - 0 = disabled
 * - 1000 = 1 second
 * - Default is 60 seconds
 */
define('PSI_REFRESH', 60000);

/**
 * Show a graph for current cpuload
 * - true = displayed, but it's a performance hit (because we have to wait to get a value, 1 second)
 * - false = will not be displayed
 */
define('PSI_LOAD_BAR', false);

/**
 * Display the virtual host name and address
 * - Default is canonical host name and address
 * - Use define('PSI_USE_VHOST', true); to display virtual host name.
 */
define('PSI_USE_VHOST', false);

/**
 * Controls the units & format for network, memory and filesystem
 * - 1 KiB = 2^10 bytes = 1,024 bytes
 * - 1 KB = 10^3 bytes = 1,000 bytes
 * - 'B'    everything is in Byte
 * - 'PiB'    everything is in PeBiByte
 * - 'TiB'    everything is in TeBiByte
 * - 'GiB'    everything is in GiBiByte
 * - 'MiB'    everything is in MeBiByte
 * - 'KiB'    everything is in KiBiByte
 * - 'auto_binary' everything is automatic done if value is to big for, e.g MiB then it will be in GiB
 * - 'PB'    everything is in PetaByte
 * - 'TB'    everything is in TeraByte
 * - 'GB'    everything is in GigaByte
 * - 'MB'    everything is in MegaByte
 * - 'KB'    everything is in KiloByte
 * - 'auto_decimal' everything is automatic done if value is to big for, e.g MB then it will be in GB
 */
define('PSI_BYTE_FORMAT', 'auto_binary');

/**
 * Format in which temperature is displayed
 * - 'c'    shown in celsius
 * - 'f'    shown in fahrenheit
 * - 'c-f'  both shown first celsius and fahrenheit in braces
 * - 'f-c'  both shown first fahrenheit and celsius in braces
 */
define('PSI_TEMP_FORMAT', 'c');


// ********************************
//       SENSORS PARAMETERS
// ********************************

/**
 * Define the motherboard monitoring program (!!!names are case-sensitive!!!)
 * We support the following programs so far
 * - LMSensors  http://www.lm-sensors.org/
 * - Healthd    http://healthd.thehousleys.net/
 * - HWSensors  http://www.openbsd.org/
 * - MBMon      http://www.nt.phys.kyushu-u.ac.jp/shimizu/download/download.html
 * - MBM5       http://mbm.livewiredev.com/
 * - Coretemp
 * - IPMI       http://openipmi.sourceforge.net/
 * - K8Temp     http://hur.st/k8temp/
 * Example: If you want to use lmsensors : define('PSI_SENSOR_PROGRAM', 'LMSensors');
 */
define('PSI_SENSOR_PROGRAM', false);

/**
 * Define how to access the monitor program
 * Available methods for the above list are in the following list
 * default method 'command' should be fine for everybody
 * !!! tcp connections are only made local and on the default port !!!
 * - LMSensors  command, file
 * - Healthd    command
 * - HWSensors  command
 * - MBMon      command, tcp
 * - MBM5       file
 * - Coretemp   command
 * - IPMI       command
 * - K8Temp     command
 */
define('PSI_SENSOR_ACCESS', 'command');

/**
 * Hddtemp program
 * If the hddtemp program is available we can read the temperature, if hdd is smart capable
 * !!ATTENTION!! hddtemp might be a security issue
 * - define('PSI_HDD_TEMP', 'tcp');	     // read data from hddtemp deamon (localhost:7634)
 * - define('PSI_HDD_TEMP', 'command');  // read data from hddtemp programm (must be set suid)
 */
define('PSI_HDD_TEMP', false);


// ********************************
//      FILESYSTEM PARAMETERS
// ********************************

/**
 * Show mount point
 * - true = show mount point
 * - false = do not show mount point
 */
define('PSI_SHOW_MOUNT_POINT', true);

/**
 * Show mount option
 * - true = show mount option
 * - false = do not show mount option
 */
define('PSI_SHOW_MOUNT_OPTION', true);

/**
 * Show mount credentials
 * - true = show mount credentials
 * - false = do not show mount credentials
 */
define('PSI_SHOW_MOUNT_CREDENTIALS', false);

/**
 * Show inode usage
 * - true = display used inodes in percent
 * - false = hide them
 */
define('PSI_SHOW_INODES', true);

/**
 * Hide mounts
 * Example : define('PSI_HIDE_MOUNTS', '/home,/usr');
 */
define('PSI_HIDE_MOUNTS', '');

/**
 * Hide filesystem types
 * Example : define('PSI_HIDE_FS_TYPES', 'tmpfs,devtmpfs,usbfs');
 */
define('PSI_HIDE_FS_TYPES', '');

/**
 * Hide partitions
 * Example : define('PSI_HIDE_DISKS', 'rootfs');
 */
define('PSI_HIDE_DISKS', '');


// ********************************
//      NETWORK PARAMETERS
// ********************************

/**
 * Hide network interfaces
 * Example : define('PSI_HIDE_NETWORK_INTERFACE', 'eth0,sit0');
 */
define('PSI_HIDE_NETWORK_INTERFACE', '');

/**
 * Show network interfaces infos for Linux, FreeBSD, Haiku and WinNT (experimental)
 * Example : define('PSI_SHOW_NETWORK_INFOS', true);
 */
define('PSI_SHOW_NETWORK_INFOS', false); 


// ********************************
//        UPS PARAMETERS
// ********************************

/**
 * Define the ups monitoring program (!!!names are case-sensitive!!!)
 * We support the following programs so far
 * - 1. Apcupsd  http://www.apcupsd.com/
 * - 2. Nut      http://www.networkupstools.org/
 * Example: If you want to use Apcupsd : define('PSI_UPS_PROGRAM', 'Apcupsd');
 */
define('PSI_UPS_PROGRAM', false);

/**
 * Apcupsd supports multiple UPSes
 * You can specify comma delimited list in the form <hostname>:<port> or <ip>:<port>. The defaults are: 127.0.0.1:3551
 * See the following parameters in apcupsd.conf: NETSERVER, NISIP, NISPORT
 */
define('PSI_UPS_APCUPSD_LIST', '127.0.0.1:3551');

?>
