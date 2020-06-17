Please follow below steps to setup the project

1) docker-compose up -d

2) docker-compose exec php composer install  

3) docker-compose exec php yarn install 

4) docker-compose exec php yarn encore dev 

5) docker-compose exec php bin/console doctrine:schema:update --force

6) docker-compose exec php bin/console doctrine:fixtures:load

7) Run tests: docker-compose exec php php bin/phpunit