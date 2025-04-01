# Use the official Composer image version 2.2.25 as the base image
FROM composer:2.2.25  

# Set the working directory inside the container
WORKDIR /conference-cms-backend  

# Copy all project files to the container
COPY . .  

# Install necessary PHP extensions for MySQL support
RUN docker-php-ext-install mysqli pdo pdo_mysql  

# Make the entrypoint script executable
RUN chmod +x Docker/entrypoint.sh  

# Expose port 8000 for the application
EXPOSE 8000  

# Define the container startup script
ENTRYPOINT ["Docker/entrypoint.sh"]
