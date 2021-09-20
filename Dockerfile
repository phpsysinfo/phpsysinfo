# phpSysInfo
# VERSION       4

FROM ubuntu:20.04
ENV LC_ALL C.UTF-8
ARG DEBIAN_FRONTEND=noninteractive
ARG http_proxy=""
ARG https_proxy=""

MAINTAINER phpSysInfo

# Update sources
RUN apt-get -q update && \
    apt-get -qy install apache2 php7.4 php7.4-xml php7.4-mbstring libapache2-mod-php7.4 git pciutils && \
    apt-get clean && \
    rm -Rf /var/lib/apt/lists/*

RUN git clone https://github.com/phpsysinfo/phpsysinfo.git /var/www/html/phpsysinfo && \
    cp /var/www/html/phpsysinfo/phpsysinfo.ini.new /var/www/html/phpsysinfo/phpsysinfo.ini

ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]

EXPOSE 80
