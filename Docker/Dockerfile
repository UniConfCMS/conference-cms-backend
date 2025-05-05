FROM composer:2.8.8

WORKDIR /conference-cms-backend  

COPY . .

RUN docker-php-ext-install mysqli pdo pdo_mysql  

RUN chmod +x /conference-cms-backend/Docker/entrypoint.sh  

EXPOSE 8000  

ENTRYPOINT ["/conference-cms-backend/Docker/entrypoint.sh"]
