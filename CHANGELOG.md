Changelog of phpSysInfo
=======================

http://phpsysinfo.sourceforge.net/

phpSysInfo 3.2.10
----------------
 - [UPD] jQuery 3.3.1

 - [ADD] NuTyX to detected distros

 - [FIX] Raid plugin - fixed zpool analize

phpSysInfo 3.2.9
----------------
 - [UPD] Greek Translation gr.xml

 - [ADD] BAT plugin - Model, Manufacturer and Serial Number information
 - [ADD] BAT plugin - SHOW_SERIAL and UPOWER option
 - [ADD] Springdale Linux to detected distros

 - [NEW] SHOW_DEVICES_INFOS and SHOW_DEVICES_SERIAL options
 - [NEW] Thinkpad - Lenovo Thinkpad sensors monitoring for Linux
 - [NEW] StableBit plugin - StableBit Scanner info
 - [NEW] HyperV plugin - Hyper-V info
 - [NEW] Raid plugin - software, fake, zpool and megaraid RAID status

 - [DEL] Removed deprecated MDStatus and DMRaid plugins

phpSysInfo 3.2.8
----------------
 - [UPD] jQuery 3.2.1
 - [UPD] French Translation fr.xml
 - [UPD] CPU usage on WinNT
 - [UPD] Ukrainian Translation uk.xml

 - [FIX] Fixed incorrect network usage on FreeBSD 11
 - [FIX] Fixed healthd sensors
 - [FIX] Fixed displaying for code pages windows-932, windows-949 and windows-950 for WinNT

 - [ADD] Container Linux, PureOS, OpenELEC, LibreELEC and Lakka to detected distros
 - [ADD] FreeIPMI, IPMItool and IPMIutil other information
 - [ADD] PS plugin - CPU usage
 - [ADD] PS plugin - MEMORY_USAGE, CPU_USAGE and SHOW_PID1CHILD_EXPANDED options
 - [ADD] SMART plugin - WMI access for WinNT

 - [NEW] DATETIME_FORMAT option
 - [NEW] BLOCKS option - order of data blocks
 - [NEW] SHOW_NETWORK_ACTIVE_SPEED option
 - [NEW] Ability to generate only part of the data
 - [NEW] LOAD_BAR compact mode option
 - [NEW] PingTest plugin

 - [DEL] Removed deprecated IPMIinfo plugin

phpSysInfo 3.2.7
----------------
 - [UPD] jQuery 3.1.1
 - [UPD] Network interfaces info for WinNT
 - [UPD] Turkish Translation tr.xml
 - [UPD] Greek Translation gr.xml
 - [UPD] Korean Translation ko.xml
 - [UPD] Ukrainian Translation uk.xml

 - [ADD] Devuan and SalentOS to detected distros
 - [ADD] HIDE_NETWORK_MACADDR option

 - [NEW] Raspberry Pi detection on Linux

phpSysInfo 3.2.6
----------------
 - [UPD] jQuery 3.1.0 and jQuery 1.12.4
 - [UPD] bootstrap 3.3.7
 - [UPD] Swedish Translation sv.xml
 - [UPD] Norwegian Translation no.xml

 - [ADD] NeoKylin and Void to detected distros
 - [ADD] schabau and lingruby template
 - [ADD] DMRaid plugin - FreeBSD graid info
 - [ADD] SNMPPInfo plugin - support for Ricoh printers
 - [ADD] UpdateNotifier plugin - ACCESS option

 - [NEW] sudo commands list
 - [NEW] plugin Iptables - iptables rules list

phpSysInfo 3.2.5
----------------
 - [UPD] jQuery 2.2.0 and jQuery 1.12.0

 - [ADD] Show network interfaces infos for NetBSD and OpenBSD
 - [ADD] SHOW_NETWORK_INFOS_EXPANDED option
 - [ADD] SHOW_MEMORY_INFOS_EXPANDED option
 - [ADD] QTS, OpenMamba and HipServ to detected distros
 - [ADD] SNMPups - monitoring UPS via SNMP

 - [NEW] reloading plugins also for frontend "bootstrap"
 - [NEW] Network speed info on WinNT, Linux, Android, Darwin, NetBSD, OpenBSD and FreeBSD
 - [NEW] Coretemp monitoring also on Linux
 - [NEW] QTSsnmp - SNMP sensors monitoring for QTS Linux
 - [NEW] Hwmon - sensors monitoring for Linux
 - [NEW] SpeedFan - sensors monitoring for WinNT
 - [NEW] Separate configuration sections for motherboard monitoring sensors programs
 - [NEW] List of IP addresses of clients authorized to run

phpSysInfo 3.2.4
----------------
 - [ADD] Solus to detected distros
 - [ADD] Uprecords plugin - MAX_ENTRIES option - maximum number of entries to show

 - [UPD] Refreshing status of all plugins for dynamic mode
 - [UPD] bootstrap 3.3.6

 - [NEW] language and template selection also for frontend "bootstrap"

 - [FIX] PHP 7 class Error issues

phpSysInfo 3.2.3
----------------
 - [UPD] jQuery 2.1.4 and jQuery 1.11.3
 - [UPD] bootstrap 3.3.5

 - [NEW] I2C devices list on Android and Linux

 - [ADD] blue template

phpSysInfo 3.2.2
----------------
 - [UPD] bootstrap 3.3.4

 - [NEW] Thunderbolt (TB) devices list on Darwin

 - [ADD] cleansyn template
 - [ADD] PS plugin - support for Android
 - [ADD] PS plugin - SHOW_KTHREADD_EXPANDED option
 - [ADD] Qubes and HandyLinux to detected distros

 - [FIX] PS plugin - fixed display of the processes tree

phpSysInfo 3.2.1
----------------
 - [UPD] bootstrap 3.3.2

 - [NEW] REFRESH parameter also for frontend "bootstrap"

 - [ADD] pfSense to detected distros

 - [FIX] Percentage memory usage

phpSysInfo 3.2.0
----------------
 - [UPD] jQuery 2.1.3 and jQuery 1.11.2

 - [NEW] Frontend "bootstrap" with Bootstrap3 and Transparency (JSON)

 - [ADD] Catalan Translation ca.xml
 - [ADD] Mer (core of Sailfish OS) and Tizen to detected distros
 - [ADD] ThermalZone sensor support for Linux and Android
 - [ADD] Temperature, voltage and current sensor for Banana Pi

 - [UPD] German Translation de.xml

phpSysInfo 3.1.17
----------------

 - [UPD] BAT plugin - capacity unit, cycle count, FreeBSD support
 - [UPD] Brazilian Portuguese Translation pt-br.xml

 - [ADD] Show the number of processes on Haiku
 - [ADD] ThermalZone sensor support for WinNT
 - [ADD] Tanglu to detected distros
 - [ADD] Android and OS X version name

 - [FIX] Fixed display of treetables

phpSysInfo 3.1.16
----------------

 - [ADD] Show the number of processes
 - [ADD] Lunar and 4MLinux to detected distros

phpSysInfo 3.1.15
----------------

 - [ADD] CoreOS and Pisi Linux to detected distros
 - [ADD] pmset - UPS support on Darwin (thanks to pelletierr)
 - [ADD] lspci support on Darwin
 - [ADD] SHOW_CPULIST_EXPANDED option
 - [ADD] lxc/docker detection on Linux
 - [ADD] ability to hide all disks and all network interfaces

 - [FIX] Small CSS fix

 - [UPD] Memory informations on Darwin systems
 - [UPD] BAT plugin - Darwin support

phpSysInfo 3.1.14
----------------

 - [ADD] FreeIPMI sensor program support
 - [ADD] IPMIutil sensor program support
 - [ADD] PowerSoftPlus (EVER) UPS program support
 - [ADD] Line frequency for the UPS info
 - [ADD] SENSOR_EVENTS option - show events of sensors
 - [ADD] HIDE_RAID_DEVICES option for plugins MDStatus and DMRaid - list of RAID devices to hide
 - [ADD] idash template

phpSysInfo 3.1.13
----------------

 - [UPD] jQuery 2.1.1 and jQuery 1.11.1
 - [UPD] Romanian Translation ro.xml

 - [ADD] SHOW_CPUINFO_EXPANDED option
 - [ADD] Machine detection on WINNT, Linux, Android and Darwin systems

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

 - [NEW] plugin DMRaid - software raid status

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
