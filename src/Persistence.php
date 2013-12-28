<?php

/**
 * Interface Persistence
 *
 * @brief Interface of classes that can persist the task.
 * @author bagia
 * @license MIT
 */
interface Persistence {
    /**
     * Initialize the persistence of a task or retrieve the
     * persistence of the task identified by the parameter.
     * @param string $identifier
     */
    public function __construct($identifier = '');

    /**
     * Identifier of the task
     * @return string
     */
    public function getIdentifier();

    /**
     * Persist the data of the task
     * @param $data
     */
    public function write($data);

    /**
     * Read the persisted data of the task
     * @return mixed
     */
    public function read();

    /**
     * Delete the persisted data of the task
     */
    public function delete();
}
