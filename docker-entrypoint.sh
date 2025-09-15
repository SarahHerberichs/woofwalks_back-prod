#!/bin/bash
set -e

echo "Debugging entrypoint script..." >> /proc/1/fd/1
echo "PORT is: $PORT" >> /proc/1/fd/1
echo "www.conf content before change:" >> /proc/1/fd/1
cat /usr/local/etc/php-fpm.d/www.conf >> /proc/1/fd/1

# Check if the listen line exists before attempting to replace it.
if grep -q "listen = 9000" "/usr/local/etc/php-fpm.d/www.conf"; then
  sed -i "s/9000/$PORT/" /usr/local/etc/php-fpm.d/www.conf
  echo "Successfully replaced port 9000 with $PORT." >> /proc/1/fd/1
else
  echo "Listen port 9000 not found in www.conf. Using default listener." >> /proc/1/fd/1
fi
echo "=== Vérification des permissions ==="
ls -la /var/www/html/var
echo "==================================="

echo "www.conf content after change:" >> /proc/1/fd/1
cat /usr/local/etc/php-fpm.d/www.conf >> /proc/1/fd/1

exec "$@"