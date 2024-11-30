# Usar uma imagem oficial do PHP com Apache
FROM php:8.1-apache

# Copiar os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html

# Instalar extensões PHP (se necessário)
RUN docker-php-ext-install mysqli

# Expor a porta padrão do Apache
EXPOSE 80
