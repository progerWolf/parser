FROM php:8.1-fpm

ARG user
ARG uid

RUN apt-get update && apt-get install -y \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    zip \
    nano \
    vim \
    git \
    libzip4 \
    libzip-dev \
    supervisor \
    && apt-get -yqq update \
    && apt-get -yqq install libxml2-dev \
    && docker-php-ext-install xml \
    && docker-php-ext-install zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd intl opcache pdo pdo_mysql exif pcntl bcmath soap zip
RUN docker-php-ext-enable xdebug

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www

USER $user
