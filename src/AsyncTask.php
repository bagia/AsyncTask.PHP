<?php

require_once('SerializableClosure.php');
require_once('FilePersistence.php');
require_once('CliExecution.php');

define('ASYNC_INIT', 0);
define('ASYNC_RUNNING', 1);
define('ASYNC_DONE', 2);

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
        $task = unserialize($persistence->read());
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

        if ($this->_state == ASYNC_DONE)
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

    protected function _persist() {
        $this->_persistence->write(serialize($this));
    }
}

interface Persistence {
    public function __construct($identifier = '');
    public function getIdentifier();
    public function write($data);
    public function read();
}

interface Execution {
    public function execute(AsyncTask $task);
}