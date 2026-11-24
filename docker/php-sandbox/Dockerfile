FROM php:8.5-cli-alpine

COPY php.ini /usr/local/etc/php/conf.d/99-sandbox.ini

RUN addgroup -g 1000 -S sandbox \
    && adduser -u 1000 -S -G sandbox -h /tmp -s /sbin/nologin sandbox \
    && mkdir -p /workspace \
    && chown 1000:1000 /workspace

WORKDIR /workspace
USER 1000:1000

CMD ["php", "-v"]
