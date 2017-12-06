#!/bin/bash
if [ -f '/etc/redhat-release' ]; then
  export USERNAME='apache'
elif python -mplatform | grep -qi Ubuntu; then
  export USERNAME='www-data'	
fi
sudo -u $USERNAME php -d include_path=".:/var/www/common" cron.php
