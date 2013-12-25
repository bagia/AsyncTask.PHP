<?php

class AsyncTask {

    protected $_steps = array();
    protected $_currentStep = -1;
    protected $_persistence;
    protected $_output = array();
    protected $_state = ASYNC_INIT;
    protected $_auto_delete = FALSE;

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

    public function addStep($step) {
        if ($step instanceof Closure)
            $step = new SerializableClosure($step);

        if (!($step instanceof SerializableClosure))
            trigger_error('Step must be a closure or a SerializableClosure object', E_ERROR);

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

        if ($this->_auto_delete) {
            $this->delete();
        } else {
            $this->_state = ASYNC_DONE;
            $this->_persist();
        }
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

    public function autoDelete() {
        $this->_auto_delete = TRUE;
    }

    protected function _persist() {
        $this->_persistence->write(serialize($this));
    }
}
