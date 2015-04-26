<?php
/**
 * PS Plugin
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_Plugin_PS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.ps.inc.php 692 2012-09-08 17:12:08Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * process Plugin, which displays all running processes
 * a simple tree view which is filled with the running processes which are determined by
 * calling the "ps" command line utility, another way is to provide
 * a file with the output of the ps utility, so there is no need to run a execute by the
 * webserver, the format of the command is written down in the phpsysinfo.ini file, where also
 * the method of getting the information is configured
 *
 * @category  PHP
 * @package   PSI_Plugin_PS
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class PS extends PSI_Plugin
{
    /**
     * variable, which holds the content of the command
     * @var array
     */
    private $_filecontent = array();
    /**
     * variable, which holds the result before the xml is generated out of this array
     * @var array
     */
    private $_result = array();
    /**
     * read the data into an internal array and also call the parent constructor
     *
     * @param String $enc encoding
     */
    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        switch (strtolower(PSI_PLUGIN_PS_ACCESS)) {
        case 'command':
            if (PSI_OS == 'WINNT') {
                try {
                    $objLocator = new COM("WbemScripting.SWbemLocator");
                    $wmi = $objLocator->ConnectServer();
                    $os_wmi = $wmi->InstancesOf('Win32_OperatingSystem');
                    foreach ($os_wmi as $os) {
                        $memtotal = $os->TotalVisibleMemorySize * 1024;
                    }
                    $process_wmi = $wmi->InstancesOf('Win32_Process');
                    foreach ($process_wmi as $process) {
                        if (strlen(trim($process->CommandLine)) > 0) {
                            $ps = trim($process->CommandLine);
                        } else {
                            $ps = trim($process->Caption);
                        }
                        if (trim($process->ProcessId) != 0) {
                            $memusage = round(trim($process->WorkingSetSize) * 100 / $memtotal, 1);
                            //ParentProcessId
                            //Unique identifier of the process that creates a process. Process identifier numbers are reused, so they
                            //only identify a process for the lifetime of that process. It is possible that the process identified by
                            //ParentProcessId is terminated, so ParentProcessId may not refer to a running process. It is also
                            //possible that ParentProcessId incorrectly refers to a process that reuses a process identifier. You can
                            //use the CreationDate property to determine whether the specified parent was created after the process
                            //represented by this Win32_Process instance was created.
                            //=> subtrees of processes may be missing (WHAT TODO?!?)
                            $this->_filecontent[] = trim($process->ProcessId)." ".trim($process->ParentProcessId)." ".$memusage." ".$ps;
                        }
                    }
                } catch (Exception $e) {
                }
            } else {
                CommonFunctions::executeProgram("ps", "axo pid,ppid,pmem,args", $buffer, PSI_DEBUG);
                if (((PSI_OS == 'Linux') || (PSI_OS == 'Android')) && (!preg_match("/^[^\n]+\n.+/", $buffer))) { //alternative method if no data
                    if (CommonFunctions::rfts('/proc/meminfo', $mbuf)) {
                        $bufe = preg_split("/\n/", $mbuf, -1, PREG_SPLIT_NO_EMPTY);
                        $totalmem = 0;
                        foreach ($bufe as $buf) {
                            if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
                                $totalmem = $ar_buf[1];
                                break;
                            }
                        }
                        $buffer = "  PID  PPID %MEM COMMAND\n";

                        $processlist = glob('/proc/*/status', GLOB_NOSORT);
                        if (($total = count($processlist)) > 0) {
                            natsort($processlist); //first sort
                            $prosess = array();
                            foreach ($processlist as $processitem) { //second sort
                                $process[] = $processitem;
                            }

                            $buf = "";
                            for ($i = 0; $i < $total; $i++) {
                                if (CommonFunctions::rfts($process[$i], $buf, 0, 4096, false)) {

                                    if (($totalmem != 0) && (preg_match('/^VmRSS:\s+(\d+)\s+kB/m', $buf, $tmppmem))) {
                                        $pmem = round(100 * $tmppmem[1] / $totalmem, 1);
                                    } else {
                                        $pmem = 0;
                                    }

                                    $name = null;
                                    if (CommonFunctions::rfts(substr($process[$i], 0, strlen($process[$i])-6)."cmdline", $namebuf, 0, 4096, false)) {
                                        $name = str_replace(chr(0), ' ', trim($namebuf));
                                    }
                                    if (preg_match('/^Pid:\s+(\d+)/m', $buf, $tmppid) &&
                                        preg_match('/^PPid:\s+(\d+)/m', $buf, $tmpppid) &&
                                        preg_match('/^Name:\s+(.+)/m', $buf, $tmpargs)) {
                                        $pid = $tmppid[1];
                                        $ppid = $tmpppid[1];
                                        $args = $tmpargs[1];
                                        if ($name !== null) {
                                            if ($name !== "") {
                                                $args = $name;
                                            } else {
                                                $args = "[".$args."]";
                                            }
                                        }
                                        $buffer .= $pid." ".$ppid." ".$pmem." ".$args."\n";
                                    }

                                }
                            }
                        }
                    }
                }
            }
            break;
        case 'data':
            CommonFunctions::rfts(APP_ROOT."/data/ps.txt", $buffer);
            break;
        default:
            $this->global_error->addConfigError("__construct()", "PSI_PLUGIN_PS_ACCESS");
            break;
        }
        if (PSI_OS != 'WINNT') {
            if (trim($buffer) != "") {
                $this->_filecontent = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
                unset($this->_filecontent[0]);
            } else {
                $this->_filecontent = array();
            }
        }
    }
    /**
     * doing all tasks to get the required informations that the plugin needs
     * result is stored in an internal array<br>the array is build like a tree,
     * so that it is possible to get only a specific process with the childs
     *
     * @return void
     */
    public function execute()
    {
        if (empty($this->_filecontent)) {
            return;
        }
        foreach ($this->_filecontent as $roworig) {
            $row = preg_split("/[\s]+/", trim($roworig), 4);
            if (count($row) != 4) {
                break;
            }
            foreach ($row as $key=>$val) {
                $items[$row[0]][$key] = $val;
            }
            if ($row[1] !== $row[0]) {
                $items[$row[1]]['childs'][$row[0]] = &$items[$row[0]];
            }
        }
        foreach ($items as $item) { //find zombie
            if (!isset($item[0])) {
                foreach ($item["childs"] as $subitem) {
                    $zombie = $subitem[1];
                    if ($zombie != 0) {
                        $items[$zombie]["0"] = $zombie;
                        $items[$zombie]["1"] = "0";
                        $items[$zombie]["2"] = "0";
                        $items[$zombie]["3"] = "unknown";
                        $items[0]['childs'][$zombie] = &$items[$zombie];
                    }
                    break; //first is sufficient
                }
            }
        }
        if (isset($items[0])) {
            $this->_result = $items[0];
        } else {
            $_result = array();
        }
    }
    /**
     * generates the XML content for the plugin
     *
     * @return SimpleXMLElement entire XML content for the plugin
     */
    public function xml()
    {
        if ($this->_result) {
            $positions = array(0=>0);
            $this->_addchild($this->_result['childs'], $this->xml, $positions);
        }

        return $this->xml->getSimpleXmlElement();
    }
    /**
     * recursive function to allow appending child processes to a parent process
     *
     * @param Array             $child      part of the array which should be appended to the XML
     * @param SimpleXMLExtended $xml        XML-Object to which the array content is appended
     * @param Array             &$positions array with parent positions in xml structure
     *
     * @return SimpleXMLExtended Object with the appended array content
     */
    private function _addchild($child, SimpleXMLExtended $xml, &$positions)
    {
        foreach ($child as $key=>$value) {
            $xmlnode = $xml->addChild("Process");
            if (isset($value[0])) {
                array_push($positions, $value[0]);
                $xmlnode->addAttribute('PID', $value[0]);
                $parentid = array_search($value[1], $positions);
                $xmlnode->addAttribute('ParentID', $parentid);
                $xmlnode->addAttribute('PPID', $value[1]);
                $xmlnode->addAttribute('MemoryUsage', $value[2]);
                $xmlnode->addAttribute('Name', $value[3]);
                if (PSI_OS !== 'WINNT') {
                    if ($parentid === 1){
                        $xmlnode->addAttribute('Expanded', 0);
                    }
                    if (defined('PSI_PLUGIN_PS_SHOW_KTHREADD_EXPANDED') && (PSI_PLUGIN_PS_SHOW_KTHREADD_EXPANDED === false) && ($value[3] === "[kthreadd]")) {
                        $xmlnode->addAttribute('Expanded', 0);
                    }
                }
            }
            if (isset($value['childs'])) {
                $this->_addChild($value['childs'], $xml, $positions);
            }
        }

        return $xml;
    }
}
