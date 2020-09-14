#!/bin/sh

if [ ! -s "/app/env/.envlock" ]; then
    cd /app/env
    touch .envlock
    if [ -n "$DB_HOST" ] ; then
        sed -i "s|DB_HOST[ =].*$|DB_HOST=$DB_HOST|g" .env
    fi
    if [ -n "$DB_NAME" ] ; then
        sed -i "s|DB_DATABASE[ =].*$|DB_DATABASE=$DB_NAME|g" .env
    fi
    if [ -n "$DB_USER" ] ; then
        sed -i "s|DB_USERNAME[ =].*$|DB_USERNAME=$DB_USER|g" .env
    fi
    if [ -n "$DB_PASS" ] ; then
        sed -i "s|DB_PASSWORD[ =].*$|DB_PASSWORD=$DB_PASS|g" .env
    fi
    if [ -n "$DB_PORT" ]; then
        sed -i "s|DB_PORT[ =].*$|DB_PORT="$DB_PORT"|g" .env
    fi
    if [ -n "$APP_DEBUG" ]; then
        sed -i "s|APP_DEBUG[ =].*$|APP_DEBUG=$APP_DEBUG|g" .env
    fi
    if [ -n "$APP_URL" ]; then
        sed -i "s|APP_URL[ =].*$|APP_URL=$APP_URL|g" .env
    fi
    if [ -n "$CACHE_DRIVER" ]; then
        sed -i "s|CACHE_DRIVER[ =].*$|CACHE_DRIVER=$CACHE_DRIVER|g" .env
    fi
    if [ -n "$REDIS_HOST" ]; then
        sed -i "s|REDIS_HOST[ =].*$|REDIS_HOST=$REDIS_HOST|g" .env
    fi
    if [ -n "$REDIS_PASSWORD" ]; then
        sed -i "s|REDIS_PASSWORD[ =].*$|REDIS_PASSWORD=$REDIS_PASSWORD|g" .env
    fi
    if [ -n "$REDIS_PORT" ]; then
        sed -i "s|REDIS_PORT[ =].*$|REDIS_PORT=$REDIS_PORT|g" .env
    fi
    chown -R nginx.nginx /app
fi
if [ ! -s "/etc/crontabs/nginx" ]; then
    cp /app/docker/crontab/nginx /etc/crontabs/nginx
    crond
fi

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf