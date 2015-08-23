<?xml version="1.0" encoding="UTF-8"?>
    <!--  $Id: phpsysinfo.xslt 699 2012-09-15 11:57:13Z namiltd $ -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fn="http://www.w3.org/2005/xpath-functions"
    xmlns:xdt="http://www.w3.org/2005/xpath-datatypes" xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <xsl:output version="4.0" method="html" indent="no"
        encoding="UTF-8" doctype-public="-//W3C//DTD HTML 4.0 Transitional//EN"
        doctype-system="http://www.w3.org/TR/html4/loose.dtd" />
    <xsl:param name="SV_OutputFormat" select="'HTML'" />
    <xsl:variable name="XML" select="/" />
    <xsl:template match="/">
        <html>
            <head>
                <title>
                    <xsl:text>phpSysInfo</xsl:text>
                </title>
                <style type="text/css">
                    <xsl:comment>
                        @import url("templates/phpsysinfo.css");
                    </xsl:comment>
                </style>
                <link href="gfx/favicon.png" rel="shortcut icon" />
            </head>
            <body>
                <xsl:for-each select="$XML">
                    <xsl:for-each select="*">
                        <div>
                            <xsl:for-each select="Vitals">
                                <h1 id="title" style="_color: #000; /* ie6 fix */">
                                    <span>
                                        <xsl:text>System information : </xsl:text>
                                    </span>
                                    <xsl:value-of select="@Hostname" />
                                    <span>
                                        <xsl:text> (</xsl:text>
                                    </span>
                                    <xsl:value-of select="@IPAddr" />
                                    <span>
                                        <xsl:text>)</xsl:text>
                                    </span>
                                </h1>
                            </xsl:for-each>
                            <div id="vitals">
                                <xsl:for-each select="Vitals">
                                    <h2>
                                        <span>
                                            <xsl:text>System Vital</xsl:text>
                                        </span>
                                    </h2>
                                    <table border="0" style="border-spacing:0;"
                                        class="stripMe" id="vitalsTable"
                                        width="100%">
                                        <tbody>
                                            <tr>
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Canonical Hostname</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <xsl:value-of
                                                        select="@Hostname" />
                                                </td>
                                            </tr>
                                            <tr class="odd">
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Listening IP</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <xsl:value-of
                                                        select="@IPAddr" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Kernel Version</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <xsl:value-of
                                                        select="@Kernel" />
                                                </td>
                                            </tr>
                                            <tr class="odd">
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Distro Name</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <img
                                                        style="height:16px; width:16px;">
                                                        <xsl:attribute
                                                            name="src">
                                                            <xsl:if
                                                            test="substring(string(concat(&apos;gfx/images/&apos;,@Distroicon)), 2, 1) = ':'">
                                                                <xsl:text>file:///</xsl:text>
                                                            </xsl:if>
                                                            <xsl:value-of
                                                            select="translate(string(concat(&apos;gfx/images/&apos;,@Distroicon)), '&#x5c;', '/')" />
                                                        </xsl:attribute>
                                                        <xsl:attribute
                                                            name="alt" />
                                                    </img>
                                                    <span>
                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                    </span>
                                                    <span>
                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                    </span>
                                                    <xsl:value-of
                                                        select="@Distro" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Uptime</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span>
                                                        <xsl:value-of
                                                            select="floor( @Uptime div 60 div 60 div 24)" />
                                                    </span>
                                                    <span>
                                                        <xsl:text> Days </xsl:text>
                                                    </span>
                                                    <span>
                                                        <xsl:value-of
                                                            select="floor( ( @Uptime div 60 div 60) - ( floor( @Uptime div 60 div 60 div 24) * 24) )" />
                                                    </span>
                                                    <span>
                                                        <xsl:text> Hours </xsl:text>
                                                    </span>
                                                    <span>
                                                        <xsl:value-of
                                                            select="floor( @Uptime div 60 - ( floor( @Uptime div 60 div 60 div 24) * 60 * 24) - ( floor( ( @Uptime div 60 div 60) - ( floor( @Uptime div 60 div 60 div 24) * 24) ) * 60) )" />
                                                    </span>
                                                    <span>
                                                        <xsl:text> Minutes</xsl:text>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="odd">
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Current Users</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <xsl:value-of
                                                        select="@Users" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:160px;">
                                                    <span>
                                                        <xsl:text>Load Averages</xsl:text>
                                                    </span>
                                                </td>
                                                <td>
                                                    <xsl:value-of
                                                        select="@LoadAvg" />
                                                    <xsl:if
                                                        test="count(@CPULoad )&gt;0">
                                                        <xsl:text disable-output-escaping="yes">&lt;br/&gt;</xsl:text>
                                                        <div
                                                            style="float:left; width:{concat(  CPULoad  , &apos;px&apos; )}; "
                                                            class="bar">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                        </div>
                                                        <div
                                                            style="float:left; ">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                            <xsl:value-of
                                                                select="round(@CPULoad)" />
                                                            <span>
                                                                <xsl:text>%</xsl:text>
                                                            </span>
                                                        </div>
                                                    </xsl:if>
                                                </td>
                                            </tr>
                                            <xsl:if
                                                test="count(@SysLang )&gt;0">
                                                <tr class="odd">
                                                    <td style="width:160px;">
                                                        <span>
                                                            <xsl:text>System Language</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of
                                                            select="@SysLang" />
                                                    </td>
                                                </tr>
                                            </xsl:if>
                                            <xsl:if
                                                test="count(@CodePage )&gt;0">
                                                <tr class="odd">
                                                    <td style="width:160px;">
                                                        <span>
                                                            <xsl:text>Code Page</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of
                                                            select="@CodePage" />
                                                    </td>
                                                </tr>
                                            </xsl:if>
                                            <xsl:if
                                                test="count(@Processes )&gt;0">
                                                <tr class="odd">
                                                    <td style="width:160px;">
                                                        <span>
                                                            <xsl:text>Processes</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of
                                                            select="@Processes" />
                                                    </td>
                                                </tr>
                                            </xsl:if>
                                        </tbody>
                                    </table>
                                </xsl:for-each>
                            </div>
                            <div id="hardware">
                                <xsl:for-each select="Hardware">
                                    <h2>
                                        <span>
                                            <xsl:text>Hardware Information</xsl:text>
                                        </span>
                                    </h2>
                                    <xsl:if
                                        test="count(@Name )&gt;0">
                                        <table border="0" style="border-spacing:0;"
                                            width="100%">
                                            <tbody>
                                                <tr class="odd">
                                                    <td style="width:160px;">
                                                        <span>
                                                            <xsl:text>Machine</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of
                                                            select="@Name" />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:if>
                                    <xsl:for-each select="CPU">
                                        <table border="0" style="border-spacing:0;"
                                            width="100%">
                                            <tbody>
                                                <tr class="odd">
                                                    <td style="width:160px;">
                                                        <span>
                                                            <xsl:text>Processors</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <xsl:value-of
                                                            select="count(CpuCore)" />
                                                    </td>
                                                </tr>
                                                <xsl:for-each
                                                    select="CpuCore">
                                                    <xsl:if
                                                        test="count(@Model )&gt;0">
                                                        <tr class="odd">
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>Model</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@Model" />
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@CpuSpeed )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>CPU Speed</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@CpuSpeed" />
                                                                <span>
                                                                    <xsl:text> MHz</xsl:text>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@CpuSpeedMax )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>CPU Speed Max</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@CpuSpeedMax" />
                                                                <span>
                                                                    <xsl:text> MHz</xsl:text>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@CpuSpeedMin )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>CPU Speed Min</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@CpuSpeedMin" />
                                                                <span>
                                                                    <xsl:text> MHz</xsl:text>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@BusSpeed )&gt;0">
                                                        <tr class="odd">
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>Bus Speed</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@BusSpeed" />
                                                                <span>
                                                                    <xsl:text> MHz</xsl:text>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@Cache )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>Cache Size</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="round(@Cache div 1024)" />
                                                                <span>
                                                                    <xsl:text> KiB</xsl:text>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@Virt )&gt;0">
                                                        <tr class="odd">
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>Virtualization</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="@Virt" />
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@Bogomips )&gt;0">
                                                        <tr class="odd">
                                                            <td
                                                                style="width:160px;">
                                                                <span>
                                                                    <xsl:text>System Bogomips</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of
                                                                    select="round(@Bogomips)" />
                                                            </td>
                                                        </tr>
                                                    </xsl:if>
                                                </xsl:for-each>
                                            </tbody>
                                        </table>

                                    </xsl:for-each>
                                    <xsl:for-each select="PCI">
                                        <h3>
                                            <span>
                                                <xsl:text>PCI Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="display:block; border-spacing:0;"
                                            id="pciTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                    <xsl:for-each select="IDE">
                                        <h3 class="odd">
                                            <span>
                                                <xsl:text>IDE Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="display:block; border-spacing:0;"
                                            class="odd" id="ideTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                    <xsl:if
                                                                        test="count(@Capacity )&gt;0">
                                                                        <span>
                                                                            <xsl:text> (</xsl:text>
                                                                        </span>
                                                                        <xsl:value-of select="round(@Capacity div 1024)" />
                                                                        <span>
                                                                            <xsl:text> KiB)</xsl:text>
                                                                        </span>
                                                                    </xsl:if>
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                    <xsl:for-each select="SCSI">
                                        <h3>
                                            <span>
                                                <xsl:text>SCSI Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="border-spacing:0;"
                                            id="scsiTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td
                                                        style="display:block;">
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                    <xsl:if
                                                                        test="count(@Capacity )&gt;0">
                                                                        <span>
                                                                            <xsl:text> (</xsl:text>
                                                                        </span>
                                                                        <xsl:value-of select="round(@Capacity div 1024)" />
                                                                        <span>
                                                                            <xsl:text> KiB)</xsl:text>
                                                                        </span>
                                                                    </xsl:if>
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                    <xsl:for-each select="USB">
                                        <h3 class="odd">
                                            <span>
                                                <xsl:text>USB Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="border-spacing:0;"
                                            class="odd" id="usbTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                    <xsl:for-each select="TB">
                                        <h3 class="odd">
                                            <span>
                                                <xsl:text>TB Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="border-spacing:0;"
                                            class="odd" id="tbTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                    <xsl:for-each select="I2C">
                                        <h3 class="odd">
                                            <span>
                                                <xsl:text>I2C Devices</xsl:text>
                                            </span>
                                        </h3>
                                        <table style="border-spacing:0;"
                                            class="odd" id="i2cTable"
                                            width="100%">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <ul
                                                            style="margin-left:10px;">
                                                            <xsl:for-each
                                                                select="Device">
                                                                <li>
                                                                    <xsl:value-of
                                                                        select="@Name" />
                                                                </li>
                                                            </xsl:for-each>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>
                                </xsl:for-each>
                            </div>
                            <div id="memory">
                                <xsl:for-each select="Memory">
                                    <h2>
                                        <span>
                                            <xsl:text>Memory Usage</xsl:text>
                                        </span>
                                    </h2>
                                    <table border="0"
                                    style="border-spacing:0;">
                                        <thead>
                                            <tr>
                                                <th style="width:200px;">
                                                    <span>
                                                        <xsl:text>Type</xsl:text>
                                                    </span>
                                                </th>
                                                <th style="width:285px;">
                                                    <span>
                                                        <xsl:text>Usage</xsl:text>
                                                    </span>
                                                </th>
                                                <th style="width:100px;"
                                                    class="right">
                                                    <span>
                                                        <xsl:text>Free</xsl:text>
                                                    </span>
                                                </th>
                                                <th style="width:100px;"
                                                    class="right">
                                                    <span>
                                                        <xsl:text>Used</xsl:text>
                                                    </span>
                                                </th>
                                                <th style="width:100px;"
                                                    class="right">
                                                    <span>
                                                        <xsl:text>Size</xsl:text>
                                                    </span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="odd">
                                                <td style="width:200px;">
                                                    <span>
                                                        <xsl:text>Physical Memory</xsl:text>
                                                    </span>
                                                </td>
                                                <td style="width:285px;">
                                                    <div
                                                        style="float:left; width:{concat(  @Percent  , &apos;px&apos; )}; "
                                                        class="bar">
                                                        <span>
                                                            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                        </span>
                                                    </div>
                                                    <div style="float:left; ">
                                                        <span>
                                                            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                        </span>
                                                        <xsl:value-of
                                                            select="@Percent" />
                                                        <span>
                                                            <xsl:text>%</xsl:text>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td style="width:100px;"
                                                    class="right">
                                                    <xsl:value-of
                                                        select="round(@Free div 1024)" />
                                                    <span>
                                                        <xsl:text> KiB</xsl:text>
                                                    </span>
                                                </td>
                                                <td style="width:100px;"
                                                    class="right">
                                                    <xsl:value-of
                                                        select="round(@Used div 1024)" />
                                                    <span>
                                                        <xsl:text> KiB</xsl:text>
                                                    </span>
                                                </td>
                                                <td style="width:100px;"
                                                    class="right">
                                                    <xsl:value-of
                                                        select="round(@Total div 1024)" />
                                                    <span>
                                                        <xsl:text> KiB</xsl:text>
                                                    </span>
                                                </td>
                                            </tr>
                                            <xsl:for-each
                                                select="Details">
                                                <xsl:if
                                                    test="count(@* )&gt;0">
                                                    <xsl:if
                                                        test="count(@App )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:200px;">
                                                                <span>
                                                                    <xsl:text>- Kernel + applications</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:285px;">
                                                                <div
                                                                    style="float:left; width:{concat(  @AppPercent  , &apos;px&apos; )}; "
                                                                    class="bar">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    style="float:left; ">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                    <xsl:value-of
                                                                        select="@AppPercent" />
                                                                    <span>
                                                                        <xsl:text>%</xsl:text>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                            <td
                                                                style="width:100px;"
                                                                class="right">
                                                                <xsl:value-of
                                                                    select="round(@App div 1024)" />
                                                                <span>
                                                                    <xsl:text> KiB</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@Cached )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:200px;">
                                                                <span>
                                                                    <xsl:text>- Cached</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:285px;">
                                                                <div
                                                                    style="float:left; width:{concat(  @CachedPercent  , &apos;px&apos; )}; "
                                                                    class="bar">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    style="float:left; ">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                    <xsl:value-of
                                                                        select="@CachedPercent" />
                                                                    <span>
                                                                        <xsl:text>%</xsl:text>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                            <td
                                                                style="width:100px;"
                                                                class="right">
                                                                <xsl:value-of
                                                                    select="round(@Cached div 1024)" />
                                                                <span>
                                                                    <xsl:text> KiB</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                        </tr>
                                                    </xsl:if>
                                                    <xsl:if
                                                        test="count(@Buffers )&gt;0">
                                                        <tr>
                                                            <td
                                                                style="width:200px;">
                                                                <span>
                                                                    <xsl:text>- Buffers</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:285px;">
                                                                <div
                                                                    style="float:left; width:{concat(  @BuffersPercent  , &apos;px&apos; )}; "
                                                                    class="bar">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    style="float:left; ">
                                                                    <span>
                                                                        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                    </span>
                                                                    <xsl:value-of
                                                                        select="@BuffersPercent" />
                                                                    <span>
                                                                        <xsl:text>%</xsl:text>
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                            <td
                                                                style="width:100px;"
                                                                class="right">
                                                                <xsl:value-of
                                                                    select="round(@Buffers div 1024)" />
                                                                <span>
                                                                    <xsl:text> KiB</xsl:text>
                                                                </span>
                                                            </td>
                                                            <td
                                                                style="width:100px;"
                                                                class="right" />
                                                        </tr>
                                                    </xsl:if>
                                                </xsl:if>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                    <xsl:for-each select="Swap">
                                        <table border="0"
                                            style="border-spacing:0;"
                                            width="100%">
                                            <tbody>
                                                <tr class="odd">
                                                    <td style="width:200px;">
                                                        <span>
                                                            <xsl:text>Disk Swap</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td style="width:285px;">
                                                        <div
                                                            style="float:left; width:{concat(  @Percent  , &apos;px&apos; )}; "
                                                            class="bar">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                        </div>
                                                        <div
                                                            style="float:left; ">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                            <xsl:value-of
                                                                select="@Percent" />
                                                            <span>
                                                                <xsl:text>%</xsl:text>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td style="width:100px;"
                                                        class="right">
                                                        <xsl:value-of
                                                            select="round(@Free div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td style="width:100px;"
                                                        class="right">
                                                        <xsl:value-of
                                                            select="round(@Used div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td style="width:100px;"
                                                        class="right">
                                                        <xsl:value-of
                                                            select="round(@Total div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <xsl:for-each
                                                    select="Mount">
                                                    <tr class="odd">
                                                        <td
                                                            style="width:200px;">
                                                            <span>
                                                                <xsl:text>- </xsl:text>
                                                                <xsl:value-of
                                                                    select="@MountPoint" />
                                                            </span>
                                                        </td>
                                                        <td
                                                            style="width:285px;">
                                                            <div
                                                                style="float:left; width:{concat(  @Percent  , &apos;px&apos; )}; "
                                                                class="bar">
                                                                <span>
                                                                    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                </span>
                                                            </div>
                                                            <div
                                                                style="float:left; ">
                                                                <span>
                                                                    <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                                </span>
                                                                <xsl:value-of
                                                                    select="@Percent" />
                                                                <span>
                                                                    <xsl:text>%</xsl:text>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td
                                                            style="width:100px;"
                                                            class="right">
                                                            <xsl:value-of
                                                                select="round(@Free div 1024)" />
                                                            <span>
                                                                <xsl:text> KiB</xsl:text>
                                                            </span>
                                                        </td>
                                                        <td
                                                            style="width:100px;"
                                                            class="right">
                                                            <xsl:value-of
                                                                select="round(@Used div 1024)" />
                                                            <span>
                                                                <xsl:text> KiB</xsl:text>
                                                            </span>
                                                        </td>
                                                        <td
                                                            style="width:100px;"
                                                            class="right">
                                                            <xsl:value-of
                                                                select="round(@Total div 1024)" />
                                                            <span>
                                                                <xsl:text> KiB</xsl:text>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </xsl:for-each>
                                            </tbody>
                                        </table>
                                    </xsl:for-each>

                                </xsl:for-each>
                            </div>
                            <div id="filesystem">
                                <h2>
                                    <span>
                                        <xsl:text>Mounted Filesystems</xsl:text>
                                    </span>
                                </h2>
                                <table style="border-spacing:0;"
                                    class="stripMe" id="filesystemTable">
                                    <thead>
                                        <tr>
                                            <th style="width:100px;">
                                                <span>
                                                    <xsl:text>Mountpoint</xsl:text>
                                                </span>
                                            </th>
                                            <th style="width:50px;">
                                                <span>
                                                    <xsl:text>Type</xsl:text>
                                                </span>
                                            </th>
                                            <th style="width:120px;">
                                                <span>
                                                    <xsl:text>Partition</xsl:text>
                                                </span>
                                            </th>
                                            <th>
                                                <span>
                                                    <xsl:text>Usage</xsl:text>
                                                </span>
                                            </th>
                                            <th style="width:100px;"
                                                class="right">
                                                <span>
                                                    <xsl:text>Free</xsl:text>
                                                </span>
                                            </th>
                                            <th style="width:100px;"
                                                class="right">
                                                <span>
                                                    <xsl:text>Used</xsl:text>
                                                </span>
                                            </th>
                                            <th style="width:100px;"
                                                class="right">
                                                <span>
                                                    <xsl:text>Size</xsl:text>
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <xsl:for-each
                                            select="FileSystem">
                                            <xsl:for-each
                                                select="Mount">
                                                <tr>
                                                    <td style="width:100px;">
                                                        <xsl:value-of
                                                            select="@MountPoint" />
                                                    </td>
                                                    <td style="width:50px;">
                                                        <xsl:value-of
                                                            select="@FSType" />
                                                    </td>
                                                    <td style="width:120px;">
                                                        <xsl:value-of
                                                            select="@Name" />
                                                    </td>
                                                    <td style="width:285px;">
                                                        <div
                                                            style="float:left; width:{concat(  @Percent  , &apos;px&apos; )}; "
                                                            class="bar">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                        </div>
                                                        <div
                                                            style="float:left; ">
                                                            <span>
                                                                <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                            </span>
                                                            <xsl:value-of
                                                                select="@Percent" />
                                                            <span>
                                                                <xsl:text>%</xsl:text>
                                                            </span>
                                                            <xsl:if
                                                                test="count(@Inodes )&gt;0">
                                                                <span>
                                                                    <xsl:text> (</xsl:text>
                                                                </span>
                                                                <span
                                                                    style="font-style:italic;">
                                                                    <xsl:value-of
                                                                        select="@Inodes" />
                                                                </span>
                                                                <span>
                                                                    <xsl:text>%)</xsl:text>
                                                        </span>
                                                            </xsl:if>
                                                        </div>
                                                    </td>
                                                    <td style="width:100px;" class="right">
                                                        <xsl:value-of select="round(@Free div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td style="width:100px;" class="right">
                                                        <xsl:value-of select="round(@Used div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td style="width:100px;" class="right">
                                                        <xsl:value-of select="round(@Total div 1024)" />
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </xsl:for-each>
                                    </tbody>
                                </table>
                            </div>
                            <div id="network">
                                <h2>
                                    <span>
                                        <xsl:text>Network Usage</xsl:text>
                                    </span>
                                </h2>
                                <table style="border-spacing:0;"
                                    class="stripMe" id="networkTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <span>
                                                    <xsl:text>Device</xsl:text>
                                                </span>
                                            </th>
                                            <th class="right" width="60px">
                                                <span>
                                                    <xsl:text>Received</xsl:text>
                                                </span>
                                            </th>
                                            <th class="right" width="60px">
                                                <span>
                                                    <xsl:text>Send</xsl:text>
                                                </span>
                                            </th>
                                            <th class="right" width="60px">
                                                <span>
                                                    <xsl:text>Err/Drop</xsl:text>
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <xsl:for-each select="Network">
                                            <xsl:for-each select="NetDevice">
                                                <tr>
                                                    <td>
                                                        <xsl:value-of select="@Name"/>
                                                    </td>
                                                    <td class="right" width="60px">
                                                        <span>
                                                            <xsl:value-of select="round(@RxBytes div 1024)"/>
                                                        </span>
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td class="right" width="60px">
                                                        <span>
                                                            <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
                                                        </span>
                                                        <span>
                                                            <xsl:value-of select="round(@TxBytes div 1024)"/>
                                                        </span>
                                                        <span>
                                                            <xsl:text> KiB</xsl:text>
                                                        </span>
                                                    </td>
                                                    <td class="right" width="60px">
                                                        <xsl:value-of select="@Err"/>
                                                        <span>
                                                            <xsl:text>/</xsl:text>
                                                        </span>
                                                        <xsl:value-of select="@Drops"/>
                                                    </td>
                                                </tr>
                                            </xsl:for-each>
                                        </xsl:for-each>
                                    </tbody>
                                </table>
                            </div>
                            <div id="footer">
                                <span>
                                    <xsl:text>Created by </xsl:text>
                                </span>
                                <a>
                                    <xsl:choose>
                                        <xsl:when test="substring(string(&apos;http://phpsysinfo.sourceforge.net/&apos;), 1, 1) = '#'">
                                            <xsl:attribute name="href">
                                                <xsl:value-of select="&apos;http://phpsysinfo.sourceforge.net/&apos;"/>
                                            </xsl:attribute>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:attribute name="href">
                                                <xsl:if test="substring(string(&apos;http://phpsysinfo.sourceforge.net/&apos;), 2, 1) = ':'">
                                                    <xsl:text>file:///</xsl:text>
                                                </xsl:if>
                                                <xsl:value-of select="translate(string(&apos;http://phpsysinfo.sourceforge.net/&apos;), '&#x5c;', '/')"/>
                                            </xsl:attribute>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <span>
                                        <xsl:text>phpSysInfo - </xsl:text>
                                    </span>
                                    <xsl:for-each select="Generation">
                                        <xsl:for-each select="@version">
                                            <span>
                                                <xsl:value-of select="string(.)"/>
                                            </span>
                                        </xsl:for-each>
                                    </xsl:for-each>
                                </a>
                            </div>
                        </div>
                    </xsl:for-each>
                </xsl:for-each>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
