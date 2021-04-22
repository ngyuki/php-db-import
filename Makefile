all:
	env PHP_VERSION=7.0 docker-compose run --rm -u $(shell id -u) -T php composer update --no-progress --prefer-lowest
	env PHP_VERSION=7.0 docker-compose run --rm -u $(shell id -u) -T php composer qa
	env PHP_VERSION=7.4 docker-compose run --rm -u $(shell id -u) -T php composer update --no-progress --prefer-stable
	env PHP_VERSION=7.4 docker-compose run --rm -u $(shell id -u) -T php composer qa
