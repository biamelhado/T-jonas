# Usar uma imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instalar dependências do sistema (se necessário)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip

# Instalar e habilitar as extensões PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar o módulo mod_rewrite do Apache (caso precise de URLs amigáveis)
RUN a2enmod rewrite

# Definir o diretório de trabalho para o Apache
WORKDIR /var/www/html

# Copiar os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html/

# Definir permissões para os arquivos (importante para garantir que o Apache tenha acesso adequado)
RUN chown -R www-data:www-data /var/www/html

# Expor a porta 80 para acessar a aplicação via navegador
EXPOSE 80

# Rodar o Apache em primeiro plano
CMD ["apache2-foreground"]
