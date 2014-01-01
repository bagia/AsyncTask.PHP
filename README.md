AsyncTask.PHP
=============
This library helps you plan, start, and follow the progress of time-consuming tasks in PHP.

Concept
=========
- A task is composed of several steps.
- The progress is the % of steps completed.
- Steps are [closures](http://www.php.net/manual/en/class.closure.php "PHP: Closure - Manual").
- When ```start()``` is called on a task, an entirely new PHP process is spawned.

Example
=======
The following example is a web page that:
- If no task_id parameter is given in the URL:
    creates an Asynchronous task, and then starts it. 
- If a task_id parameter is present in the query string:
    shows the progress in % of steps completed of the associated task.

The task is configured to be automatically deleted from the persistence layer when done.

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
- The ```system``` function MUST be enabled
- The script is relying on files written in the ```/tmp``` directory (or the system default temporary directory if ```/tmp``` doesn't exist). Some distributions using ```systemd``` are executing services inside a temporary directory sandbox that creates isolated folders inside the /tmp directory for each service. This system must be disabled to get **AsyncTask.PHP** to work.
If using ```systemd```, you must disable the ```PrivateTmp``` clause of the PHP service. 

Example (```/usr/lib/systemd/system/php-fpm.service```):

```ini
[Unit]
...

[Service]
...
PrivateTmp=false

[Install]
...
```
