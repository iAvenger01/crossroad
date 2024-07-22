FROM nginx:alpine

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/conf.d /etc/nginx/conf.d

WORKDIR /var/www/app

CMD ["nginx"]

EXPOSE 80