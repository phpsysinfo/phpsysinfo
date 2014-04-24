Changelog of phpSysInfo
=======================

http://phpsysinfo.sourceforge.net/

phpSysInfo 3.1.12
----------------

 - [ADD] GoboLinux, UltimateEdition, BOSS, Canaima, VortexBox, KaOS and NixOS to detected distros
 - [ADD] OpenHardwareMonitor sensor program support
 - [ADD] Possibility to define multiple UPS_PROGRAM
 - [ADD] UPS_NUT_LIST option

 - [FIX] Fixed incorrect network usage on FreeBSD

 - [UPD] SMART plugin - Smartctl --device option value setting method

phpSysInfo 3.1.11
----------------

 - [ADD] Add Access-Control-Allow-Origin on XML (JSON) interface for Ajax Load PR#47
 - [ADD] Generations Linux and SliTaz to detected distros
 - [ADD] IPMI and LMSensors currents information
 - [ADD] Plugin IPMIInfo - added powers and currents values
 - [ADD] Partial support of QNX

 - [FIX] Reduce execution time on Linux systems when showing load average PR#47

phpSysInfo 3.1.10
----------------

 - [ADD] Zenwalk and Raspbian to detected distros

 - [FIX] /etc/os-release Linux distro detection

phpSysInfo 3.1.9
----------------

 - [NEW] New plugin DMRaid - software raid status

 - [ADD] Calculate, Tails, SMEServer, Semplice, SolydXK, Parsix, RedFlag, Amazon, Korora, OpenMandriva, SteamOS, ROSA Enterprise Server and ROSA Desktop Fresh to detected distros

 - [UPD] Rebuilding of the Linux distribution detection
 - [UPD] jQuery 2.1.0 and jQuery 1.11.0

phpSysInfo 3.1.8
----------------

 - [ADD] Add printers messages in the XML output
 - [ADD] PSStatus plugin - added optional regular expression search in the process name
 - [ADD] RedHatEnterpriseClient distro icon #40
 - [ADD] Hebrew Translation he.xml

 - [FIX] BAT plugin - fix for old and new kernel /proc/acpi and /sys/class/power_supply

 - [UPD] LMSensors name for Mac hardware sensors

phpSysInfo 3.1.7
----------------

 - [ADD] Ksplice support for Linux
 - [ADD] Show CPU frequency max and min for Darwin (Mac OS X)
 - [ADD] Show System Language and Code Page on Darwin (Mac OS X)
 - [ADD] Show network interfaces infos for Minix and SunOS
 - [ADD] SMS, gNewSense and Vector to detected distros
 - [ADD] LMSensors power information
 - [ADD] Battery installation date for the UPS info

 - [UPD] Network interfaces infos and filesystems infos for FreeBSD
 - [UPD] Updated support of SunOS
 - [UPD] Memory informations on Darwin systems
 - [UPD] BAT plugin - updated Linux support
 - [UPD] Updated HWSensors - OpenBSD sensor program

phpSysInfo 3.1.6
----------------

 - [ADD] Porteus, Peppermint, Manjaro, Netrunner and Salix to detected distros
 - [ADD] Show CPU frequency max for WINNT
 - [ADD] Show network interfaces infos for Darwin (Mac OS X)

 - [UPD] SNMPPInfo plugin, ink level for some of the data
 - [UPD] jQuery 2.0.3 and jQuery 1.10.2
 - [UPD] Russian Translation ru.xml
 - [UPD] BAT plugin - WINNT support

 - [SEC] Fix JSONP

phpSysInfo 3.1.5
----------------

 - [ADD] Possibility to define multiple SENSOR_PROGRAM
 - [ADD] Added display of temperature and fan speed for IPMI sensor program
 - [ADD] openSUSE and Eisfair to detected distros
 - [ADD] Portuguese Translation pt-pt.xml

 - [FIX] Fixed incorrect display of the minimum fan speed
 - [FIX] Fix recovery detection of RAID arrays on debian systems #18

phpSysInfo 3.1.4
----------------

 - [ADD] Option for reading the results of functions executeProgram() and rfts() from log
 - [ADD] Show CPU frequency max and min for variable speed processors for Linux and Android
 - [ADD] Filesystem usage warning on defined threshold FS_USAGE_THRESHOLD

 - [UPD] BAT plugin - added temperature, condition and type of battery, Android support
 - [UPD] jQuery 2.0.2 and jQuery 1.10.1

phpSysInfo 3.1.3
----------------

 - [ADD] IPFire, Sabayon, PearOS, ClearOS, Frugalware, Fuduntu, Foresight, Tinycore, ALT Linux, ROSA Desktop Marathon and RedHatEnterpriseServer to detected distros

 - [UPD] Added "username" to filtered mount credentials
 - [UPD] jQuery 2.0 coexistent with jQuery 1.9.1 for old Internet Explorer browser versions (IE 6/7/8)

 - [FIX] proc_open() malfunction on some PHP for Android by replacing by popen()
 - [FIX] Run php-cs-fixer on php files (PSR-2 fixer)

phpSysInfo 3.1.2
----------------

 - [ADD] Tempsensor and CPU frequency for Raspberry Pi (thanks to hawkeyexp)
 - [ADD] Linaro to detected distros
 - [ADD] Option for logging of functions executeProgram() and rfts()
 - [ADD] Add support of JSONP

 - [FIX] Incorrect display of chunk size for the plugin mdstatus for some results

phpSysInfo 3.1.1
----------------

 - [ADD] SolusOS, Deepin and antiX to detected distros
 - [ADD] Simplified Chinese translation

 - [UPD] jQuery 1.9.1

phpSysInfo 3.1.0
----------------

 - [NEW] Configuration moved from config.php and subdirs of "plugins" to one file phpsysinfo.ini

 - [ADD] Turbolinux, Oracle Linux, CloudLinux, PCLinuxOS, StartOS, Trisquel, CRUX, Slax, Pear, Android, Zorin and elementary OS to detected distros
 - [ADD] Show System Language and Code Page on Linux, Haiku and WINNT
 - [ADD] Minor support of ReactOS
 - [ADD] apcupsd-cgi support (thanks to duhast)

 - [UPD] Plugin ipmi renamed to IPMIInfo and Update-Notifier to UpdateNotifier (to avoid name conflicts)
 - [UPD] Case-insensitive for most of parameters
 - [UPD] Detection of Mac OS X and Linux distribution
 - [UPD] CPU detection on Mac OS X

 - [FIX] Fixed UTF8 encoding for Linux
 - [FIX] SMART plugin doesn't display for some results
 - [FIX] Incorrect display of mountpoint on Mac OS X
