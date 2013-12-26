<?php

require_once("bootstrap.php");

class CliExecution implements Execution {

    public function execute(AsyncTask $task) {
        $file_name = escapeshellarg(realpath(__FILE__));
        if ($this->isWindows()) {
            $dir = realpath(__DIR__);
            $command_line = "{$dir}/startbg/bin/startbg.exe php {$file_name} at_exec {$task->getIdentifier()}";
            system($command_line);
        } else {
            $command_line = "php {$file_name} at_exec {$task->getIdentifier()} > /dev/null 2>&1 &";
            exec($command_line);
        }
    }

    protected function isWindows() {
        return stripos(PHP_OS, 'win') !== FALSE;
    }

}

if (php_sapi_name() == "cli" && isset($argv[1]) && $argv[1] == "at_exec") {
    set_time_limit(0);
    $identifier = $argv[2];
    $task = AsyncTask::get($identifier);
    $task->syncExecute();
}