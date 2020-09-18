<?php
/**
 * parser Class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.Parser.inc.php 604 2012-07-10 07:31:34Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * parser class with common used parsing metods
 *
 * @category  PHP
 * @package   PSI
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class Parser
{
    /**
     * parsing the output of lspci command
     *
     * @param  bool  $debug
     * @return array
     */
    public static function lspci($debug = PSI_DEBUG)
    {
        $arrResults = array();
        if (CommonFunctions::executeProgram("lspci", "", $strBuf, $debug)) {
            $arrLines = preg_split("/\n/", $strBuf, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($arrLines as $strLine) {
                $arrParams = preg_split('/ /', trim($strLine), 2);
                if (count($arrParams) == 2)
                   $strName = $arrParams[1];
                else
                   $strName = "unknown";
                $strName = preg_replace('/\(.*\)/', '', $strName);
                $dev = new HWDevice();
                $dev->setName($strName);
                $arrResults[] = $dev;
            }
        }

        return $arrResults;
    }

    /**
     * parsing the output of df command
     *
     * @param string $df_param   additional parameter for df command
     * @param bool   $get_inodes
     *
     * @return array
     */
    public static function df($df_param = "", $get_inodes = true)
    {
        $arrResult = array();
        if (CommonFunctions::executeProgram('mount', '', $mount, PSI_DEBUG)) {
            $mount = preg_split("/\n/", $mount, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($mount as $mount_line) {
                if (preg_match("/(\S+) on ([\S ]+) type (.*) \((.*)\)/", $mount_line, $mount_buf)) {
                    $parm = array();
                    $parm['mountpoint'] = trim($mount_buf[2]);
                    $parm['fstype'] = $mount_buf[3];
                    $parm['name'] = $mount_buf[1];
                    if (PSI_SHOW_MOUNT_OPTION) $parm['options'] = $mount_buf[4];
                    $mount_parm[] = $parm;
                } elseif (preg_match("/(\S+) is (.*) mounted on (\S+) \(type (.*)\)/", $mount_line, $mount_buf)) {
                    $parm = array();
                    $parm['mountpoint'] = trim($mount_buf[3]);
                    $parm['fstype'] = $mount_buf[4];
                    $parm['name'] = $mount_buf[1];
                    if (PSI_SHOW_MOUNT_OPTION) $parm['options'] = $mount_buf[2];
                    $mount_parm[] = $parm;
                } elseif (preg_match("/(\S+) (.*) on (\S+) \((.*)\)/", $mount_line, $mount_buf)) {
                    $parm = array();
                    $parm['mountpoint'] = trim($mount_buf[3]);
                    $parm['fstype'] = $mount_buf[2];
                    $parm['name'] = $mount_buf[1];
                    if (PSI_SHOW_MOUNT_OPTION) $parm['options'] = $mount_buf[4];
                    $mount_parm[] = $parm;
                } elseif (preg_match("/(\S+) on ([\S ]+) \((\S+)(,\s(.*))?\)/", $mount_line, $mount_buf)) {
                    $parm = array();
                    $parm['mountpoint'] = trim($mount_buf[2]);
                    $parm['fstype'] = $mount_buf[3];
                    $parm['name'] = $mount_buf[1];
                    if (PSI_SHOW_MOUNT_OPTION) $parm['options'] = isset($mount_buf[5]) ? $mount_buf[5] : '';
                    $mount_parm[] = $parm;
                }
            }
        } elseif (CommonFunctions::rfts("/etc/mtab", $mount)) {
            $mount = preg_split("/\n/", $mount, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($mount as $mount_line) {
                if (preg_match("/(\S+) (\S+) (\S+) (\S+) ([0-9]+) ([0-9]+)/", $mount_line, $mount_buf)) {
                    $parm = array();
                    $mount_point = preg_replace("/\\\\040/i", ' ', $mount_buf[2]); //space as \040
                    $parm['mountpoint'] = $mount_point;
                    $parm['fstype'] = $mount_buf[3];
                    $parm['name'] = $mount_buf[1];
                    if (PSI_SHOW_MOUNT_OPTION) $parm['options'] = $mount_buf[4];
                    $mount_parm[] = $parm;
                }
            }
        }
        if (CommonFunctions::executeProgram('df', '-k '.$df_param, $df, PSI_DEBUG) && ($df!=="")) {
            $df = preg_split("/\n/", $df, -1, PREG_SPLIT_NO_EMPTY);
            if ($get_inodes && PSI_SHOW_INODES) {
                if (CommonFunctions::executeProgram('df', '-i '.$df_param, $df2, PSI_DEBUG)) {
                    $df2 = preg_split("/\n/", $df2, -1, PREG_SPLIT_NO_EMPTY);
                    // Store inode use% in an associative array (df_inodes) for later use
                    foreach ($df2 as $df2_line) {
                        if (preg_match("/^(\S+).*\s([0-9]+)%/", $df2_line, $inode_buf)) {
                            $df_inodes[$inode_buf[1]] = $inode_buf[2];
                        }
                    }
                }
            }
            foreach ($df as $df_line) {
                $df_buf1 = preg_split("/(\%\s)/", $df_line, 3);
                if (count($df_buf1) < 2) {
                    continue;
                }
                if (preg_match("/(.*)(\s+)(([0-9]+)(\s+)([0-9]+)(\s+)([\-0-9]+)(\s+)([0-9]+)$)/", $df_buf1[0], $df_buf2)) {
                    if (count($df_buf1) == 3) {
                        $df_buf = array($df_buf2[1], $df_buf2[4], $df_buf2[6], $df_buf2[8], $df_buf2[10], $df_buf1[2]);
                    } else {
                        $df_buf = array($df_buf2[1], $df_buf2[4], $df_buf2[6], $df_buf2[8], $df_buf2[10], $df_buf1[1]);
                    }
                    if (count($df_buf) == 6) {
                        $df_buf[5] = trim($df_buf[5]);
                        $dev = new DiskDevice();
                        $dev->setName(trim($df_buf[0]));
                        if ($df_buf[2] < 0) {
                            $dev->setTotal($df_buf[3] * 1024);
                            $dev->setUsed($df_buf[3] * 1024);
                        } else {
                            $dev->setTotal($df_buf[1] * 1024);
                            $dev->setUsed($df_buf[2] * 1024);
                            if ($df_buf[3]>0) {
                                $dev->setFree($df_buf[3] * 1024);
                            }
                        }
                        if (PSI_SHOW_MOUNT_POINT) $dev->setMountPoint($df_buf[5]);

                        $notwas = true;
                        if (isset($mount_parm)) {
                            foreach ($mount_parm as $mount_param) { //name and mountpoint find
                                if (($mount_param['name']===trim($df_buf[0])) && ($mount_param['mountpoint']===$df_buf[5])) {
                                    $dev->setFsType($mount_param['fstype']);
                                    if (PSI_SHOW_MOUNT_OPTION && (trim($mount_param['options'])!=="")) {
                                        if (PSI_SHOW_MOUNT_CREDENTIALS) {
                                            $dev->setOptions($mount_param['options']);
                                        } else {
                                            $mpo=$mount_param['options'];

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
                                    $notwas = false;
                                    break;
                                }
                            }
                            if ($notwas) foreach ($mount_parm as $mount_param) { //mountpoint find
                                if ($mount_param['mountpoint']===$df_buf[5]) {
                                    $dev->setFsType($mount_param['fstype']);
                                    if (PSI_SHOW_MOUNT_OPTION && (trim($mount_param['options'])!=="")) {
                                        if (PSI_SHOW_MOUNT_CREDENTIALS) {
                                            $dev->setOptions($mount_param['options']);
                                        } else {
                                            $mpo=$mount_param['options'];

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
                                    $notwas = false;
                                    break;
                                }
                            }
                        }

                        if ($notwas) {
                            $dev->setFsType('unknown');
                        }

                        if ($get_inodes && PSI_SHOW_INODES && isset($df_inodes[trim($df_buf[0])])) {
                            $dev->setPercentInodesUsed($df_inodes[trim($df_buf[0])]);
                        }
                        $arrResult[] = $dev;
                    }
                }
            }
        } else {
            if (isset($mount_parm)) {
                foreach ($mount_parm as $mount_param) {
                    if (is_dir($mount_param['mountpoint'])) {
                        $total = disk_total_space($mount_param['mountpoint']);
                        if (($mount_param['fstype'] != 'none') && ($total > 0)) {
                            $dev = new DiskDevice();
                            $dev->setName($mount_param['name']);
                            $dev->setFsType($mount_param['fstype']);

                            if (PSI_SHOW_MOUNT_POINT) $dev->setMountPoint($mount_param['mountpoint']);

                            $dev->setTotal($total);
                            $free = disk_free_space($mount_param['mountpoint']);
                            if ($free > 0) {
                                $dev->setFree($free);
                            } else {
                                $free = 0;
                            }
                            if ($total > $free) $dev->setUsed($total - $free);

                            if (PSI_SHOW_MOUNT_OPTION) {
                                if (PSI_SHOW_MOUNT_CREDENTIALS) {
                                    $dev->setOptions($mount_param['options']);
                                } else {
                                    $mpo=$mount_param['options'];

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
                            $arrResult[] = $dev;
                        }
                    }
                }
            }
        }

        return $arrResult;
    }
}
