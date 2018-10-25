FROM php:7.1.22-fpm

RUN rm /etc/apt/preferences.d/no-debian-php && apt-get update \
    && apt-get install -y --no-install-recommends vim curl sudo debconf git apt-transport-https apt-utils \
    build-essential locales acl wget zip unzip bsdtar libaio1 \
    gnupg gnupg1 gnupg2 \
    php-soap php-zip

COPY php.ini /etc/php/7.1.22/php.ini
COPY php-fpm-pool.conf /etc/php/7.1.22/pool.d/www.conf

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
	mv composer.phar /usr/local/bin/composer

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m

RUN rm -rf /var/lib/apt/lists/*
RUN echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

WORKDIR /home/wwwroot/

RUN apt-get update && apt-get install -y libxml2-dev && docker-php-ext-install soap


# Add PostgreSQL for SDI
RUN apt-get update && apt-get install -y libpq-dev \
   && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
   && docker-php-ext-install pdo pdo_pgsql pgsql

# Add nodejs and yarn for SDI UI
RUN wget -qO- https://deb.nodesource.com/setup_8.x | sudo -E bash - \
   && apt-get update && apt-get install -y nodejs

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add - \
   && echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list \
   && apt-get update && apt-get install -y yarn

EXPOSE 9000
CMD ["php-fpm"]
