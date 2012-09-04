#!/bin/sh -x

if [ $# -ne 1 ]
then
    echo "Usage : ./tools/MakeRelease <version>"
    exit
fi

sed -i "s/PSI_VERSION = '.*'/PSI_VERSION = '$1'/g" includes/class.CommonFunctions.inc.php
ARCHIVE_NAME="phpsysinfo-$1.tar.gz"

#copy to temp dir
rm -rf /tmp/phpsysinfo
mkdir /tmp/phpsysinfo
cp -R . /tmp/phpsysinfo
cd /tmp/phpsysinfo

# remove the svn directories
find . -type d -name .svn -exec rm -fr {} \;
#or find . -iname ".svn" -print0 | xargs -0 rm -r

#remove some dirs
rm -rf tools sample

#remove phpsysinfo.ini
rm -rf phpsysinfo.ini .cvsignore .project

#create archive
cd ..
tar -czf $ARCHIVE_NAME phpsysinfo

md5sum $ARCHIVE_NAME
