<?php

require_once("AsyncTask.php");

class CliExecution implements Execution {

    public function execute(AsyncTask $task) {
        $command_line = 'php "' . realpath(__FILE__) . '" at_exec ' . $task->getIdentifier() . ' &';
        shell_exec($command_line);
    }

    protected function isWindows() {
        return stripos(PHP_OS, 'win') !== FALSE;
    }

}

if (php_sapi_name() == "cli" && $argc > 1 && $argv[1] == "at_exec") {
    $identifier = $argv[2];
    $task = AsyncTask::get($identifier);
    $task->syncExecute();
}