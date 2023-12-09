#!/bin/bash

set -e

# If you want to have more than one application, and in just one of them to run the supervisor, uncomment the lines below,
# and add the env variable IS_WORKER as true in the EBS application you want the supervisor

#if [ "${IS_WORKER}" != "true" ]; then
#    echo "Not a worker. Set variable IS_WORKER=true to run supervisor on this instance"
#    exit 0
#fi


echo "Supervisor - starting setup"

if [ ! -d /etc/supervisor ]; then
    mkdir /etc/supervisor
    echo "create supervisor directory"
else
    echo "supervisor directory already exists"
fi

if [ ! -d /etc/supervisor/conf.d ]; then
    mkdir /etc/supervisor/conf.d
    echo "create supervisor configs directory"
else
    echo "supervisor configs directory already exists"
fi

echo "copy config files"

cat .ebextensions/supervisor/supervisord.conf > /etc/supervisor/supervisord.conf
cat .ebextensions/supervisor/supervisord.conf > /etc/supervisord.conf
cat .ebextensions/supervisor/supervisor_messenger.conf > /etc/supervisor/conf.d/supervisor_messenger.conf

echo "copy done"

echo "kill supervisor if running"

if ps aux | grep "[/]usr/bin/supervisord"; then
  sudo kill -9 $(pgrep -f /usr/bin/supervisord)
fi

echo "starting supervisor"
sudo /usr/bin/supervisord

echo "Supervisor reread"
/usr/bin/supervisorctl reread
echo "Supervisor update"
/usr/bin/supervisorctl update

echo "Supervisor Running!"