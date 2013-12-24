<?php

require_once('../src/AsyncTask.php');

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
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 1\n";
    sleep(5);
    echo "End step 1\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 2\n";
    sleep(5);
    echo "End step 2\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 3\n";
    sleep(5);
    echo "End step 3\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 4\n";
    sleep(5);
    echo "End step 4\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 5\n";
    sleep(5);
    echo "End step 5\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 6\n";
    sleep(5);
    echo "End step 6\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 7\n";
    sleep(5);
    echo "End step 7\n";
}));
$task->addStep(new SerializableClosure(function() {
    echo "Begin step 8\n";
    sleep(5);
    echo "End step 8\n";
}));

$task->autoDelete();
echo $task->start();
echo "\n";

