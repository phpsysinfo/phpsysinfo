phpSysInfo
==============

* Copyright (c), 1999-2008, Uriah Welcome (precision@users.sf.net)
* Copyright (c), 1999-2009, Michael Cramer (bigmichi1@users.sf.net)
* Copyright (c), 2007-2008, Audun Larsen (xqus@users.sf.net)
* Copyright (c), 2007-2014, Erkan Valentin
* Copyright (c), 2009-2014, Mieczyslaw Nalewaj (namiltd@users.sf.net)
* Copyright (c), 2010-2012, Damien Roth (iysaak@users.sf.net)



CURRENT TESTED PLATFORMS
------------------------

- Linux 2.6.x
- FreeBSD 7.x
- OpenBSD 2.8+
- NetBSD
- DragonFly
- IBM AIX
- HP-UX
- Darwin/OSX
- Windows 2000 / Windows 2003 / Windows XP / Windows Vista / Windows 7 / Windows 8  / Windows 8.1
- > PHP 5.2 or later
  - With PCRE, XML, XSL, MBString and SimpleXML extension.

####Platforms currently in progress:
- Haiku
- Minix
- SunOS
- ReactOS
- Android
- QNX

If your platform is not here try checking out the mailing list archives or
the message boards on SourceForge.

INSTALLATION AND CONFIGURATION
------------------------------

Just decompress and untar the source (which you should have done by now,
if you're reading this...), into your webserver's document root.

There is a configuration file called phpsysinfo.ini.new. If this a brand new
installation, you should copy this file to phpsysinfo.ini and edit it.

- make sure your `php.ini` file's `include_path` entry contains "."
- make sure your `php.ini` has `safe_mode` set to 'off'.

phpSysInfo require php-xml extension.

Please keep in the mind that because phpSysInfo requires access to many
files in `/proc` and other system binary you **MUST DISABLE** `php's safe_mode`.
Please see the PHP documentation for information on how you
can do this.

That's it.  Restart your webserver (if you changed php.ini), and voila.

KNOWN PROBLEMS
--------------

- phpSysInfo is not compatible with SELinux Systems
- small bug under FreeBSD with memory reporting

PLATFORM SPECIFIC ISSUES
------------------------

####Windows with IIS
  On Windows systems we get our informations through the WMI interface.
  If you run phpSysInfo on the IIS webserver, phpSysInfo will not connect
  to the WMI interface for security reasons. At this point you MUST set
  an authentication mechanism for the directory in the IIS admin
  interface for the directory where phpSysInfo is installed. Then you
  will be asked for an user and a password when opening the page. At this
  point it is necassary to log in with an user that will be able to
  connect to the WMI interface. If you use the wrong user and/or password
  you might get an "ACCESS DENIED ERROR".

SENSOR RELATED INFORMATION
---------------------------

####MBM5
  Make sure you set MBM5 Interval Logging to csv and to the data
  directory of phpSysInfo. The file must be called MBM5. Also make sure
  MBM5 doesn't add symbols to the values. This is a Quick MBM5 log parser,
  need more csv logs to make it better.

WHAT TO DO IF IT DOESN'T WORK
-----------------------------

First make sure you've read this file completely, especially the
"INSTALLATION AND CONFIGURATION" section.  If it still doesn't work then
you can:

Submit a bug on SourceForge (preferred) (http://sourceforge.net/projects/phpsysinfo/)

Ask for help in the forum (http://sourceforge.net/projects/phpsysinfo/)

***!! If you have any problems, please set `DEBUG` to true in `phpsysinfo.ini` 
and include any error messages in your bug report / help request !!***

OTHER NOTES
-----------

If you have a great idea or want to help out, just drop by the project
page at SourceForge (http://sourceforge.net/projects/phpsysinfo/).

LICENSING
---------

This program and all associated files are released under the GNU Public
License, see [COPYING](COPYING) for details.
