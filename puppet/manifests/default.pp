class apache {
	
	exec { 
		'apt-get update':
			command => '/usr/bin/apt-get update'
	}
	->
	file {
		"/var/www":
			ensure => link,
			target => "/vagrant",
			notify => Service["apache2"],
			force => true,
	}
	->
	package {
		"apache2":
			ensure => present,
			require => Exec["apt-get update"],
	}
	->
	file {
   		"/etc/apache2/sites-available/default":
   			owner => root,
   			group => root,
   			mode => 0644,
   			source => "/vagrant/puppet/conf/default",
   			notify => Service["apache2"],
    }
	->
	service {
		"apache2":
			ensure => running,
			require => Package["apache2"],
	}

	package {
		"build-essential":
			ensure => present,
			require => Exec["apt-get update"],
	}
	
	package {
		"php5":
			ensure => present,
			notify => Service["apache2"],
			require => Exec["apt-get update"],
	}
	->
	package {
		"php-pear":
			ensure => present,
			require => Package["php5"],
	}
	->
	package {
		"php5-dev":
			ensure => present,
			require => Package["php5"],
	}
	->
	exec { 'pecl-mongo-install':
       command => '/usr/bin/pecl install mongo',
       unless => "/usr/bin/pecl info mongo",
       notify => Service['apache2'],
   }
   ->
   file {
   		"/etc/php5/conf.d/mongo.ini":
   			owner => root,
   			group => root,
   			mode => 0644,
   			source => "/vagrant/puppet/conf/mongo.ini",
   			notify => Service["apache2"],
   }

   file {
		"/etc/apache2/mods-enabled/rewrite.load":
			ensure => link,
			target => "/etc/apache2/mods-available/rewrite.load",
			notify => Service["apache2"],
			require => Package["apache2"],
	}
}

class imagemagick {
	package {
		"imagemagick":
			ensure => present,
			require => Exec["apt-get update"],
	}
}

class tenlayer {
	file {
		"/vagrant/content":
			ensure =>directory,
			mode => 0644,
			owner => "www-data",
	}
}

class locales {
	exec { 'locale-gen':
        command => "/usr/sbin/locale-gen en_US.utf8",
        unless => '/bin/grep -q "en_US.utf8" /etc/default/locale'
    }

    exec { 'export':
		command => '/bin/echo \'export LC_ALL="en_US.UTF-8"\' >> /home/vagrant/.bashrc',
		unless => '/bin/grep -q "export LC_ALL" /home/vagrant/.bashrc',
	}
}

class mongodb {
	
	exec {
		"mongodb-key":
			command => "/usr/bin/apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10",
			unless => "/usr/bin/apt-key list | grep 7F0CEB10",
	}
	->
	file {
		"/etc/apt/sources.list.d/10gen.list":
			source => "/vagrant/puppet/conf/10gen.list",
	}
	->
	Exec["apt-get update"]
	->
	package {
		"mongodb-10gen":
			require => Exec["apt-get update"],
			ensure => present,
	}
	->
	service {
		"mongodb-10gen":
			ensure => running,
			require => Package["mongodb-10gen"],
	}
	
}

include apache
include tenlayer
include locales
include mongodb
include imagemagick