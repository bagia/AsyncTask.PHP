<?php

require_once('../src/bootstrap.php');

if (isset($argv[1])) {
    $identifier = $argv[1];
    echo "Task: {$identifier}\n";
    $task = AsyncTask::get($identifier);
    //echo "Progress: {$task->getProgress()}\n";
    while(!$task->isDone()) {
        echo $task->getNewOutput($cursor);
        sleep(1);
        $task->refresh();
    }
    exit;
}

// If no arg define and start the task
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
})->addStep(function() {
    echo "Begin step 4\n";
    sleep(5);
    echo "End step 4\n";
})->addStep(function() {
    echo "Begin step 5\n";
    sleep(5);
    echo "End step 5\n";
})->addStep(function() {
    echo "Begin step 6\n";
    sleep(5);
    echo "End step 6\n";
})->addStep(function() {
    echo "Begin step 7\n";
    sleep(5);
    echo "End step 7\n";
})->addStep(function() {
    echo "Begin step 8\n";
    sleep(5);
    echo "End step 8\n";
})->autoDelete();

echo $task->start();

echo "\n";

