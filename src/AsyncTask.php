<?php

spl_autoload_register(function($class) {
   require_once("{$class}.php");
});

define('ASYNC_INIT', 0);
define('ASYNC_RUNNING', 1);
define('ASYNC_DONE', 2);
define('ASYNC_DELETED', 3);

class AsyncTask {

    protected $_steps = array();
    protected $_currentStep = -1;
    protected $_persistence;
    protected $_output = array();
    protected $_state = ASYNC_INIT;

    public function __construct() {
        $this->_persistence = self::_newPersistence();
    }

    protected  static function _newPersistence($identifier = '') {
        // TODO: add dependency injection and other modes of persistence
        return new FilePersistence($identifier);
    }

    protected static function _newExecution() {
        // TODO: add dependency injection and other modes of execution
        return new CliExecution();
    }

    public static function get($identifier) {
        $persistence =  self::_newPersistence($identifier);
        try {
            $task = unserialize($persistence->read());
        } catch(Exception $exception) {
            $task = new AsyncTask();
            $task->_state = ASYNC_DELETED;
        }
        return $task;
    }

    public function addStep(SerializableClosure $step) {
        $this->_steps[] = $step;
    }

    public function start() {
        // async start and then return identifier
        $this->_persist();
        $execution = self::_newExecution();
        $execution->execute($this);
        return $this->getIdentifier();
    }

    public function getIdentifier() {
        return $this->_persistence->getIdentifier();
    }

    public function getProgress() {
        if ($this->_state == ASYNC_INIT)
            return 0;

        if ($this->_state == ASYNC_DONE || $this->_state == ASYNC_DELETED)
            return 100;

        return floor(100 * $this->_currentStep / count($this->_steps));
    }

    public function syncExecute() {
        $this->_state = ASYNC_RUNNING;
        $this->_persist();

        foreach($this->_steps as $step_index => $step) {
            if ($step_index < $this->_currentStep)
                continue;

            $this->_currentStep = $step_index;
            ob_start();
            $step(); // execute the closure
            $output = ob_get_contents();
            $this->_output[$step_index] = $output;
            ob_end_flush();

            $this->_persist();
        }

        $this->_state = ASYNC_DONE;
        $this->_persist();
    }

    public function getOutput() {
        return $this->_output;
    }

    public function getState() {
        return $this->_state;
    }

    public function delete() {
        $this->_persistence->delete();
        $this->_state = ASYNC_DELETED;
    }

    protected function _persist() {
        $this->_persistence->write(serialize($this));
    }
}

interface Persistence {
    public function __construct($identifier = '');
    public function getIdentifier();
    public function write($data);
    public function read();
    public function delete();
}

interface Execution {
    public function execute(AsyncTask $task);
}