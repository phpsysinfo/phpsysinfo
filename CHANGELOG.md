Changelog of phpSysInfo
=======================

http://phpsysinfo.sourceforge.net/

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

 - [ADD] IPFire, Sabayon, PearOS, ClearOS, Frugalware, Fuduntu, Foresight, Tinycore, ALT Linux, ROSA and RedHatEnterpriseServer to detected distros

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
