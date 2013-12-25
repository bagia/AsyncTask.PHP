<?php

require_once('../src/bootstrap.php');

if (isset($argv[1])) {
    $identifier = $argv[1];
    echo "Task: {$identifier}\n";
    $task = AsyncTask::get($identifier);
    echo "Progress: {$task->getProgress()}\n";
    foreach($task->getOutput() as $output) {
        echo $output."\n";
    }
    exit;
}

$task = new AsyncTask();
$task->addStep(function() {
    $fp = fopen('C:\\temp\\log2.txt', 'w+');
    fwrite($fp, "Start\n");
    echo "Sleeping for 5 seconds...\n";
    sleep(20);
    echo "Done.\n";
    fwrite($fp, "End\n");
    fclose($fp);
});
$task->addStep(function() {
    echo "Begin step 2\n";
    sleep(5);
    echo "End step 2\n";
});
$task->addStep(function() {
    echo "Begin step 3\n";
    sleep(5);
    echo "End step 3\n";
});
$task->addStep(function() {
    echo "Begin step 4\n";
    sleep(5);
    echo "End step 4\n";
});
$task->addStep(function() {
    echo "Begin step 5\n";
    sleep(5);
    echo "End step 5\n";
});
$task->addStep(function() {
    echo "Begin step 6\n";
    sleep(5);
    echo "End step 6\n";
});
$task->addStep(function() {
    echo "Begin step 7\n";
    sleep(5);
    echo "End step 7\n";
});
$task->addStep(function() {
    echo "Begin step 8\n";
    sleep(5);
    echo "End step 8\n";
});

$task->autoDelete();
echo $task->start();
echo "\n";

