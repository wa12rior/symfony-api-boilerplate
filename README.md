# Symfony API Boilerplate

## Installation

Installation guide:
Project is not yet fully dockerized. Only database and mailer tools are dockerized for now.

Before you start:

- Install Docker,
- Install composer globally,
- Install php-fpm globally,

```shell
git clone git@github.com:wa12rior/symfony-api-boilerplate.git
# install and copy env vars
composer install
cp .env.example .env.local
# start docker 
docker compose up -d
# create database
make refresh_db
```

## Testing

You should be able to click play button on your test class scope to test it.
However if you don't use PHPStorm you can do it through your console.

With Maker:

```shell
make test
```

or without it

```shell
bin/console --env=test d:d:d --if-exists --force
bin/console --env=test d:d:c --if-not-exists
bin/console --env=test doctrine:mi:mi -n
bin/console --env=test doctrine:fixtures:load
bin/phpunit tests/
```

### Coverage

We use xdebug to make test coverage report that is exported to html. In the terminal just paste:

```shell
XDEBUG_MODE=coverage composer test-coverage
```

## Documentation

http://127.0.0.1:8000/docs
