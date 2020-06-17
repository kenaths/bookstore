FROM php:7.4-fpm

RUN docker-php-ext-install pdo_mysql

RUN pecl install apcu

RUN apt-get update && \
apt-get install -y \
zlib1g-dev

# RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu

# RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
#     && php -r "if (hash_file('SHA384', 'composer-setup.php') === '%SHA_384%') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
#     && php composer-setup.php --filename=composer \
#     && php -r "unlink('composer-setup.php');" \
#     && mv composer /usr/local/bin/composer

RUN apt-get install -y curl nano && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install git -y 

RUN apt-get install zip unzip

RUN apt-get install wget

RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# Install node.js

RUN  curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.8/install.sh | bash \
    && export NVM_DIR="$HOME/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && nvm install node && nvm use node \
    && npm cache clean -f && npm install -g n && n stable && npm install cross-env

RUN apt-get update && npm install -g yarn

RUN composer global require hirak/prestissimo

WORKDIR /usr/src/app

COPY app/ /usr/src/app

RUN composer install

RUN yarn install

RUN PATH=$PATH:/usr/src/apps/vendor/bin:bin

RUN chown -R www-data:www-data /usr/src/app/var/log /usr/src/app/var/cache/dev/doctrine

# RUN bin/console doctrine:schema:update

# RUN bin/console doctrine:fixtures:load