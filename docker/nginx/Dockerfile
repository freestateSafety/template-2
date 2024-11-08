FROM nginx:latest
COPY docker.local.key /etc/ssl/docker.local.key
COPY docker.local.crt /etc/ssl/docker.local.crt
COPY default.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www/public_html