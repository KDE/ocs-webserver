# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = '2'

@script = <<SCRIPT

BOX_USER="vagrant"
BOX_DBPASS="vagrant"

debconf-set-selections <<< "mysql-server mysql-server/root_password password $BOX_DBPASS"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $BOX_DBPASS"

# Install dependencies
apt-get update
apt-get install -y apache2 git curl php7.0 php7.0-bcmath php7.0-bz2 php7.0-cli php7.0-curl php7.0-intl php7.0-json php7.0-mbstring php7.0-opcache php7.0-soap php7.0-xml php7.0-xsl php7.0-zip libapache2-mod-php7.0 php-mysql
apt-get install -y memcached php-memcache
apt-get install -y mysql-server mysql-client php-xdebug

# Configure XDebug
echo '
zend_extension=xdebug.so

xdebug.remote_enable=true
xdebug.remote_connect_back=true
xdebug.idekey=vagrant ' > /etc/php/7.0/mods-available/xdebug.ini

if [ ! -d /var/www/public ]; then
    mkdir /var/www/public
fi

# Configure Apache
echo '<VirtualHost *:80>
	DocumentRoot /var/www/public
	AllowEncodedSlashes On

	<Directory /var/www/public>
		Options +Indexes +FollowSymLinks
		DirectoryIndex index.php index.html
		Order allow,deny
		Allow from all
		AllowOverride All
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf
a2enmod rewrite
a2enmod env
service apache2 restart

sudo -s mysql --user="root" --password="$BOX_DBPASS" -e 'grant all on *.* to vagrant@localhost identified by "vagrant" with grant option;'
sudo service mysql restart
mysql --user="$BOX_USER" --password="$BOX_DBPASS" < /var/www/scripts/sql/init_schema.sql

if [ -e /usr/local/bin/composer ]; then
    /usr/local/bin/composer self-update
else
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Reset home directory of vagrant user
if ! grep -q "cd /var/www" /home/vagrant/.profile; then
    echo "cd /var/www" >> /home/vagrant/.profile
fi

echo "** [ZF] Run the following command to install dependencies, if you have not already:"
echo "    vagrant ssh -c 'composer install'"
echo "** [ZF] Visit http://localhost:8080 in your browser for to view the application **"
SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = 'bento/ubuntu-16.04'
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.synced_folder '.', '/var/www'
  config.vm.provision 'shell', inline: @script

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--name", "pling-ocs-api - Ubuntu 16.04"]
  end
end
