FROM php:8.2-apache

# Install required extensions
RUN docker-php-ext-install bcmath

# Copy files
COPY . /var/www/html/

# Update permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to listen on port 10000 (Render default)
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Expose the port
EXPOSE 10000

# Start Apache
CMD ["apache2-foreground"]
