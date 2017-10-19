FROM php:7.0-apache
RUN apt-get update && apt-get install -y python3 \
        python3-pip git locales cron sqlite3 
RUN locale-gen fr_FR.UTF-8  
ENV LANG fr_FR.UTF-8  
ENV LANGUAGE fr_FR:en  
ENV LC_ALL fr_FR.UTF-8  



RUN apt-get install -y libxml2-dev libxslt1-dev libz-dev
COPY src/requirements.txt /var/www
COPY src/create_db.py /var/www
WORKDIR /var/www
RUN pip3 install -r requirements.txt
WORKDIR /var/www/slack/slack
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
           php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"  && \
	php composer-setup.php && \
	php -r "unlink('composer-setup.php');"

COPY config/php.ini /usr/local/etc/php/
COPY config/apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN mkdir /var/www/slack/db
RUN /usr/bin/sqlite3 /var/www/slack/db/resources.db
RUN python3 /var/www/create_db.py
RUN chown -R www-data:www-data /var/www/slack/
RUN php composer.phar require google/apiclient:^2.0
RUN python3 /var/www/create_db.py 
COPY src/ /var/www/slack
RUN python3 /var/www/slack/slack/kaamelott_quotes_to_db.py

