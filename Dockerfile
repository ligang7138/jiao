FROM php:8.4-fpm-alpine

# 设置时区
ENV TZ=Asia/Shanghai

# 安装系统依赖
RUN apk add --no-cache \
    autoconf \
    dpkg-dev dpkg \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c \
    nginx \
    curl \
    git \
    zip \
    unzip \
    vim \
    bash \
    mysql-client \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
    redis \
    supervisor

# 安装 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    xml \
    bcmath \
    intl \
    opcache \
    pcntl \
    posix

# 安装 Redis 扩展
RUN pecl install redis && docker-php-ext-enable redis

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 配置 PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# 配置 Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d /etc/nginx/conf.d

# 配置 Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 创建工作目录
WORKDIR /var/www/html

# 复制应用代码
COPY . .

# 创建必要目录并设置权限
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache /var/log/supervisor /var/run/nginx \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 暴露端口
EXPOSE 9000 80

# 启动命令
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
