#!/bin/sh

# set up the fpm instance with parameters.yml
docker exec fpm composer update --no-scripts
docker exec fpm bin/console doctrine:schema:create
docker exec fpm composer update
# import the datas
docker exec -i db mysql -u root --password=password freestatesafety < original/gclabel.sql
docker exec -i db mysql -u root --password=password freestatesafety < original/import.sql
# update for existing images
docker exec fpm bin/console app:fix-empty-product-image
