#!/usr/bin/env bash

set -e -x

if [ -d /var/www/html/itop ];then
  mkdir -p /var/www/html/itop/{conf,data,env-production,env-production-build,log}
  chown -R www-data.www-data /var/www/html/itop/{conf,data,env-production,env-production-build,log}
  ln -sf /var/www/html /data/itop
fi

exec $@