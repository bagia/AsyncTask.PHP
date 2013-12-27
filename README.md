AsyncTask.PHP
=============
Asynchronous task handler in PHP.

Licensing
=========
This software is realed under the MIT License.

Known issues
============
If using systemd, you must disable the ```PrivateTmp``` clause of the PHP service. Example:
```
[Unit]
Description=The PHP FastCGI Process Manager
After=syslog.target network.target

[Service]
Type=notify
PIDFile=/run/php-fpm/php-fpm.pid
PrivateTmp=false
ExecStart=/usr/bin/php-fpm --nodaemonize --pid /run/php-fpm/php-fpm.pid
ExecReload=/bin/kill -USR2 $MAINPID

[Install]
WantedBy=multi-user.target
```
