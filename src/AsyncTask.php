<?php

/**
 * Class AsyncTask
 *
 * @brief Container for time consuming tasks to be executed in background
 * @author bagia
 * @license MIT
 */
class AsyncTask {

    protected $_steps = array();
    protected $_currentStep = -1;
    protected $_persistence;
    protected $_output = array();
    protected $_state = ASYNC_INIT;
    protected $_auto_delete = FALSE;
    protected $_dependencies = array();
    protected $_exception = NULL;

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

    /**
     * Retrieve an existing task.
     * If the object could not be unserialized,
     * an empty task is created and set to the
     * ASYNC_DELETED state.
     * @param $identifier Identifier of the task
     * @return AsyncTask
     */
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

    /**
     * Add a new step to the asynchronous task
     * @param $step - Must be a closure
     * @return $this
     */
    public function addStep($step) {
        if ($step instanceof Closure)
            $step = new SerializableClosure($step);

        if (!($step instanceof SerializableClosure))
            trigger_error('Step must be a closure or a SerializableClosure object', E_ERROR);

        $this->_steps[] = $step;

        return $this;
    }

    /**
     * Start asynchronously the task.
     * @return string - The identifier of the task
     */
    public function start() {
        // async start and then return identifier
        $this->_persist();
        $execution = self::_newExecution();
        $execution->execute($this);
        return $this->getIdentifier();
    }

    /**
     * @return string - The identifier of the task
     */
    public function getIdentifier() {
        return $this->_persistence->getIdentifier();
    }

    /**
     * Get the progress of the task in % of the number
     * of steps completed.
     * @return int - Between 0 and 100
     */
    public function getProgress() {
        if ($this->_state == ASYNC_INIT)
            return 0;

        if ($this->_state == ASYNC_DONE || $this->_state == ASYNC_DELETED)
            return 100;

        return floor(100 * ($this->_currentStep + 1) / count($this->_steps));
    }

    /**
     * Execute the task synchronously.
     * @return $this
     * @throws Exception
     */
    public function syncExecute() {
        $this->_state = ASYNC_RUNNING;
        $this->_persist();

        foreach($this->_steps as $step_index => $step) {
            if ($step_index < $this->_currentStep)
                continue;

            $this->_currentStep = $step_index;
            ob_start();
            try {
                $step(); // execute the closure
            } catch(\Exception $exception) {
                $this->_setException(new \Exception($exception->getMessage(), $exception->getCode()));
                $this->_state = ASYNC_CRASHED;
                $this->_persist();
                throw $exception;
            }

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

    /**
     * Check if the task failed to execute entirely.
     * @return mixed - FALSE if the task succeeded, the exception raised if the task failed.
     */
    public function didFail() {
        if (!is_null($this->_exception))
            return $this->_exception;

        return FALSE;
    }

    /**
     * Check if the task is finished running.
     * @return bool
     */
    public function isDone() {
        return $this->_state >= ASYNC_DONE;
    }

    /**
     * Refresh the object from its persisted state.
     * @return $this
     */
    public function refresh() {
        $updated_object = self::get($this->getIdentifier());
        foreach(get_object_vars($updated_object) as $name => $value) {
            $this->{$name} = $value;
        }

        return $this;
    }

    /**
     * Get new output since last call
     * @param $cursor - Cursor use to locate last output returned
     * @return string - Output of a step or empty string
     */
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

    /**
     * @return array - Array of the outputs of all the steps
     */
    public function getOutput() {
        return $this->_output;
    }

    /**
     * ASYNC_INIT - The task hasn't been started yet
     * ASYNC_RUNNING - The task is being executed
     * ASYNC_DONE - Execution is over
     * ASYNC_CRASHED - The execution failed
     * ASYNC_DELETED - The task has been deleted
     * @return int - State of the task
     */
    public function getState() {
        return $this->_state;
    }

    /**
     * Delete the task from the persistence layer.
     * @return $this
     */
    public function delete() {
        $this->_persistence->delete();
        $this->_state = ASYNC_DELETED;

        return $this;
    }

    /**
     * Automatically delete the task after its execution is over.
     * The task won't be deleted if the task crashes.
     * @return $this
     */
    public function autoDelete() {
        $this->_auto_delete = TRUE;

        return $this;
    }

    /**
     * Add a dependency for inclusion before executing the task
     * @param $file_name
     */
    public function addDependency($file_name, $deprecated_param = '') {
        // former signature was addDependency($class_name, $file_name)
        if (!empty($deprecated_param))
            $file_name = $deprecated_param;

        $this->_dependencies[] = realpath($file_name);
    }

    /**
     * @return array
     */
    public function getDependencies() {
        return $this->_dependencies;
    }

    /**
     * @return Exception|null
     */
    public function getException() {
        return $this->_exception;
    }

    protected function _setException(\Exception $exception) {
        $this->_exception = $exception;
    }

    protected function _persist() {
        $this->_persistence->write(serialize($this));
    }
}
