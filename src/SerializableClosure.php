<?php
/**
 * Class SerializableClosure
 *
 * @brief A serializable class than can store a Closure
 * @author bagia
 * @license MIT
 */
class SerializableClosure  {

    protected $_static_variables; // To store the values of the variables given in the use() clause
    protected $_code; // To store the code as a string
    protected $_file_name;
    protected $_start_line;
    protected $_end_line;

    protected $_closure; // To store the actual Closure object

    /**
     * Example: new SerializableClosure(function() { echo 'Hello world'; })
     * @param callable $closure A Closure object
     */
    public function __construct(Closure $closure) {
        $this->_closure = $closure;

        $reflection = $this->_getReflection();
        $this->_file_name = $reflection->getFileName();
        $this->_start_line = $reflection->getStartLine();
        $this->_end_line = $reflection->getEndLine();
    }

    /**
     * Automatically called when the object is invoked
     */
    public function __invoke() {
        $arguments = func_get_args();
        forward_static_call_array($this->_closure, $arguments);
    }

    /**
     * Called prior to serialization
     * @return array
     */
    public function __sleep() {
        $this->_saveStaticVariables();
        $this->_extractCode();
        return array('_static_variables', '_code', '_file_name', '_start_line', '_end_line');
    }

    /**
     * Called prior to unserialization
     */
    public function __wakeup() {
        // restore the original context
        foreach($this->_static_variables as $name => $value) {
            if (!isset($$name))
                $$name = $value;
        }

        // re-create the closure object
        $closure = NULL;
        $eval = '$closure = ' . $this->_code . ';';
        eval($eval);
        $this->_closure = $closure;
    }

    /**
     * Save the context of the closure
     */
    protected function _saveStaticVariables() {
        $static_variables = $this->_getReflection()->getStaticVariables();
        $this->_static_variables = $static_variables;
    }

    /**
     * Extracts the code of the closure from the file it is stored into.
     */
    protected function _extractCode() {
        // Get the actual code
        $source = file($this->_file_name);
        $start_index = $this->_start_line - 1;
        $end_index = $this->_end_line - 1;

        // narrow-down the code
        $source = array_slice($source, $start_index, $end_index - $start_index + 1);
        $source = implode("", $source);

        preg_match_all('#new\s*SerializableClosure\(\s*(.*)\)#ims', $source, $matches);
        $source = $matches[1][0];

        // now we need to count the number of parenthesis to remove extra-code
        $open = mb_substr_count($source, "(");
        $close = mb_substr_count($source, ")");
        if ($close > $open) {
            $diff = $close - $open;
            $explode = explode(")", $source);
            $explode = array_slice($explode, 0, -$diff);
            $source = implode(")", $explode);
        }

        $this->_code = $source;
    }

    protected $_reflection = NULL;
    protected function _getReflection() {
        if (is_null($this->_reflection)) {
            $this->_reflection = new ReflectionFunction($this->_closure);
        }
        return $this->_reflection;
    }
}