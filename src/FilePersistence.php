<?php

require_once("bootstrap.php");

class FilePersistence implements Persistence {

    protected $_file;

    public function __construct($identifier = '') {
        if (empty($identifier))
            $this->_file = tempnam("/tmp", "at");
        else
            $this->_file = $this->_getFile($identifier);
    }

    public function getIdentifier() {
        return base64_encode($this->_file);
    }

    public function write($data) {
        file_put_contents($this->_file, $data, LOCK_EX);
    }

    public function read() {
        if (!file_exists($this->_file) || !is_readable($this->_file))
            throw new Exception("File not found.");

        return file_get_contents($this->_file);
    }

    public function delete() {
        unlink($this->_file);
    }

    protected function _getFile($identifier) {
        return base64_decode($identifier);
    }
}