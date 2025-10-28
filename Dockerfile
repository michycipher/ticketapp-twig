# Use official PHP image
FROM php:8.2-cli

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install dependencies
RUN composer install

# Expose port
EXPOSE 10000

# Start PHP server from /public
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]