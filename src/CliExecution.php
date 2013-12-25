<?php

require_once("bootstrap.php");

class CliExecution implements Execution {

    public function execute(AsyncTask $task) {
        $command_line = 'php "' . realpath(__FILE__) . '" at_exec ' . $task->getIdentifier();
        if ($this->isWindows()) {
            trigger_error('AsyncTask.PHP is not compatible with Windows', E_USER_ERROR);
        } else {
            $command_line .= ' > /dev/null 2>&1 &';
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