FROM php:7.0.10-alpine

RUN apk add --no-cache --virtual build.deps zlib-dev &&\
    docker-php-ext-install pdo_mysql zip &&\
    apk del build.deps

RUN curl -fsSL https://phar.phpunit.de/phpunit-6.3.1.phar -o /usr/local/bin/phpunit &&\
    chmod +x /usr/local/bin/phpunit

RUN curl -fsSL http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o /usr/local/bin/php-cs-fixer &&\
    chmod +x /usr/local/bin/php-cs-fixer

RUN curl -fsSL https://getcomposer.org/download/1.6.2/composer.phar -o /usr/local/bin/composer &&\
    chmod +x /usr/local/bin/composer
