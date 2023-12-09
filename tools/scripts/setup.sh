#!/bin/bash

composer install --no-progress --no-suggest --prefer-dist
php bin/console lexik:jwt:generate-keypair --skip-if-exists
