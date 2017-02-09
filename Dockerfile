FROM php:7.0-cli
MAINTAINER Krotov Artem <timmson666@mail.ru>

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        git \
        cron \
        vim \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && ln -fs /usr/share/zoneinfo/Europe/Moscow /etc/localtime && dpkg-reconfigure -f noninteractive tzdata \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && ln -fs /var/www/html /app

# Add task-cron file in the cron directory
ADD task-cron /etc/cron.d/task-cron

# Set the WORKDIR to /app so all following commands run in /app
WORKDIR /app

# Copy src
COPY ./src/ ./

# Install dependencies with Composer.
RUN composer install --prefer-source --no-interaction && crontab /etc/cron.d/task-cron

# Run the command on container startup
CMD ["cron", "-f"]
