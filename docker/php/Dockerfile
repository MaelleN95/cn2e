FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libzip-dev libpng-dev libonig-dev libxml2-dev \
    mariadb-client \
    libicu-dev \
    libmagickwand-dev --no-install-recommends \
    && docker-php-ext-install pdo pdo_mysql zip gd opcache intl \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /var/lib/apt/lists/*

# Node + npm
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    ca-certificates

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# vérification
RUN node -v && npm -v

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Copy custom PHP config
COPY conf/docker-php-custom.ini /usr/local/etc/php/conf.d/

# Bash history configuration
RUN echo 'export HISTFILE=/root/.bash_history' >> /root/.bashrc \
 && echo 'export HISTSIZE=10000' >> /root/.bashrc \
 && echo 'export HISTFILESIZE=20000' >> /root/.bashrc \
 && echo 'shopt -s histappend' >> /root/.bashrc \
 && echo 'PROMPT_COMMAND="history -a"' >> /root/.bashrc

WORKDIR /var/www/html