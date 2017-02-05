# Stop unused services
sudo service mongod stop
sudo service postgresql stop

# Start PHP development server
cd /vagrant/public && php -S 0.0.0.0:8080 &
