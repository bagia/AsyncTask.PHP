<?php

/**
 * Interface Execution
 *
 * @brief Interface of classes that can execute the task in the background.
 * @author bagia
 * @license MIT
 */
interface Execution {

    /**
     * Execute the task in the background.
     * @param AsyncTask $task
     * @return mixed
     */
    public function execute(AsyncTask $task);
}