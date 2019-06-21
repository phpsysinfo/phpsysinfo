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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
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
     * @var array
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
     * @var array
     */
    private $_bootstrap_templates = array();

    /**
     * all available languages
     *
     * @var array
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
     * @param string $indexname
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
        if (!defined("PSI_DEFAULT_TEMPLATE") || (($this->_template = strtolower(trim(PSI_DEFAULT_TEMPLATE))) == "") || !file_exists(PSI_APP_ROOT.'/templates/'.$this->_template.".css")) {
            $this->_template = 'phpsysinfo';
        }
        if (!defined("PSI_DEFAULT_BOOTSTRAP_TEMPLATE") || (($this->_bootstrap_template = strtolower(trim(PSI_DEFAULT_BOOTSTRAP_TEMPLATE))) == "") || !file_exists(PSI_APP_ROOT.'/templates/'.$this->_bootstrap_template."_bootstrap.css")) {
            $this->_bootstrap_template = 'phpsysinfo';
        }
        $this->_pick_template = !defined("PSI_SHOW_PICKLIST_TEMPLATE") || (PSI_SHOW_PICKLIST_TEMPLATE !== false);

        if (!defined("PSI_DEFAULT_LANG") || (($this->_language = strtolower(trim(PSI_DEFAULT_LANG))) == "") || !file_exists(PSI_APP_ROOT.'/language/'.$this->_language.".xml")) {
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
        $dirlist = CommonFunctions::gdc(PSI_APP_ROOT.'/templates/');
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
        $dirlist = CommonFunctions::gdc(PSI_APP_ROOT.'/language/');
        sort($dirlist);
        foreach ($dirlist as $file) {
            $lang_ext = strtolower(substr($file, strlen($file) - 4));
            $lang_name = strtolower(substr($file, 0, strlen($file) - 4));
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
        $tpl->set("showCPUListExpanded", defined('PSI_SHOW_CPULIST_EXPANDED') ? (PSI_SHOW_CPULIST_EXPANDED ? 'true' : 'false') : 'true');
        $tpl->set("showCPUInfoExpanded", defined('PSI_SHOW_CPUINFO_EXPANDED') ? (PSI_SHOW_CPUINFO_EXPANDED ? 'true' : 'false') : 'false');
        $tpl->set("showNetworkInfosExpanded", defined('PSI_SHOW_NETWORK_INFOS_EXPANDED') ? (PSI_SHOW_NETWORK_INFOS_EXPANDED ? 'true' : 'false') : 'false');
        $tpl->set("showMemoryInfosExpanded", defined('PSI_SHOW_MEMORY_INFOS_EXPANDED') ? (PSI_SHOW_MEMORY_INFOS_EXPANDED ? 'true' : 'false') : 'false');
        $tpl->set("showNetworkActiveSpeed", defined('PSI_SHOW_NETWORK_ACTIVE_SPEED') ? (PSI_SHOW_NETWORK_ACTIVE_SPEED ? ((strtolower(PSI_SHOW_NETWORK_ACTIVE_SPEED) === 'bps') ? 'bps' :'true') : 'false') : 'false');
        $tpl->set("showCPULoadCompact", defined('PSI_LOAD_BAR') ? ((strtolower(PSI_LOAD_BAR) === 'compact') ? 'true' :'false') : 'false');
        $tpl->set("hideBootstrapLoader", defined('PSI_HIDE_BOOTSTRAP_LOADER') ? (PSI_HIDE_BOOTSTRAP_LOADER ? 'true' : 'false') : 'false');
        if (defined('PSI_BLOCKS')) {
            if (is_string(PSI_BLOCKS)) {
                if (preg_match(ARRAY_EXP, PSI_BLOCKS)) {
                    $blocks = eval(strtolower(PSI_BLOCKS));
                } else {
                    $blocks = array(strtolower(PSI_BLOCKS));
                }
                $blocklist = '';
                $validblocks = array('vitals','hardware','memory','filesystem','network','voltage','current','temperature','fans','power','other','ups');
                foreach ($blocks as $block) {
                    if (in_array($block, $validblocks)) {
                        if (empty($blocklist)) {
                            $blocklist = $block;
                        } else {
                            $blocklist .= ','.$block;
                        }
                    }
                }
                if (!empty($blocklist)) {
                    $tpl->set("blocks", $blocklist);
                }
            } elseif (PSI_BLOCKS) {
                $tpl->set("blocks", 'true');
            }
        } else {
            $tpl->set("blocks", 'true');
        }

        echo $tpl->fetch();
    }
}
