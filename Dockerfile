FROM php:8.2-apache

# Instala dependências necessárias para o Composer funcionar
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    unzip \
    git \
 && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Baixa e instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia o código da aplicação para o diretório padrão do Apache
COPY . /var/www/html





# Ajusta permissões (opcional, mas recomendado)
RUN chown -R www-data:www-data /var/www/html

RUN chmod -R 755 /var/www/html

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite 

# Exponha a porta 80 (padrão Apache)
EXPOSE 80
