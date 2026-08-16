FROM docker.io/library/php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" intl mysqli gd zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8080"]
