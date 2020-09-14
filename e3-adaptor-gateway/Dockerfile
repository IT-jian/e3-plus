FROM registry.cn-hangzhou.aliyuncs.com/wuxyyin/docker-php-nginx:composer-swoole-7.3

# COPY files to image dir /app/
COPY . /app/

# COPY entrypoint to image
COPY docker/entrypoint.sh /

# Composer install
RUN cd /app && \
    composer install && \
    composer clearcache && \
    chown -R nginx.nginx /app && \
    chmod -R 755 /app && \
    chmod -R 777 /app/runtime && \
    chmod +x /entrypoint.sh

# COPY new nginx config
COPY docker/nginx/conf.d/ /etc/nginx/conf.d/

# Make the document root a volume
#VOLUME /app

# Switch to use a non-root user from here on
#USER nginx

# Add application
#WORKDIR /app

# Expose the port nginx is reachable on
#EXPOSE 8080

# Let supervisord start nginx & php-fpm
#CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
ENTRYPOINT ["/entrypoint.sh"]

# Configure a healthcheck to validate that everything is up&running
#HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8081/fpm-ping
