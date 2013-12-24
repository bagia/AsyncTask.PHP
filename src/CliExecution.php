<?php

require_once("AsyncTask.php");

class CliExecution implements Execution {

    public function execute(AsyncTask $task) {
        $command_line = 'php "' . realpath(__FILE__) . '" at_exec ' . $task->getIdentifier();
        if ($this->isWindows()) {
            pclose(popen("start /B " . $command_line, "r"));
        } else {
            $command_line .= ' > /dev/null 2>&1 &';
            exec($command_line);
        }
    }

    protected function isWindows() {
        return stripos(PHP_OS, 'win') !== FALSE;
    }

}

if (php_sapi_name() == "cli" && $argc > 1 && $argv[1] == "at_exec") {
    set_time_limit(0);
    echo "Trying to start...\n";
    $identifier = $argv[2];
    $task = AsyncTask::get($identifier);
    $task->syncExecute();
}