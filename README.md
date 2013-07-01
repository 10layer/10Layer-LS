10Layer-LS
==========

10Layer Version 2 (LS) is a Content Management System designed to be extremely customisable. It seperates the CMS from the front-end, and uses a RESTful API to feed the front-end with JSON data. Data types can be designed, prototyped and built quickly, essentially giving you all the benefits of a completely custom CMS without the hassle of actually building one.

Requirements
============

- Web server (Apache recommended)
- PHP 5.x
- ImageMagick
- MongoDB
- MongoDB PHP drivers

License
=======

[MIT License](http://opensource.org/licenses/MIT)

Installation
============

This assumes a totally clean Debian or Ubuntu server. We've called ours test.10layer.com and is available on that address on the internet. All commands are root.

First we fix this annoying language bug in Debian
	
	export LC_ALL="en_US.UTF-8"
	locale-gen

We make sure our system is up-to-date
	
	apt-get update
	apt-get upgrade

We configure the system for our time zone, or for GMT
	
	dpkg-reconfigure tzdata

We generate a public and private key
	
	ssh-keygen

We install the prerequisites. Choose "Internet Site" for the Postfix setup.
	
	apt-get install git-core mongodb-server php5 apache2 php5-dev php-pear build-essential postfix imagemagick
	
We install the Mongo drivers for PHP. In Debian, this should be available as a package, so you could alternatively apt-get install php-mongo.
	
	pecl install mongo
	echo "extension=mongo.so" > /etc/php5/apache2/conf.d/mongo.ini

We enable the Rewrite module in Apache
	
	a2enmod rewrite

We use the .htaccess file in /var/www, so we need to change AllowOverride None to AllowOverride All for /var/www:
	
	pico /etc/apache2/sites-enabled/000-default
	
	<Directory /var/www/>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All 
		Order allow,deny
		allow from all
	</Directory>

Restart Apache to ensure the changes take effect
	
	apache2ctl restart
	
Download 10Layer LS
	
	cd /var
	rm -rf www
	git clone git@github.com:10layer/10Layer-LS.git www

Visit http://test.10layer.com and follow the instructions.