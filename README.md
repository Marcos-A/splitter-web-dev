# splitter-web
Split SAGA PDF reports ("butlletins") into student individual PDFs. Count passed and evaluated UF from SAGA PDF reports ("actes").

## Installation
Clone the repository into your web root folder.

Make sure `uploads/` and `script/tmp/` directories are web writable.

## Requirements
```
sudo apt install apache2
sudo apt install php7.4-cli
sudo apt install php libapache2-mod-php
sudo apt install default-jdk
sudo apt-get install python3	
sudo apt install python3-pip
pip3 install PyPDF2
pip3 install tika
```

## Apache configuration: VirtualHost
```
<VirtualHost *:80>
	ServerAdmin admin@avaluacions
	ServerName avaluacions
	ServerAlias avaluacions
	DocumentRoot /var/www/html/avaluacions/public
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## How to run
Start the server with:

`sudo systemctl start apache2`

Run the tika server script with:

`bash /var/www/html/avaluacions/script/tika_server_start.sh`

Setting up a cron job is recommended for the tika server script to run at server start up:

1. Run: `$ sudo crontab -e`
2. Select an editor
3. Insert at the end of the file: `@reboot bash /var/www/html/avaluacions/script/tika_server_start.sh`
4. Save and close the file. Then restart the server with: `$ sudo systemctl restart apache2`

Visit localhost:80 from your browser.
