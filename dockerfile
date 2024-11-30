# Usar uma imagem oficial do PHP com Apache
FROM php:8.1-apache

# Copiar os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev

# Instalar extensões PHP (se necessário)
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

# Expor a porta padrão do Apache
EXPOSE 80
