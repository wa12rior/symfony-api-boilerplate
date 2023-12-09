refresh_db:
	php bin/console doctrine:database:drop --if-exists --force
	php bin/console doctrine:database:create
	php bin/console do:mi:mi -n

migrate:
	php bin/console do:mi:mi -n

check:
	./vendor/bin/phpstan analyse src tests
	./vendor/bin/phpunit tests
	./vendor/bin/phpcbf -s -p --colors --extensions=php --standard=phpcs.xml src tests
	./vendor/bin/phpcs -s -p --colors --extensions=php --standard=phpcs.xml src tests
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --verbose --show-progress=dots src --rules=fully_qualified_strict_types,no_unused_imports,ordered_imports
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --verbose --show-progress=dots tests --rules=fully_qualified_strict_types,no_unused_imports,ordered_imports

test:
	php bin/console --env=test d:d:d --if-exists --force
	php bin/console --env=test d:d:c --if-not-exists
	php bin/console --env=test doctrine:mi:mi -n
	#php bin/console --env=test d:f:l -n
	./vendor/bin/phpunit tests

coverage:

