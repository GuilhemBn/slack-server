FROM php:7.0-apache
RUN apt-get update && apt-get install -y python3 \
        python3-pip git

COPY config/php.ini /usr/local/etc/php/
COPY config/apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY src/ /var/www/slack

WORKDIR /var/www/slack/slack

RUN apt-get install -y libxml2-dev libxslt1-dev libz-dev
RUN pip3 install -r requirements.txt
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
           php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"  && \
	php composer-setup.php && \
	php -r "unlink('composer-setup.php');"
RUN php composer.phar require google/apiclient:^2.0
