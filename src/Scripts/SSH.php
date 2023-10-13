<?php
namespace Scripts;
use Abstracts\AScripts;
use Arturka\CLI\Debug;

class SSH extends AScripts {
    function update() {
        parent::update();
        
        if (\Shell::exec('sshd -t', $out))
            return Debug::error(implode("\n", $out));
        
        if (\Shell::serviceReload('sshd'))
            return Debug::error('failed restart sshd');
        
        Debug::notice('Update success');
    }
    
    function checkVersion() : ?string {
        if (\Shell::exec('ssh -V 2>&1', $out))
            return Debug::error('failed check sshd version');
        
        return preg_match_first('/OpenSSH_((?:\d+\.?)+)/ms', implode("\n", $out));
    }
}