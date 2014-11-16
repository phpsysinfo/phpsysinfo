# phpSysInfo
# VERSION       2

FROM ubuntu:14.04

MAINTAINER phpSysInfo

# Update sources
RUN echo "deb http://archive.ubuntu.com/ubuntu trusty main universe" > /etc/apt/sources.list
RUN apt-get update

RUN apt-get install -y apache2 php5 git pciutils

RUN git clone https://github.com/phpsysinfo/phpsysinfo.git /var/www/html/phpsysinfo
#RUN cp /var/www/html/phpsysinfo/phpsysinfo.ini.new /var/www/html/phpsysinfo/phpsysinfo.ini
RUN cat /var/www/html/phpsysinfo/phpsysinfo.ini.new | sed 's/^LOAD_BAR=false/LOAD_BAR=true/' >/var/www/html/phpsysinfo/phpsysinfo.ini

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]

EXPOSE 80
