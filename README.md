AsyncTask.PHP
=============
This library helps you plan, start, and follow the progress of time-consuming tasks in PHP.

Example
=======
```php
<?php
require_once('AsyncTask.PHP/src/bootstrap.php');

if (isset($_GET['task_id'])) {
    $identifier = $_GET['task_id'];

    $task = AsyncTask::get($identifier);
    echo "Progress: {$task->getProgress()}\n";
    
    if ($task->isDone()) {
      echo "Done executing task.\n";
    }
    
    exit;
}

// If no task_id was passed as a parameter, define and start the task
$task = new AsyncTask();
$task->addStep(function() {
    echo "Begin step 1\n";
    sleep(5);
    echo "End step 1\n";
})->addStep(function() {
    echo "Begin step 2\n";
    sleep(5);
    echo "End step 2\n";
})->addStep(function() {
    echo "Begin step 3\n";
    sleep(5);
    echo "End step 3\n";
});
// Delete the temporary file at the end of the execution
$task->autoDelete();

// Start the task asynchronously and echo the task identifier to the console
echo $task->start();

echo "\n";


```

Licensing
=========
This software is released under the MIT License.

Troubleshooting
===============
- The ```system``` function must NOT be disabled
- The script is relying on files written in the /tmp directory (or the system default temporary directory if /tmp doesn't exist). Some distributions using systemd are executing services inside a sandbox that creates private tmp folders inside the /tmp directory. This system must be disabled to get AsyncTask.PHP to work.
If using systemd, you must disable the ```PrivateTmp``` clause of the PHP service. Example:

```ini
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
