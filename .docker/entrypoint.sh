#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh

# OPCache development mode.
if [[ $OPCACHE_MODE == "development" ]]; then
    # enable development caching for OPCache.
    echo "opcache.enable=1" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.revalidate_freq=0" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.validate_timestamps=1" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.max_accelerated_files=10000" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.memory_consumption=192" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.max_wasted_percentage=10" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.interned_strings_buffer=16" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.fast_shutdown=1" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
fi

# OPCache extreme mode.
if [[ $OPCACHE_MODE == "extreme" ]]; then
    # enable extreme caching for OPCache.
    echo "opcache.enable=1" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.memory_consumption=512" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.interned_strings_buffer=128" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.max_accelerated_files=32531" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.validate_timestamps=0" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.save_comments=1" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
    echo "opcache.fast_shutdown=0" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
fi

# OPCache disabled mode.
if [[ $OPCACHE_MODE == "disabled" ]]; then
    # disable extension.
    sed -i "/zend_extension=/c\;zend_extension=" /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
    # set enabled as zero, case extension still gets loaded (by other extension).
    echo "opcache.enable=0" | tee -a /usr/local/etc/php/conf.d/00_opcache.ini >/dev/null
fi

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if [ ! -f ".env.testing" ]; then
    cp .env.testing.example .env.testing
fi

if [[ $APP_MODE == "development" ]]; then
    composer install
    php artisan key:generate
    php artisan migrate
fi

if [[ $CACHE_MODE == "development" ]]; then
    composer cache-prune
fi

if [[ $CACHE_MODE == "extreme" ]]; then
    composer cache-all
fi

php-fpm
