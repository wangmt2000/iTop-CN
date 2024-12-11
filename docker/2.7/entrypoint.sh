#!/usr/bin/env bash

set -e -x

DST=/data/iTop
if [ ! -d $DST ];then
  unzip -q -n -d $DST /iTop-2.7.8-10409.zip
  mkdir -p $DST/web/{conf,data,env-production,env-production-build,log}
  chown -R www-data.www-data $DST/web/{conf,data,env-production,env-production-build,log}
  ln -sf $DST/web /var/www/html
fi

exec $@