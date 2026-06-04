# Базовый образ PHP с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/html

# Копируем файлы зависимостей для кеширования
COPY composer.json composer.lock ./
COPY package.json package-lock.json* ./

# Устанавливаем PHP зависимости
RUN composer install --no-dev --no-scripts --no-autoloader

# Устанавливаем Node.js зависимости
RUN npm install

# Копируем весь проект
COPY . .

# Настраиваем права и генерируем автозагрузку
RUN composer dump-autoload --optimize
RUN chown -R www-data:www-data storage bootstrap/cache

# Собираем фронтенд (Vue)
RUN npm run build

# Экспоз порта
EXPOSE 80

# Команда запуска
CMD ["apache2-foreground"]
