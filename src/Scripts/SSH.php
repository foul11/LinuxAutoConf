<?php
namespace Scripts;
use Abstracts\AScripts;
use Arturka\CLI\Debug;

class SSH extends AScripts {
    function install() {
        Debug::notice('Ya ystanavlivaucb');
    }
    
    function update() {
        Debug::notice('Ya update');
    }
    
    function remove() {
        Debug::notice('Ya remove');
    }
}