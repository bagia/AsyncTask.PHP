<?php

interface Persistence {
    public function __construct($identifier = '');
    public function getIdentifier();
    public function write($data);
    public function read();
    public function delete();
}
