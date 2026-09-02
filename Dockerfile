FROM php:8.2-apache

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libgd-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        opcache \
    && a2enmod rewrite alias \
    && rm -rf /var/lib/apt/lists/*

# Set DocumentRoot to app/ (index.php acts as front controller)
RUN sed -i \
    's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/app|g' \
    /etc/apache2/sites-available/000-default.conf

# Apache VirtualHost directives — allow .htaccess + uploads alias
RUN printf '<Directory /var/www/html/app>\n\
    AllowOverride All\n\
    Require all granted\n\
    Options -Indexes +FollowSymLinks\n\
</Directory>\n' \
>> /etc/apache2/sites-available/000-default.conf

# PHP production hardening & performance tuning
RUN { \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 22M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 60'; \
    echo 'expose_php = Off'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.cookie_samesite = Lax'; \
    echo 'session.use_strict_mode = 1'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
    echo 'opcache.interned_strings_buffer = 8'; \
    echo 'opcache.max_accelerated_files = 4000'; \
    echo 'opcache.revalidate_freq = 2'; \
} > /usr/local/etc/php/conf.d/simou.ini

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
