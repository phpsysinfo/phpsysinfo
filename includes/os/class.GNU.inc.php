<?php
/**
 * GNU Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI GNU class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2012 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.GNU.inc.php 687 2012-09-06 20:54:49Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * GNU sysinfo class
 * get all the required information from GNU
 *
 * @category  PHP
 * @package   PSI GNU class
 * @author    Mieczyslaw Nalewaj <namiltd@users.sourceforge.net>
 * @copyright 2022 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class GNU extends Linux
{
    /**
     * Network devices
     * includes also rx/tx bytes
     *
     * @return void
     */
    protected function _network($bufr = null)
    {
        if ($this->sys->getOS() == 'GNU') {
            if (CommonFunctions::executeProgram('ifconfig', '-a', $bufr, PSI_DEBUG)) {
                $lines = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
                $was = false;
                $macaddr = "";
                $dev = null;
                foreach ($lines as $line) {
                    if (preg_match("/^\/dev\/([^\s:]+)/", $line, $ar_buf) || preg_match("/^([^\s:]+)/", $line, $ar_buf)) {
                        if ($was) {
                            if ($macaddr != "") {
                                $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                            }
                            $this->sys->setNetDevices($dev);
                        }
                        $macaddr = "";
                        $dev = new NetDevice();
                        $dev->setName($ar_buf[1]);
                        $was = true;
                    } else {
                        if ($was) {
                            if (defined('PSI_SHOW_NETWORK_INFOS') && (PSI_SHOW_NETWORK_INFOS)) {
                                if (preg_match('/^\s+inet address\s+(\S+)$/', $line, $ar_buf)) {
                                    $dev->setInfo($ar_buf[1]);
                                } elseif (preg_match('/^\s+hardware addr\s+(\S+)$/', $line, $ar_buf)) {
                                    if (!defined('PSI_HIDE_NETWORK_MACADDR') || !PSI_HIDE_NETWORK_MACADDR) {
                                        $macaddr = preg_replace('/:/', '-', strtoupper($ar_buf[1]));
                                        if ($macaddr === '00-00-00-00-00-00') { // empty
                                            $macaddr = "";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($was) {
                    if ($macaddr != "") {
                        $dev->setInfo($macaddr.($dev->getInfo()?';'.$dev->getInfo():''));
                    }
                    $this->sys->setNetDevices($dev);
                }
            }
        } else {
            parent::_network($bufr);
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    protected function _users()
    {
        if ($this->sys->getOS() == 'GNU') {
            if (CommonFunctions::executeProgram('who', '', $strBuf, PSI_DEBUG)) {
                if (strlen($strBuf) > 0) {
                    $lines = preg_split('/\n/', $strBuf);
                    preg_match_all('/^login\s+/m', $strBuf, $ttybuf);
                    if (($who = count($lines)-count($ttybuf[0])) > 0) {
                        $this->sys->setUsers($who);
                    }
                }
            }
        } else {
            parent::_users();
        }
    }

     /**
     * get the information
     *
     * @return void
     */
    public function build()
    {
        if ($this->sys->getOS() == 'GNU') {
            $this->error->addWarning("The GNU Hurd version of phpSysInfo is a work in progress, some things currently don't work");
        }
        parent::build();
    }
}
