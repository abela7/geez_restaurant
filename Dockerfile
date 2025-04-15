FROM php:8.2-apache

# Enable MySQLi
RUN docker-php-ext-install mysqli

# Optional: Enable .htaccess rewrite
RUN a2enmod rewrite

# Copy your code to the web root
COPY . /var/www/html/

# Set proper permissions (optional)
RUN chown -R www-data:www-data /var/www/html/

# Expose port for Render
EXPOSE 80
