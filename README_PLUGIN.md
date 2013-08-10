phpSysInfo 3.1 - http://phpsysinfo.sourceforge.net/
===================================================

Document written by Michael Cramer (bigmichi1 at sourceforge.net)

!!Please read if you want to develop a plugin to understand our plugin system!!


Plugins
-------

Beginning with phpSysInfo 3.0, phpSysInfo can be extended by Plugins. So here is
a description that a developer of a plugin must take care of. Plugins can be
enabled through the `phpsysinfo.ini` in the PLUGINS variable. The name of the
plugin is essential for the function of the plugin system. Lets say you write a
plugin with the name 'hdd_stats', then this name is added to the PLUGINS
variable in `phpsysinfo.ini`. And this is also then the name which is everywhere in
the plugin system used, like creating the object, locate the needed files and
so on.

So if the name is now specified, phpSysInfo needs a special directory structure
to find the needed files. The directory structure for the example `hdd_stats`
plugin can be seen here:

```
-+ phpSysInfo root
 |
 +---+ plugins (directory in that plugins are installed)
 |   |
 |   +---+ hdd_stats (the real plugin directory, must have the same name like
 |   |   |            the plugin named in PLUGINS, else it won't be found)
 |   |   |
 |   |   +---+ js (directory in which the needed JavaScript file is located,
 |   |   |   |     to generate the html output out of the xml)
 |   |   |   # hdd_stats.js (the js file must have the same name, like the
 |   |   |                   plugin in PSI_PLUGINS with the extension js)
 |   |   +---+ css (directory in which the needed style sheet information are
 |   |   |   |      located, can exists, but it's up to the author)
 |   |   |   # hdd_stats.css (the css file must have the same name, like the
 |   |   |                    plugin in PSI_PLUGINS with the extension css)
 |   |   +---+ lang (directory where translations for the plugin are located)
 |   |   |   |
 |   |   |   # en.xml (at least an english translation file must exist)
 |   |   |
 |   |   # class.hdd_stats.inc.php (this is the core file of the plugin,
 |   |                              name must consists of 'class' +
 |   |                              name from PSI_PLUGINS + '.inc.php')
```

other files or directorys can be included in the plugin directory, but then
its up to the developer to include them in the plugin. So it might be possible
to have a 'gfx' directory in which some pics are located that are used in the
output.

If the directory structure is build up, then it's time to start programming.

Files
-----

An example implementation is the mdstat plugin, which is shipped with phpSysInfo

* en.xml - at least this file must exist to get the translation working, and the
         the first entry in this file is normally the headline of the plugin.
         So one translation migth exists everytime. Other translation files
         are also in the same directory like the `en.xml` file.
         The id's specified in the translation file SHOULD have the following
         look `plugin_hdd_status_001`. First we say that this is a plugin
         translation, then the name of plugin and at last a increasing number
         for each translation. Please create your id's in that way, so that
         other plugins don't redefine your translations. At the time of writing
         this, there is no check to verify the id's, so be carfull.

* hdd_stats.css - here can all custom style sheet informations written down. The
         names of the id's and classes SHOULD also begin, like the translation
         id's, with `'plugin_' + pluginname`. If thats not the case it might be
         possible that another plugin is overwriting your css definitions.

* class.hdd_stats.inc.php - this file MUST include a class with the plugin name
         and also this class MUST extend the 'psi_plugin' class. A check that
         such a class exist and also extends 'psi_plugin' will be included in
         the near future. And if the check fails the plugin won't be loaded.
         The psi_plugin class checks the existens of the js and the en.xml
         files. Also an extra configuration of the plugin is loaded
         automatically from `phpsysinfo.ini`, if present.
         Through the extension of the psi_plugin class there is a need to
         include at least two public function. These are the execute() function
         and the xml() function. Other functions can be exist, that depends on
         the plugin needs or the author of the class. The execute() function is
         called to get the required information that should be later included
         in the xml file. The xml() function is called when the xml output
         should be generated. This function must return a simplexml object. This
         object is then included in another xml at the right position or as a
         standalone xml. So there is no need to do some special things, only
         create a xml object for the plugin.

* hdd_stats.js - this file is called when the page is loaded. A block for the
        plugin is automatically created. This one is a div container with the
        id `'plugin_'+ pluginname ("plugin_hdd_stats")`. The entire output must be
        placed in that container.
        There is a helper function for creating the headline: buildBlock() that
        can be called. This function returns a string with the html code of the
        headline, this code can then be appended to the plugin block. The
        generated headline can provide a reload icon for an ajax request. Only
        the click action of that icon must be created. The id of this icon is
        `'reload_' + pluginname + 'Table' ("reload_hdd_statsTable")`.
        Everything that then is done to get the html output out of the xml is up
        to the author.
        To get the xml document the ajax request url is `'xml.php?plugin=' +
        pluginname (xml.php?plugin=hdd_stats)`. This xml includes only the xml
        from the plugin nothing more.
        The last two executed commands should/must be the translation call and
        the unhide of the filled div container.
        The translation function that needs to be called is named
        plugin_traslate() with one argument, that is the pluginname like in
        `PSI_PLUGINS (plugin_translate("hdd_stats");)`.
        To unhide the filled container call the .show() function of it.
        `$("plugin_" + pluginname).show() ($("plugin_hdd_stat").show())`.

FAQ
---

Q: Is the plugin system ready to use?

A: It can be used, but it might change slightly in the future, if there are some
   special needs.

SUGGESTION
----------

If anybody out there has some suggestions in improving the plugin system let us
know. We are looking forward to get some feedback, suggestions and patches and
more. Feel free to contact us on our website: http://phpsysinfo.sourceforge.net.

$Id: README_PLUGIN 463 2011-04-19 17:34:41Z namiltd $
