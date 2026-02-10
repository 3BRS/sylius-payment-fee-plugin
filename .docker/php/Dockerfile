ARG PHP_VERSION=8.3

FROM node:20-alpine AS node

FROM ghcr.io/sylius/sylius-php:${PHP_VERSION}-xdebug-alpine

RUN apk add --no-cache \
    bash \
    make \
    chromium \
    chromium-chromedriver \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/share /usr/local/share
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin
COPY --from=node /opt /opt

# Chromium environment variables
ENV CHROME_BIN=/usr/bin/chromium-browser \
    CHROME_PATH=/usr/lib/chromium/ \
    CHROMIUM_FLAGS="--disable-software-rasterizer --disable-dev-shm-usage --no-sandbox"

CMD [ "php-fpm" ]

ENTRYPOINT [ "docker-php-entrypoint" ]
