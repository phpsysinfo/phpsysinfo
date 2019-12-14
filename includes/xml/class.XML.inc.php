<?php
/**
 * XML Generation class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   SVN: $Id: class.XML.inc.php 699 2012-09-15 11:57:13Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for generation of the xml
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class XML
{
    /**
     * Sysinfo object where the information retrieval methods are included
     *
     * @var PSI_Interface_OS
     */
    private $_sysinfo;

    /**
     * @var System
     */
    private $_sys = null;

    /**
     * xml object with the xml content
     *
     * @var SimpleXMLExtended
     */
    private $_xml;

    /**
     * object for error handling
     *
     * @var PSI_Error
     */
    private $_errors;

    /**
     * array with all enabled plugins (name)
     *
     * @var array
     */
    private $_plugins;

    /**
     * plugin name if pluginrequest
     *
     * @var string
     */
    private $_plugin = '';

    /**
     * generate a xml for a plugin or for the main app
     *
     * @var boolean
     */
    private $_plugin_request = false;

    /**
     * generate the entire xml with all plugins or only a part of the xml (main or plugin)
     *
     * @var boolean
     */
    private $_complete_request = false;

    /**
     * doing some initial tasks
     * - generate the xml structure with the right header elements
     * - get the error object for error output
     * - get a instance of the sysinfo object
     *
     * @param boolean $complete   generate xml with all plugins or not
     * @param string  $pluginname name of the plugin
     *
     * @return void
     */
    public function __construct($complete = false, $pluginname = "", $blockname = false)
    {
        $this->_errors = PSI_Error::singleton();
        if ($pluginname == "") {
            $this->_plugin_request = false;
            $this->_plugin = '';
        } else {
            $this->_plugin_request = true;
            $this->_plugin = $pluginname;
        }
        if ($complete) {
            $this->_complete_request = true;
        } else {
            $this->_complete_request = false;
        }
        $os = PSI_OS;
        $this->_sysinfo = new $os($blockname);
        $this->_plugins = CommonFunctions::getPlugins();
        $this->_xmlbody();
    }

    /**
     * generate common information
     *
     * @return void
     */
    private function _buildVitals()
    {
        $vitals = $this->_xml->addChild('Vitals');
        $vitals->addAttribute('Hostname', $this->_sys->getHostname());
        $vitals->addAttribute('IPAddr', $this->_sys->getIp());
        $vitals->addAttribute('Kernel', $this->_sys->getKernel());
        $vitals->addAttribute('Distro', $this->_sys->getDistribution());
        $vitals->addAttribute('Distroicon', $this->_sys->getDistributionIcon());
        $vitals->addAttribute('Uptime', $this->_sys->getUptime());
        $vitals->addAttribute('Users', $this->_sys->getUsers());
        $vitals->addAttribute('LoadAvg', $this->_sys->getLoad());
        if ($this->_sys->getLoadPercent() !== null) {
            $vitals->addAttribute('CPULoad', $this->_sys->getLoadPercent());
        }
        if ($this->_sysinfo->getLanguage() !== null) {
            $vitals->addAttribute('SysLang', $this->_sysinfo->getLanguage());
        }
        if ($this->_sysinfo->getEncoding() !== null) {
            $vitals->addAttribute('CodePage', $this->_sysinfo->getEncoding());
        }

        //processes
        if (($procss = $this->_sys->getProcesses()) !== null) {
            if (isset($procss['*']) && (($procall = $procss['*']) > 0)) {
                $vitals->addAttribute('Processes', $procall);
                if (!isset($procss[' ']) || !($procss[' '] > 0)) { // not unknown
                    $procsum = 0;
                    if (isset($procss['R']) && (($proctmp = $procss['R']) > 0)) {
                        $vitals->addAttribute('ProcessesRunning', $proctmp);
                        $procsum += $proctmp;
                    }
                    if (isset($procss['S']) && (($proctmp = $procss['S']) > 0)) {
                        $vitals->addAttribute('ProcessesSleeping', $proctmp);
                        $procsum += $proctmp;
                    }
                    if (isset($procss['T']) && (($proctmp = $procss['T']) > 0)) {
                        $vitals->addAttribute('ProcessesStopped', $proctmp);
                        $procsum += $proctmp;
                    }
                    if (isset($procss['Z']) && (($proctmp = $procss['Z']) > 0)) {
                        $vitals->addAttribute('ProcessesZombie', $proctmp);
                        $procsum += $proctmp;
                    }
                    if (isset($procss['D']) && (($proctmp = $procss['D']) > 0)) {
                        $vitals->addAttribute('ProcessesWaiting', $proctmp);
                        $procsum += $proctmp;
                    }
                    if (($proctmp = $procall - $procsum) > 0) {
                        $vitals->addAttribute('ProcessesOther', $proctmp);
                    }
                }
            }
        }
//        $vitals->addAttribute('OS', PSI_OS);
        $vitals->addAttribute('OS', (PSI_OS=='Android')?'Linux':PSI_OS);
    }

    /**
     * generate the network information
     *
     * @return void
     */
    private function _buildNetwork()
    {
        $hideDevices = array();
        $network = $this->_xml->addChild('Network');
        if (defined('PSI_HIDE_NETWORK_INTERFACE')) {
            if (is_string(PSI_HIDE_NETWORK_INTERFACE)) {
                if (preg_match(ARRAY_EXP, PSI_HIDE_NETWORK_INTERFACE)) {
                    $hideDevices = eval(PSI_HIDE_NETWORK_INTERFACE);
                } else {
                    $hideDevices = array(PSI_HIDE_NETWORK_INTERFACE);
                }
            } elseif (PSI_HIDE_NETWORK_INTERFACE === true) {
                return;
            }
        }
        foreach ($this->_sys->getNetDevices() as $dev) {
            if (!in_array(trim($dev->getName()), $hideDevices)) {
                $device = $network->addChild('NetDevice');
                $device->addAttribute('Name', $dev->getName());
                $device->addAttribute('RxBytes', $dev->getRxBytes());
                $device->addAttribute('TxBytes', $dev->getTxBytes());
                $device->addAttribute('Err', $dev->getErrors());
                $device->addAttribute('Drops', $dev->getDrops());
                if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS && $dev->getInfo())
                    $device->addAttribute('Info', $dev->getInfo());
            }
        }
    }

    /**
     * generate the hardware information
     *
     * @return void
     */
    private function _buildHardware()
    {
        $hardware = $this->_xml->addChild('Hardware');
        if ($this->_sys->getMachine() != "") {
            $hardware->addAttribute('Name', $this->_sys->getMachine());
        }
        $pci = null;
        foreach (System::removeDupsAndCount($this->_sys->getPciDevices()) as $dev) {
            if ($pci === null) $pci = $hardware->addChild('PCI');
            $tmp = $pci->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                if ($dev->getManufacturer() !== null) {
                    $tmp->addAttribute('Manufacturer', $dev->getManufacturer());
                }
                if ($dev->getProduct() !== null) {
                    $tmp->addAttribute('Product', $dev->getProduct());
                }
            }
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $usb = null;
        foreach (System::removeDupsAndCount($this->_sys->getUsbDevices()) as $dev) {
            if ($usb === null) $usb = $hardware->addChild('USB');
            $tmp = $usb->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                if ($dev->getManufacturer() !== null) {
                    $tmp->addAttribute('Manufacturer', $dev->getManufacturer());
                }
                if ($dev->getProduct() !== null) {
                    $tmp->addAttribute('Product', $dev->getProduct());
                }
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL && ($dev->getSerial() !== null)) {
                    $tmp->addAttribute('Serial', $dev->getSerial());
                }
            }
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $ide = null;
        foreach (System::removeDupsAndCount($this->_sys->getIdeDevices()) as $dev) {
            if ($ide === null) $ide = $hardware->addChild('IDE');
            $tmp = $ide->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                if ($dev->getCapacity() !== null) {
                    $tmp->addAttribute('Capacity', $dev->getCapacity());
                }
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL && ($dev->getSerial() !== null)) {
                    $tmp->addAttribute('Serial', $dev->getSerial());
                }
            }
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $scsi = null;
        foreach (System::removeDupsAndCount($this->_sys->getScsiDevices()) as $dev) {
            if ($scsi === null) $scsi = $hardware->addChild('SCSI');
            $tmp = $scsi->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                if ($dev->getCapacity() !== null) {
                    $tmp->addAttribute('Capacity', $dev->getCapacity());
                }
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL && ($dev->getSerial() !== null)) {
                    $tmp->addAttribute('Serial', $dev->getSerial());
                }
            }
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $nvme = null;
        foreach (System::removeDupsAndCount($this->_sys->getNvmeDevices()) as $dev) {
            if ($nvme === null) $nvme = $hardware->addChild('NVMe');
            $tmp = $nvme->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if (defined('PSI_SHOW_DEVICES_INFOS') && PSI_SHOW_DEVICES_INFOS) {
                if ($dev->getCapacity() !== null) {
                    $tmp->addAttribute('Capacity', $dev->getCapacity());
                }
                if (defined('PSI_SHOW_DEVICES_SERIAL') && PSI_SHOW_DEVICES_SERIAL && ($dev->getSerial() !== null)) {
                    $tmp->addAttribute('Serial', $dev->getSerial());
                }
            }
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $tb = null;
        foreach (System::removeDupsAndCount($this->_sys->getTbDevices()) as $dev) {
            if ($tb === null) $tb = $hardware->addChild('TB');
            $tmp = $tb->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }
        $i2c = null;
        foreach (System::removeDupsAndCount($this->_sys->getI2cDevices()) as $dev) {
            if ($i2c === null) $i2c = $hardware->addChild('I2C');
            $tmp = $i2c->addChild('Device');
            $tmp->addAttribute('Name', $dev->getName());
            if ($dev->getCount() > 1) {
                $tmp->addAttribute('Count', $dev->getCount());
            }
        }

        $cpu = null;
        $vendortab = null;
        foreach ($this->_sys->getCpus() as $oneCpu) {
            if ($cpu === null) $cpu = $hardware->addChild('CPU');
            $tmp = $cpu->addChild('CpuCore');
            $tmp->addAttribute('Model', $oneCpu->getModel());
            if ($oneCpu->getCpuSpeed() !== 0) {
                $tmp->addAttribute('CpuSpeed', max($oneCpu->getCpuSpeed(), 0));
            }
            if ($oneCpu->getCpuSpeedMax() !== 0) {
                $tmp->addAttribute('CpuSpeedMax', $oneCpu->getCpuSpeedMax());
            }
            if ($oneCpu->getCpuSpeedMin() !== 0) {
                $tmp->addAttribute('CpuSpeedMin', $oneCpu->getCpuSpeedMin());
            }
/*
            if ($oneCpu->getTemp() !== null) {
                $tmp->addAttribute('CpuTemp', $oneCpu->getTemp());
            }
*/
            if ($oneCpu->getBusSpeed() !== null) {
                $tmp->addAttribute('BusSpeed', $oneCpu->getBusSpeed());
            }
            if ($oneCpu->getCache() !== null) {
                $tmp->addAttribute('Cache', $oneCpu->getCache());
            }
            if ($oneCpu->getVirt() !== null) {
                $tmp->addAttribute('Virt', $oneCpu->getVirt());
            }
            if ($oneCpu->getVendorId() !== null) {
                if ($vendortab === null) $vendortab = @parse_ini_file(PSI_APP_ROOT."/data/cpus.ini", true);
                $shortvendorid = preg_replace('/[\s!]/', '', $oneCpu->getVendorId());
                if ($vendortab && ($shortvendorid != "") && isset($vendortab['manufacturer'][$shortvendorid])) {
                    $tmp->addAttribute('Manufacturer', $vendortab['manufacturer'][$shortvendorid]);
                }
            }
            if ($oneCpu->getBogomips() !== null) {
                $tmp->addAttribute('Bogomips', $oneCpu->getBogomips());
            }
            if ($oneCpu->getLoad() !== null) {
                $tmp->addAttribute('Load', $oneCpu->getLoad());
            }
        }
    }

    /**
     * generate the memory information
     *
     * @return void
     */
    private function _buildMemory()
    {
        $memory = $this->_xml->addChild('Memory');
        $memory->addAttribute('Free', $this->_sys->getMemFree());
        $memory->addAttribute('Used', $this->_sys->getMemUsed());
        $memory->addAttribute('Total', $this->_sys->getMemTotal());
        $memory->addAttribute('Percent', $this->_sys->getMemPercentUsed());
        if (($this->_sys->getMemApplication() !== null) || ($this->_sys->getMemBuffer() !== null) || ($this->_sys->getMemCache() !== null)) {
            $details = $memory->addChild('Details');
            if ($this->_sys->getMemApplication() !== null) {
                $details->addAttribute('App', $this->_sys->getMemApplication());
                $details->addAttribute('AppPercent', $this->_sys->getMemPercentApplication());
            }
            if ($this->_sys->getMemBuffer() !== null) {
                $details->addAttribute('Buffers', $this->_sys->getMemBuffer());
                $details->addAttribute('BuffersPercent', $this->_sys->getMemPercentBuffer());
            }
            if ($this->_sys->getMemCache() !== null) {
                $details->addAttribute('Cached', $this->_sys->getMemCache());
                $details->addAttribute('CachedPercent', $this->_sys->getMemPercentCache());
            }
        }
        if (count($this->_sys->getSwapDevices()) > 0) {
            $swap = $memory->addChild('Swap');
            $swap->addAttribute('Free', $this->_sys->getSwapFree());
            $swap->addAttribute('Used', $this->_sys->getSwapUsed());
            $swap->addAttribute('Total', $this->_sys->getSwapTotal());
            $swap->addAttribute('Percent', $this->_sys->getSwapPercentUsed());
            $i = 1;
            foreach ($this->_sys->getSwapDevices() as $dev) {
                $swapMount = $swap->addChild('Mount');
                $this->_fillDevice($swapMount, $dev, $i++);
            }
        }
    }

    /**
     * fill a xml element with atrributes from a disk device
     *
     * @param SimpleXmlExtended $mount Xml-Element
     * @param DiskDevice        $dev   DiskDevice
     * @param Integer           $i     counter
     *
     * @return Void
     */
    private function _fillDevice(SimpleXMLExtended $mount, DiskDevice $dev, $i)
    {
        $mount->addAttribute('MountPointID', $i);
        if ($dev->getFsType()!=="") $mount->addAttribute('FSType', $dev->getFsType());
        $mount->addAttribute('Name', $dev->getName());
        $mount->addAttribute('Free', sprintf("%.0f", $dev->getFree()));
        $mount->addAttribute('Used', sprintf("%.0f", $dev->getUsed()));
        $mount->addAttribute('Total', sprintf("%.0f", $dev->getTotal()));
        $mount->addAttribute('Percent', $dev->getPercentUsed());
        if ($dev->getIgnore() > 0) $mount->addAttribute('Ignore', $dev->getIgnore());
        if (PSI_SHOW_MOUNT_OPTION === true) {
            if ($dev->getOptions() !== null) {
                $mount->addAttribute('MountOptions', preg_replace("/,/", ", ", $dev->getOptions()));
            }
        }
        if ($dev->getPercentInodesUsed() !== null) {
            $mount->addAttribute('Inodes', $dev->getPercentInodesUsed());
        }
        if (PSI_SHOW_MOUNT_POINT === true) {
            $mount->addAttribute('MountPoint', $dev->getMountPoint());
        }
    }

    /**
     * generate the filesysteminformation
     *
     * @return void
     */
    private function _buildFilesystems()
    {
        $hideMounts = $hideFstypes = $hideDisks = $ignoreFree = $ignoreUsage = $ignoreThreshold = array();
        $i = 1;
        if (defined('PSI_HIDE_MOUNTS') && is_string(PSI_HIDE_MOUNTS)) {
            if (preg_match(ARRAY_EXP, PSI_HIDE_MOUNTS)) {
                $hideMounts = eval(PSI_HIDE_MOUNTS);
            } else {
                $hideMounts = array(PSI_HIDE_MOUNTS);
            }
        }
        if (defined('PSI_HIDE_FS_TYPES') && is_string(PSI_HIDE_FS_TYPES)) {
            if (preg_match(ARRAY_EXP, PSI_HIDE_FS_TYPES)) {
                $hideFstypes = eval(PSI_HIDE_FS_TYPES);
            } else {
                $hideFstypes = array(PSI_HIDE_FS_TYPES);
            }
        }
        if (defined('PSI_HIDE_DISKS')) {
            if (is_string(PSI_HIDE_DISKS)) {
                if (preg_match(ARRAY_EXP, PSI_HIDE_DISKS)) {
                    $hideDisks = eval(PSI_HIDE_DISKS);
                } else {
                    $hideDisks = array(PSI_HIDE_DISKS);
                }
            } elseif (PSI_HIDE_DISKS === true) {
                return;
            }
        }
        if (defined('PSI_IGNORE_FREE') && is_string(PSI_IGNORE_FREE)) {
            if (preg_match(ARRAY_EXP, PSI_IGNORE_FREE)) {
                $ignoreFree = eval(PSI_IGNORE_FREE);
            } else {
                $ignoreFree = array(PSI_IGNORE_FREE);
            }
        }
        if (defined('PSI_IGNORE_USAGE') && is_string(PSI_IGNORE_USAGE)) {
            if (preg_match(ARRAY_EXP, PSI_IGNORE_USAGE)) {
                $ignoreUsage = eval(PSI_IGNORE_USAGE);
            } else {
                $ignoreUsage = array(PSI_IGNORE_USAGE);
            }
        }
        if (defined('PSI_IGNORE_THRESHOLD_FS_TYPES') && is_string(PSI_IGNORE_THRESHOLD_FS_TYPES)) {
            if (preg_match(ARRAY_EXP, PSI_IGNORE_THRESHOLD_FS_TYPES)) {
                $ignoreThreshold = eval(PSI_IGNORE_THRESHOLD_FS_TYPES);
            } else {
                $ignoreThreshold = array(PSI_IGNORE_THRESHOLD_FS_TYPES);
            }
        }
        $fs = $this->_xml->addChild('FileSystem');
        foreach ($this->_sys->getDiskDevices() as $disk) {
            if (!in_array($disk->getMountPoint(), $hideMounts, true) && !in_array($disk->getFsType(), $hideFstypes, true) && !in_array($disk->getName(), $hideDisks, true)) {
                $mount = $fs->addChild('Mount');
                if (in_array($disk->getFsType(), $ignoreThreshold, true)) {
                    $disk->setIgnore(3);
                } elseif (in_array($disk->getMountPoint(), $ignoreUsage, true)) {
                    $disk->setIgnore(2);
                } elseif (in_array($disk->getMountPoint(), $ignoreFree, true)) {
                    $disk->setIgnore(1);
                }
                $this->_fillDevice($mount, $disk, $i++);
            }
        }
    }

    /**
     * generate the motherboard information
     *
     * @return void
     */
    private function _buildMbinfo()
    {
        $mbinfo = $this->_xml->addChild('MBInfo');
        $temp = $fan = $volt = $power = $current = $other = null;

        if (sizeof(unserialize(PSI_MBINFO))>0) {
            foreach (unserialize(PSI_MBINFO) as $mbinfoclass) {
                $mbinfo_data = new $mbinfoclass();
                $mbinfo_detail = $mbinfo_data->getMBInfo();

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='temperature') foreach ($mbinfo_detail->getMbTemp() as $dev) {
                    if ($temp == null) {
                        $temp = $mbinfo->addChild('Temperature');
                    }
                    $item = $temp->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if ($dev->getMax() !== null) {
                        $item->addAttribute('Max', $dev->getMax());
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='fans') foreach ($mbinfo_detail->getMbFan() as $dev) {
                    if ($fan == null) {
                        $fan = $mbinfo->addChild('Fans');
                    }
                    $item = $fan->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if ($dev->getMin() !== null) {
                        $item->addAttribute('Min', $dev->getMin());
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='voltage') foreach ($mbinfo_detail->getMbVolt() as $dev) {
                    if ($volt == null) {
                        $volt = $mbinfo->addChild('Voltage');
                    }
                    $item = $volt->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if ($dev->getMin() !== null) {
                        $item->addAttribute('Min', $dev->getMin());
                    }
                    if ($dev->getMax() !== null) {
                        $item->addAttribute('Max', $dev->getMax());
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='power') foreach ($mbinfo_detail->getMbPower() as $dev) {
                    if ($power == null) {
                        $power = $mbinfo->addChild('Power');
                    }
                    $item = $power->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if ($dev->getMax() !== null) {
                        $item->addAttribute('Max', $dev->getMax());
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='current') foreach ($mbinfo_detail->getMbCurrent() as $dev) {
                    if ($current == null) {
                        $current = $mbinfo->addChild('Current');
                    }
                    $item = $current->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if ($dev->getMin() !== null) {
                        $item->addAttribute('Min', $dev->getMin());
                    }
                    if ($dev->getMax() !== null) {
                        $item->addAttribute('Max', $dev->getMax());
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }

                if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='other') foreach ($mbinfo_detail->getMbOther() as $dev) {
                    if ($other == null) {
                        $other = $mbinfo->addChild('Other');
                    }
                    $item = $other->addChild('Item');
                    $item->addAttribute('Label', $dev->getName());
                    $item->addAttribute('Value', $dev->getValue());
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->addAttribute('Event', $dev->getEvent());
                    }
                }
            }
        }
    }

    /**
     * generate the ups information
     *
     * @return void
     */
    private function _buildUpsinfo()
    {
        $upsinfo = $this->_xml->addChild('UPSInfo');
        if (defined('PSI_UPS_APCUPSD_CGI_ENABLE') && PSI_UPS_APCUPSD_CGI_ENABLE) {
            $upsinfo->addAttribute('ApcupsdCgiLinks', true);
        }
        if (sizeof(unserialize(PSI_UPSINFO))>0) {
            foreach (unserialize(PSI_UPSINFO) as $upsinfoclass) {
                $upsinfo_data = new $upsinfoclass();
                $upsinfo_detail = $upsinfo_data->getUPSInfo();
                foreach ($upsinfo_detail->getUpsDevices() as $ups) {
                    $item = $upsinfo->addChild('UPS');
                    $item->addAttribute('Name', $ups->getName());
                    if ($ups->getModel() !== "") {
                        $item->addAttribute('Model', $ups->getModel());
                    }
                    if ($ups->getMode() !== "") {
                        $item->addAttribute('Mode', $ups->getMode());
                    }
                    if ($ups->getStartTime() !== "") {
                        $item->addAttribute('StartTime', $ups->getStartTime());
                    }
                    $item->addAttribute('Status', $ups->getStatus());
                    if ($ups->getTemperatur() !== null) {
                        $item->addAttribute('Temperature', $ups->getTemperatur());
                    }
                    if ($ups->getOutages() !== null) {
                        $item->addAttribute('OutagesCount', $ups->getOutages());
                    }
                    if ($ups->getLastOutage() !== null) {
                        $item->addAttribute('LastOutage', $ups->getLastOutage());
                    }
                    if ($ups->getLastOutageFinish() !== null) {
                        $item->addAttribute('LastOutageFinish', $ups->getLastOutageFinish());
                    }
                    if ($ups->getLineVoltage() !== null) {
                        $item->addAttribute('LineVoltage', $ups->getLineVoltage());
                    }
                    if ($ups->getLineFrequency() !== null) {
                        $item->addAttribute('LineFrequency', $ups->getLineFrequency());
                    }
                    if ($ups->getLoad() !== null) {
                        $item->addAttribute('LoadPercent', $ups->getLoad());
                    }
                    if ($ups->getBatteryDate() !== null) {
                        $item->addAttribute('BatteryDate', $ups->getBatteryDate());
                    }
                    if ($ups->getBatteryVoltage() !== null) {
                        $item->addAttribute('BatteryVoltage', $ups->getBatteryVoltage());
                    }
                    if ($ups->getBatterCharge() !== null) {
                        $item->addAttribute('BatteryChargePercent', $ups->getBatterCharge());
                    }
                    if ($ups->getTimeLeft() !== null) {
                        $item->addAttribute('TimeLeftMinutes', $ups->getTimeLeft());
                    }
                }
            }
        }
    }

    /**
     * generate the xml document
     *
     * @return void
     */
    private function _buildXml()
    {
        if (!$this->_plugin_request || $this->_complete_request) {
            if ($this->_sys === null) {
                if (PSI_DEBUG === true) {
                    // unstable version check
                    if (!is_numeric(substr(PSI_VERSION, -1))) {
                        $this->_errors->addError("WARN", "This is an unstable version of phpSysInfo, some things may not work correctly");
                    }

                    // Safe mode check
                    $safe_mode = @ini_get("safe_mode") ? true : false;
                    if ($safe_mode) {
                        $this->_errors->addError("WARN", "PhpSysInfo requires to set off 'safe_mode' in 'php.ini'");
                    }
                    // Include path check
                    $include_path = @ini_get("include_path");
                    if ($include_path && ($include_path!="")) {
                        $include_path = preg_replace("/(:)|(;)/", "\n", $include_path);
                        if (preg_match("/^\.$/m", $include_path)) {
                            $include_path = ".";
                        }
                    }
                    if ($include_path != ".") {
                        $this->_errors->addError("WARN", "PhpSysInfo requires '.' inside the 'include_path' in php.ini");
                    }
                    // popen mode check
                    if (defined("PSI_MODE_POPEN") && PSI_MODE_POPEN === true) {
                        $this->_errors->addError("WARN", "Installed version of PHP does not support proc_open() function, popen() is used");
                    }
                }
                $this->_sys = $this->_sysinfo->getSys();
            }
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='vitals') $this->_buildVitals();
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='network') $this->_buildNetwork();
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='hardware') $this->_buildHardware();
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='memory') $this->_buildMemory();
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='filesystem') $this->_buildFilesystems();
            if (!$this->_sysinfo->getBlockName() || in_array($this->_sysinfo->getBlockName(), array('voltage','current','temperature','fans','power','other'))) $this->_buildMbinfo();
            if (!$this->_sysinfo->getBlockName() || $this->_sysinfo->getBlockName()==='ups') $this->_buildUpsinfo();
        }
        if (!$this->_sysinfo->getBlockName()) $this->_buildPlugins();
        $this->_xml->combinexml($this->_errors->errorsAddToXML($this->_sysinfo->getEncoding()));
    }

    /**
     * get the xml object
     *
     * @return SimpleXmlElement
     */
    public function getXml()
    {
        $this->_buildXml();

        return $this->_xml->getSimpleXmlElement();
    }

    /**
     * include xml-trees of the plugins to the main xml
     *
     * @return void
     */
    private function _buildPlugins()
    {
        $pluginroot = $this->_xml->addChild("Plugins");
        if (($this->_plugin_request || $this->_complete_request) && count($this->_plugins) > 0) {
            $plugins = array();
            if ($this->_complete_request) {
                $plugins = $this->_plugins;
            }
            if ($this->_plugin_request) {
                $plugins = array($this->_plugin);
            }
            foreach ($plugins as $plugin) {
                $object = new $plugin($this->_sysinfo->getEncoding());
                $object->execute();
                $oxml = $object->xml();
                if (sizeof($oxml) > 0) {
                    $pluginroot->combinexml($oxml);
                }
            }
        }
    }

    /**
     * build the xml structure where the content can be inserted
     *
     * @return void
     */
    private function _xmlbody()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement("tns:phpsysinfo");
        $root->setAttribute('xmlns:tns', 'http://phpsysinfo.sourceforge.net/');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://phpsysinfo.sourceforge.net/ phpsysinfo3.xsd');
        $dom->appendChild($root);
        $this->_xml = new SimpleXMLExtended(simplexml_import_dom($dom), $this->_sysinfo->getEncoding());

        $generation = $this->_xml->addChild('Generation');
        $generation->addAttribute('version', PSI_VERSION_STRING);
        $generation->addAttribute('timestamp', time());
        $options = $this->_xml->addChild('Options');
        $options->addAttribute('tempFormat', defined('PSI_TEMP_FORMAT') ? strtolower(PSI_TEMP_FORMAT) : 'c');
        $options->addAttribute('byteFormat', defined('PSI_BYTE_FORMAT') ? strtolower(PSI_BYTE_FORMAT) : 'auto_binary');
        $options->addAttribute('datetimeFormat', defined('PSI_DATETIME_FORMAT') ? strtolower(PSI_DATETIME_FORMAT) : 'utc');
        if (defined('PSI_REFRESH')) {
            $options->addAttribute('refresh',  max(intval(PSI_REFRESH), 0));
        } else {
            $options->addAttribute('refresh', 60000);
        }
        if (defined('PSI_FS_USAGE_THRESHOLD')) {
            if ((($fsut = intval(PSI_FS_USAGE_THRESHOLD)) >= 1) && ($fsut <= 99)) {
                $options->addAttribute('threshold', $fsut);
            }
        } else {
            $options->addAttribute('threshold', 90);
        }
        if (count($this->_plugins) > 0) {
            if ($this->_plugin_request) {
                $plug = $this->_xml->addChild('UsedPlugins');
                $plug->addChild('Plugin')->addAttribute('name', $this->_plugin);
            } elseif ($this->_complete_request) {
                $plug = $this->_xml->addChild('UsedPlugins');
                foreach ($this->_plugins as $plugin) {
                    $plug->addChild('Plugin')->addAttribute('name', $plugin);
                }
/*
            } else {
                $plug = $this->_xml->addChild('UnusedPlugins');
                foreach ($this->_plugins as $plugin) {
                    $plug->addChild('Plugin')->addAttribute('name', $plugin);
                }
*/
            }
        }
    }
}
