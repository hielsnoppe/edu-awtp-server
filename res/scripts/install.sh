composer install

# http://sabre.io/dav/caldav/#mysql
cat vendor/sabre/dav/examples/sql/mysql.* | mysql -u root -p sabredav -h 127.0.0.1
#cat res/sql/setup.sql | mysql -u root -p sabredav -h 127.0.0.1
