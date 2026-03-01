FROM php:8.2-cli

WORKDIR /var/www

# تثبيت الأدوات اللازمة
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev zip \
    && docker-php-ext-install zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# نسخ الملفات
COPY . .

# تثبيت الحزم
RUN composer install --no-dev --optimize-autoloader

# توليد مفتاح التطبيق
RUN php artisan key:generate

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000