<?php
namespace Scripts;
use Abstracts\AScripts;
use Arturka\CLI\Debug;
use PhpSchool\CliMenu\MenuItem\SelectableItem;

class SSH extends AScripts {
    function update() {
        parent::update();
        
        if (\Shell::exec('sshd -t', $out))
            return Debug::error(implode("\n", $out));
        
        $authkeys = [];
        $authkeys_file = $this->conf->get('scripts.SSH.authorized_keys_path', "{$_SERVER['HOME']}/.ssh/authorized_keys");
        
        if (file_exists($authkeys_file))
            $authkeys = explode("\n", file_get_contents($authkeys_file));
        
        $keys = $this->conf->get('scripts.SSH.public_keys', []);
        
        foreach ($authkeys as $key => $val) {
            if (strpos($val, '[linux_auto_conf]') !== false) {
                if (($kkey = array_search(substr($val, 0, -17), $keys)) !== false) {
                    unset($keys[$kkey]);
                } else {
                    unset($authkeys[$key]);
                }
            }
        }
        
        $authkeys = array_values($authkeys);
        
        foreach ($keys as $val) {
            $authkeys[] = $val . '[linux_auto_conf]';
        }
        
        file_put_contents($authkeys_file, implode("\n", $authkeys));
        chmod($authkeys_file, 0600);
        
        if (\Shell::serviceReload('sshd'))
            return Debug::error('failed restart sshd');
        
        Debug::notice('Update success');
    }
    
    function getMenuElements($RemoveDisable = true) {
        return [ new SelectableItem('Update conf and keys', [$this, 'update']) ];
    }
    
    function checkVersion() : ?string {
        if (\Shell::exec('ssh -V 2>&1', $out))
            return Debug::error('failed check sshd version');
        
        return preg_match_first('/OpenSSH_((?:\d+\.?)+)/ms', implode("\n", $out));
    }
}