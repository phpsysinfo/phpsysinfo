<?php
/**
 * start page for webaccess
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Web
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Webpage.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * generate the dynamic webpage
 *
 * @category  PHP
 * @package   PSI_Web
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Webpage extends Output implements PSI_Interface_Output
{
    /**
     * configured indexname
     *
     * @var String
     */
    private $_indexname;

    /**
     * configured language
     *
     * @var String
     */
    private $_language;

    /**
     * configured template
     *
     * @var String
     */
    private $_template;

    /**
     * all available templates
     *
     * @var Array
     */
    private $_templates = array();

    /**
     * configured bootstrap template
     *
     * @var String
     */
    private $_bootstrap_template;

    /**
     * all available bootstrap templates
     *
     * @var Array
     */
    private $_bootstrap_templates = array();

    /**
     * all available languages
     *
     * @var Array
     */
    private $_languages = array();

    /**
     * configured show picklist language
     *
     * @var boolean
     */
    private $_pick_language;

    /**
     * configured show picklist template
     *
     * @var boolean
     */
    private $_pick_template;

    /**
     * check for all extensions that are needed, initialize needed vars and read phpsysinfo.ini
     */
    public function __construct($indexname="dynamic")
    {
        $this->_indexname = $indexname;
        parent::__construct();
        $this->_getTemplateList();
        $this->_getLanguageList();
    }

    /**
     * checking phpsysinfo.ini setting for template, if not supportet set phpsysinfo.css as default
     * checking phpsysinfo.ini setting for language, if not supported set en as default
     *
     * @return void
     */
    private function _checkTemplateLanguage()
    {
        if (!defined("PSI_DEFAULT_TEMPLATE") || (($this->_template = strtolower(trim(PSI_DEFAULT_TEMPLATE))) == "") || !file_exists(APP_ROOT.'/templates/'.$this->_template.".css")) {
            $this->_template = 'phpsysinfo';
        }
        if (!defined("PSI_DEFAULT_BOOTSTRAP_TEMPLATE") || (($this->_bootstrap_template = strtolower(trim(PSI_DEFAULT_BOOTSTRAP_TEMPLATE))) == "") || !file_exists(APP_ROOT.'/templates/'.$this->_bootstrap_template.".css")) {
            $this->_bootstrap_template = 'phpsysinfo';
        }
        $this->_pick_template = !defined("PSI_SHOW_PICKLIST_TEMPLATE") || (PSI_SHOW_PICKLIST_TEMPLATE !== false);

        if (!defined("PSI_DEFAULT_LANG") || (($this->_language = strtolower(trim(PSI_DEFAULT_LANG))) == "") || !file_exists(APP_ROOT.'/language/'.$this->_language.".xml")) {
            $this->_language = 'en';
        }
        $this->_pick_language = !defined("PSI_SHOW_PICKLIST_LANG") || (PSI_SHOW_PICKLIST_LANG !== false);
    }

    /**
     * get all available tamplates and store them in internal array
     *
     * @return void
     */
    private function _getTemplateList()
    {
        $dirlist = CommonFunctions::gdc(APP_ROOT.'/templates/');
        sort($dirlist);
        foreach ($dirlist as $file) {
            $tpl_ext = substr($file, strlen($file) - 4);
            $tpl_name = substr($file, 0, strlen($file) - 4);
            if ($tpl_ext === ".css") {
                if (preg_match("/(\S+)_bootstrap$/", $tpl_name, $ar_buf)) {
                    array_push($this->_bootstrap_templates, $ar_buf[1]);
                } else {
                    array_push($this->_templates, $tpl_name);
                }
            }
        }
    }

    /**
     * get all available translations and store them in internal array
     *
     * @return void
     */
    private function _getLanguageList()
    {
        $dirlist = CommonFunctions::gdc(APP_ROOT.'/language/');
        sort($dirlist);
        foreach ($dirlist as $file) {
            $lang_ext = substr($file, strlen($file) - 4);
            $lang_name = substr($file, 0, strlen($file) - 4);
            if ($lang_ext == ".xml") {
                array_push($this->_languages, $lang_name);
            }
        }
    }

    /**
     * render the page
     *
     * @return void
     */
    public function run()
    {
        $this->_checkTemplateLanguage();

        $tpl = new Template("/templates/html/index_".$this->_indexname.".html");

        $tpl->set("template", $this->_template);
        $tpl->set("templates", $this->_templates);
        $tpl->set("bootstraptemplate", $this->_bootstrap_template);
        $tpl->set("bootstraptemplates", $this->_bootstrap_templates);
        $tpl->set("picktemplate", $this->_pick_template);
        $tpl->set("language", $this->_language);
        $tpl->set("languages", $this->_languages);
        $tpl->set("picklanguage", $this->_pick_language);

        echo $tpl->fetch();
    }
}
