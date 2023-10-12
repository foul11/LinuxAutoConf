<?php

class OneIterator extends IteratorIterator {
    private $obj;
    private bool $valid = true;
    
    function __construct($obj) {
        $this->obj = $obj;
    }
    
    #[\ReturnTypeWillChange]
    function key(){
        return 0;
    }
    
    #[\ReturnTypeWillChange]
    function current(){
        return $this->obj;
    }
    
    #[\ReturnTypeWillChange]
    function next(){
        $this->valid = false;
    }
    
    #[\ReturnTypeWillChange]
    function rewind(){
        $this->valid = true;
    }
    
    #[\ReturnTypeWillChange]
    function valid(){
        return $this->valid;
    }
}