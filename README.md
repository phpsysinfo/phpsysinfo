phpSysInfo
==============

* Copyright (c), 1999-2008, Uriah Welcome ([sf.net/users/precision](https://sf.net/users/precision))
* Copyright (c), 1999-2009, Michael Cramer ([sf.net/users/bigmichi1](https://sf.net/users/bigmichi1))
* Copyright (c), 2007-2008, Audun Larsen ([sf.net/users/xqus](https://sf.net/users/xqus))
* Copyright (c), 2007-2015, Erkan Valentin ([github.com/rk4an](https://github.com/rk4an), [sf.net/users/jacky672](https://sf.net/users/jacky672))
* Copyright (c), 2009-2020, Mieczyslaw Nalewaj ([github.com/namiltd](https://github.com/namiltd), [sf.net/users/namiltd](https://sf.net/users/namiltd))
* Copyright (c), 2010-2012, Damien Roth ([sf.net/users/iysaak](https://sf.net/users/iysaak))


REQUIREMENTS
------------

PHP 5.1.3 or later with SimpleXML, PCRE, XML and DOM extension.

#### Suggested extensions:
- mbstring: Required for *nix non UTF-8 systems
- com_dotnet: Required for Windows environments
- xsl: Required for static mode
- json: Required for bootstrap mode

CURRENT TESTED PLATFORMS
------------------------

- Linux 2.6+
- FreeBSD 7+
- OpenBSD 2.8+
- NetBSD
- DragonFly
- HP-UX
- Darwin / Mac OS / OS X
- Windows 2000 / XP / 2003 / Vista / 2008 / 7 / 2011 / 2012 / 8 / 8.1 / 10 / 2016 / 2019
- Android

#### Platforms currently in progress:
- Haiku
- Minix
- SunOS
- ReactOS
- IBM AIX
- QNX

If your platform is not here try checking out the mailing list archives or
the message boards on SourceForge.

INSTALLATION AND CONFIGURATION
------------------------------

#### Typical installation

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

#### Docker container installation

- `sudo docker build -t phpsysinfo github.com/phpsysinfo/phpsysinfo`
- `sudo docker run -i -p 8080:80 -t phpsysinfo`
- go to http://localhost:8080/phpsysinfo/

KNOWN PROBLEMS
--------------

- phpSysInfo is not compatible with SELinux Systems
- small bug under FreeBSD with memory reporting

PLATFORM SPECIFIC ISSUES
------------------------

#### Windows with IIS
  On Windows systems we get our informations through the WMI interface.
  If you run phpSysInfo on the IIS webserver, phpSysInfo will not connect
  to the WMI interface for security reasons. At this point you MUST set
  an authentication mechanism for the directory in the IIS admin
  interface for the directory where phpSysInfo is installed. Then you
  will be asked for an user and a password when opening the page. At this
  point it is necessary to log in with an user that will be able to
  connect to the WMI interface. If you use the wrong user and/or password
  you might get an "ACCESS DENIED ERROR".

SENSOR RELATED INFORMATION
---------------------------

#### MBM5
  Make sure you set MBM5 Interval Logging to csv and to the data
  directory of phpSysInfo. The file must be called MBM5. Also make sure
  MBM5 doesn't add symbols to the values. This is a Quick MBM5 log parser,
  need more csv logs to make it better.

WHAT TO DO IF IT DOESN'T WORK
-----------------------------

First make sure you've read this file completely, especially the
"INSTALLATION AND CONFIGURATION" section.  If it still doesn't work then
you can:

Ask for help or submit a bug on Github (https://github.com/phpsysinfo/phpsysinfo/issues)

***!! If you have any problems, please set `DEBUG` to true in `phpsysinfo.ini`
and include any error messages in your bug report / help request !!***

OTHER NOTES
-----------

If you have a great idea or want to help out, just create a pull request with your change proposal
in the [phpSysInfo](https://github.com/phpsysinfo/phpsysinfo) repository.

LICENSING
---------

This program is released under the GNU Public License Version 2 or 
(at your option) any later version, see [COPYING](COPYING) for details.
