<?php

class AsyncTask {

    protected $_steps = array();
    protected $_currentStep = -1;
    protected $_persistence;
    protected $_output = array();
    protected $_state = ASYNC_INIT;
    protected $_auto_delete = FALSE;
    protected $_dependencies;

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
        $task = FALSE;
        try {
            for($intents = 0; $task === FALSE && $intents < 3; $intents++) {
                $task = unserialize($persistence->read());
            }
            if ($task === FALSE)
                throw new Exception("Unable to unserialize persisted data.");
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

        return $this;
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

        return floor(100 * ($this->_currentStep + 1) / count($this->_steps));
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

        return $this;
    }

    public function isDone() {
        return $this->_state == ASYNC_DONE || $this->_state == ASYNC_DELETED;
    }

    public function refresh() {
        $updated_object = self::get($this->getIdentifier());
        foreach(get_object_vars($updated_object) as $name => $value) {
            $this->{$name} = $value;
        }

        return $this;
    }

    public function getNewOutput(&$cursor) {
        if (is_null($cursor))
            $cursor = -1;

        $intent_cursor = $cursor + 1;
        if (isset($this->_output[$intent_cursor])) {
            $cursor = $intent_cursor;
            return $this->_output[$cursor];
        }
        return "";
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

        return $this;
    }

    public function autoDelete() {
        $this->_auto_delete = TRUE;

        return $this;
    }

    public function addDependency($class_name, $file_name) {
        $this->_dependencies[$class_name] = realpath($file_name);
    }

    public function getDependencies() {
        return $this->_dependencies;
    }

    protected function _persist() {
        $this->_persistence->write(serialize($this));
    }
}
