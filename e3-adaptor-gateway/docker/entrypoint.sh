#!/bin/sh

cd /app
cp .env.example .env
chown -R nginx.nginx /app

chmod -R 777 /app/runtime

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf