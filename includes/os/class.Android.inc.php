<?php
/**
 * Android System Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI Android OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.Linux.inc.php 712 2012-12-05 14:09:18Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * Android sysinfo class
 * get all the required information from Android system
 *
 * @category  PHP
 * @package   PSI Android OS class
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Android extends Linux
{
    /**
     * call parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Kernel Version
     *
     * @return void
     */
    private function _kernel()
    {
        if (CommonFunctions::rfts('/proc/version', $strBuf, 1)) {
            if (preg_match('/version (.*?) /', $strBuf, $ar_buf)) {
                $result = $ar_buf[1];
                if (preg_match('/SMP/', $strBuf)) {
                    $result .= ' (SMP)';
                }
                $this->sys->setKernel($result);
            }
        }
    }

    /**
     * Number of Users
     *
     * @return void
     */
    private function _users()
    {
        $this->sys->setUsers(1);
    }

    /**
     * filesystem information
     *
     * @return void
     */
    private function _filesystems()
    {
        if (CommonFunctions::executeProgram('df', '2>/dev/null ', $df, PSI_DEBUG)) {
            $df = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
            if (CommonFunctions::executeProgram('mount', '', $mount, PSI_DEBUG)) {
                $mount = preg_split("/\n/", $mount, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($mount as $mount_line) {
                    $mount_buf = preg_split('/\s+/', $mount_line);
                    if (count($mount_buf) == 6) {
                        $mount_parm[$mount_buf[1]]['fstype'] = $mount_buf[2];
                        if (PSI_SHOW_MOUNT_OPTION) $mount_parm[$mount_buf[1]]['options'] = $mount_buf[3];
                        $mount_parm[$mount_buf[1]]['mountdev'] = $mount_buf[0];
                    }
                }
                foreach ($df as $df_line) {
                    if ((preg_match("/^(\/\S+)(\s+)(([0-9\.]+)([KMGT])(\s+)([0-9\.]+)([KMGT])(\s+)([0-9\.]+)([KMGT])(\s+))/", $df_line, $df_buf)
                         || preg_match("/^(\/[^\s\:]+)\:(\s+)(([0-9\.]+)([KMGT])(\s+total\,\s+)([0-9\.]+)([KMGT])(\s+used\,\s+)([0-9\.]+)([KMGT])(\s+available))/", $df_line, $df_buf))
                         && !preg_match('/^\/mnt\/asec\/com\./', $df_buf[1])) {
                        $dev = new DiskDevice();
                        if (PSI_SHOW_MOUNT_POINT) $dev->setMountPoint($df_buf[1]);

                        if ($df_buf[5] == 'K') $dev->setTotal($df_buf[4] * 1024);
                        elseif ($df_buf[5] == 'M') $dev->setTotal($df_buf[4] * 1024*1024);
                        elseif ($df_buf[5] == 'G') $dev->setTotal($df_buf[4] * 1024*1024*1024);
                        elseif ($df_buf[5] == 'T') $dev->setTotal($df_buf[4] * 1024*1024*1024*1024);

                        if ($df_buf[8] == 'K') $dev->setUsed($df_buf[7] * 1024);
                        elseif ($df_buf[8] == 'M') $dev->setUsed($df_buf[7] * 1024*1024);
                        elseif ($df_buf[8] == 'G') $dev->setUsed($df_buf[7] * 1024*1024*1024);
                        elseif ($df_buf[8] == 'T') $dev->setUsed($df_buf[7] * 1024*1024*1024*1024);

                        if ($df_buf[11] == 'K') $dev->setFree($df_buf[10] * 1024);
                        elseif ($df_buf[11] == 'M') $dev->setFree($df_buf[10] * 1024*1024);
                        elseif ($df_buf[11] == 'G') $dev->setFree($df_buf[10] * 1024*1024*1024);
                        elseif ($df_buf[11] == 'T') $dev->setFree($df_buf[10] * 1024*1024*1024*1024);

                        if (isset($mount_parm[$df_buf[1]])) {
                            $dev->setFsType($mount_parm[$df_buf[1]]['fstype']);
                            $dev->setName($mount_parm[$df_buf[1]]['mountdev']);

                            if (PSI_SHOW_MOUNT_OPTION) {
                                if (PSI_SHOW_MOUNT_CREDENTIALS) {
                                    $dev->setOptions($mount_parm[$df_buf[1]]['options']);
                                } else {
                                    $mpo=$mount_parm[$df_buf[1]]['options'];

                                    $mpo=preg_replace('/(^guest,)|(^guest$)|(,guest$)/i', '', $mpo);
                                    $mpo=preg_replace('/,guest,/i', ',', $mpo);

                                    $mpo=preg_replace('/(^user=[^,]*,)|(^user=[^,]*$)|(,user=[^,]*$)/i', '', $mpo);
                                    $mpo=preg_replace('/,user=[^,]*,/i', ',', $mpo);

                                    $mpo=preg_replace('/(^username=[^,]*,)|(^username=[^,]*$)|(,username=[^,]*$)/i', '', $mpo);
                                    $mpo=preg_replace('/,username=[^,]*,/i', ',', $mpo);

                                    $mpo=preg_replace('/(^password=[^,]*,)|(^password=[^,]*$)|(,password=[^,]*$)/i', '', $mpo);
                                    $mpo=preg_replace('/,password=[^,]*,/i', ',', $mpo);

                                    $dev->setOptions($mpo);
                                }
                            }
                        }
                        $this->sys->setDiskDevices($dev);
                    }
                }
            }
        }
    }

    /**
     * Distribution
     *
     * @return void
     */
    private function _distro()
    {
        $buf = "";
        if (CommonFunctions::rfts('/system/build.prop', $lines, 0, 4096, false)
            && preg_match('/^ro\.build\.version\.release=([^\n]+)/m', $lines, $ar_buf)) {
                $buf = trim($ar_buf[1]);
        }
        if (is_null($buf) || ($buf == "")) {
            $this->sys->setDistribution('Android');
        } else {
            if (preg_match('/^(\d+\.\d+)/', $buf, $ver)
                && ($list = @parse_ini_file(APP_ROOT."/data/osnames.ini", true))
                && isset($list['Android'][$ver[1]])) {
                    $buf.=' '.$list['Android'][$ver[1]];
            }
            $this->sys->setDistribution('Android '.$buf);
        }
        $this->sys->setDistributionIcon('Android.png');
    }

    /**
     * Machine
     *
     * @return void
     */
    private function _machine()
    {
        if (CommonFunctions::rfts('/system/build.prop', $lines, 0, 4096, false)) {
            $buf = "";
            if (preg_match('/^ro\.product\.manufacturer=([^\n]+)/m', $lines, $ar_buf)) {
                $buf .= ' '.trim($ar_buf[1]);
            }
            if (preg_match('/^ro\.product\.model=([^\n]+)/m', $lines, $ar_buf) && (trim($buf) !== trim($ar_buf[1]))) {
                $buf .= ' '.trim($ar_buf[1]);
            }
            if (preg_match('/^ro\.semc\.product\.name=([^\n]+)/m', $lines, $ar_buf)) {
                $buf .= ' '.trim($ar_buf[1]);
            }
            if (trim($buf) != "") {
                $this->sys->setMachine(trim($buf));
            }
        }
    }

    /**
     * PCI devices
     *
     * @return array
     */
    private function _pci()
    {
        if (CommonFunctions::executeProgram('lspci', '', $bufr, false)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                $device = preg_split("/ /", $buf, 4);
                if (isset($device[3]) && trim($device[3]) != "") {
                    $dev = new HWDevice();
                    $dev->setName('Class '.trim($device[2]).' Device '.trim($device[3]));
                    $this->sys->setPciDevices($dev);
                }
            }
        }
    }

    /**
     * USB devices
     *
     * @return array
     */
    private function _usb()
    {
        if (file_exists('/dev/bus/usb') && CommonFunctions::executeProgram('lsusb', '', $bufr, false)) {
            $bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bufe as $buf) {
                $device = preg_split("/ /", $buf, 6);
                if (isset($device[5]) && trim($device[5]) != "") {
                    $dev = new HWDevice();
                    $dev->setName(trim($device[5]));
                    $this->sys->setUsbDevices($dev);
                }
            }
        }
    }

    /**
     * get the information
     *
     * @see PSI_Interface_OS::build()
     *
     * @return Void
     */
    public function build()
    {
        $this->_distro();
        $this->_hostname();
        $this->_kernel();
        $this->_machine();
        $this->_uptime();
        $this->_users();
        $this->_cpuinfo();
        $this->_pci();
        $this->_usb();
        $this->_i2c();
        $this->_network();
        $this->_memory();
        $this->_filesystems();
        $this->_loadavg();
        $this->_processes();
    }
}
