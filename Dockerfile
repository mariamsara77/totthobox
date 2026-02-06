# PHP Runtime ব্যবহার করা হচ্ছে
FROM php:8.3.6-fpm

# প্রয়োজনীয় সিস্টেম প্যাকেজ ইন্সটল
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# PHP Extensions ইন্সটল
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer ইন্সটল
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# প্রোজেক্ট ফাইল কপি
WORKDIR /var/www
COPY . .

# ডিপেন্ডেন্সি ইন্সটল এবং Vite বিল্ড
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# পোর্ট সেটআপ
EXPOSE 80

# সার্ভার চালু করার কমান্ড
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]