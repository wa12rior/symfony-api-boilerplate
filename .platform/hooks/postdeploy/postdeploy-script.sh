#!/bin/bash
sudo su

cat /opt/elasticbeanstalk/deployment/env > /var/app/current/.env
