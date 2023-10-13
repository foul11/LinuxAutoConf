<?php
namespace Menu;
use Abstracts\AMenu;
use Arturka\CLI\Debug;
use ExeptionApp;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\CliMenu;

class Remote extends AMenu{
    const TITLE = 'Remote';
    
    function __construct($conf, $menu, $parent) {
        parent::__construct($conf, $menu, $parent);
        
        foreach ($conf->get('remote', []) as $key => $host) {
            $prettyName = $host['name'] ?? $host['ip'] ?? 'unknown';
            $menu->addItem(f('[%s] %s', $key + 1, $prettyName), function(CliMenu $menu) use($host, $prettyName) {
                $ssh = $this->connectSSH($host['ip'], $host['port'] ?? 22, $host['password'] ?? '');
                $this->installApp($ssh);
                $this->ssh2_run_stdout($ssh, "chmod +x /usr/local/bin/linux_auto_conf && /usr/local/bin/linux_auto_conf --title $prettyName");
            });
        }
        
        $menu->addLineBreak(' ');
        $menu->addLineBreak('-');
        $menu->addItem('[B]ack', new GoBackAction());
    }
    
    function connectSSH($host, $port, $password) { // todo: other auth methods (private key)
        $ssh = ssh2_connect($host, $port);
        
        if (!is_resource($ssh))
            throw new ExeptionApp("failed connect to $host");
        
        $methods = ssh2_auth_none($ssh, 'root');
        
        if ($methods === true)
            return $ssh;
        
        if (in_array('password', $methods))
            if (ssh2_auth_password($ssh, 'root', $password))
                return $ssh;
        
        if (in_array('agent', $methods))
            if (ssh2_auth_agent($ssh, 'root'))
                return $ssh;
        
        throw new ExeptionApp('failed auth in ssh');
    }
    
    function ssh2_run($ssh2, $cmd, &$out = null, &$err = null, $debug = true) {
        $result = false;
        $out = '';
        $err = '';
        
        $term = $this->builder->getTerminal();
        
        /** @var mixed | resource */
        $sshout = ssh2_exec($ssh2, $cmd, 'xterm', null, $term->getWidth(), $term->getHeight());
        
        if ($sshout) {
            $ssherr = ssh2_fetch_stream($sshout, SSH2_STREAM_STDERR);
            
            if ($ssherr) {
                # we cannot use stream_select() with SSH2 streams
                # so use non-blocking stream_get_contents() and usleep()
                if (
                    stream_set_blocking($sshout, false) and
                    stream_set_blocking($ssherr, false)
                ) {
                    $result = true;
                    # loop until end of output on both stdout and stderr
                    $wait = 0;
                    
                    while (!feof($sshout) or !feof($ssherr)) {
                        # sleep only after not reading any data
                        if ($wait)
                            usleep($wait);
                        
                        $wait = 50000; # 1/20 second
                        
                        if (!feof($sshout)) {
                            $one = stream_get_contents($sshout);
                            
                            if ($one === false) {
                                $result = false;
                                break;
                            }
                            
                            if ($one != '') {
                                $out .= $one;
                                $wait = 0;
                                
                                if ($debug)
                                    echo $one;
                            }
                        }
                        
                        if (!feof($ssherr)) {
                            $one = stream_get_contents($ssherr);
                            
                            if ($one === false) {
                                $result = false;
                                break;
                            }
                            
                            if ($one != '') {
                                $err .= $one;
                                $wait = 0;
                                
                                if ($debug)
                                    echo $one;
                            }
                        }
                    }
                }
                
                # we need to wait for end of command
                stream_set_blocking($sshout, true);
                stream_set_blocking($ssherr, true);
                # these will not get any output
                stream_get_contents($sshout);
                stream_get_contents($ssherr);
                fclose($ssherr);
            }
            
            fclose($sshout);
        }
        
        return $result;
    }
    
    function ssh2_run_stdout($ssh2, $cmd) {
        $term = $this->builder->getTerminal();
        
        /** @var mixed | resource */
        $sshout = ssh2_exec($ssh2, $cmd, 'xterm', null, $term->getWidth(), $term->getHeight());
        
        if ($sshout) {
            $ssherr = ssh2_fetch_stream($sshout, SSH2_STREAM_STDERR);
            
            if ($ssherr) {
                if (
                    stream_set_blocking($sshout, false) and
                    stream_set_blocking($ssherr, false) and
                    stream_set_blocking(STDIN  , false)
                ) {
                    $wait = 0;
                    
                    while (!feof($sshout) or !feof($ssherr)) {
                        if ($wait)
                            usleep($wait);
                        
                        $wait = 10000; # 1/100 second
                        
                        if (!feof($sshout)) {
                            $one = stream_get_contents($sshout);
                            
                            if ($one === false)
                                break;
                            
                            if ($one != '') {
                                echo $one;
                                $wait = 0;
                            }
                        }
                        
                        if (!feof($ssherr)) {
                            $one = stream_get_contents($ssherr);
                            
                            if ($one === false)
                                break;
                            
                            if ($one != '') {
                                echo $one;
                                $wait = 0;
                            }
                        }
                        
                        if (!feof(STDIN)) {
                            $one = stream_get_contents(STDIN);
                            
                            if ($one === false) {
                                break;
                            }
                            
                            if ($one != '') {
                                fwrite($sshout, $one);
                                $wait = 0;
                            }
                        }
                    }
                }
                
                stream_set_blocking($sshout, true);
                stream_set_blocking($ssherr, true);
                stream_set_blocking(STDIN  , true);
                
                stream_get_contents($sshout);
                stream_get_contents($ssherr);
                fclose($ssherr);
            }
            
            fclose($sshout);
        }
    }
    
    function installApp($ssh) {
        $tmpPahr = 'temp_upload.phar';
        $base = __DIR__ . '/../../';
        
        if (file_exists($tmpPahr))
            unlink($tmpPahr);
        
        $ait = new \AppendIterator();
        $ait->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . 'vendor', \FilesystemIterator::SKIP_DOTS)));
        $ait->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . 'src', \FilesystemIterator::SKIP_DOTS)));
        $ait->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . 'conf.d', \FilesystemIterator::SKIP_DOTS)));
        $ait->append(new \OneIterator(new \SplFileInfo($base . 'config.php')));
        $ait->append(new \OneIterator(new \SplFileInfo($base . 'linterHacks.php')));
        $ait->append(new \OneIterator(new \SplFileInfo($base . 'main.php')));
        
        if (!\Phar::canWrite())
            throw new ExeptionApp('[Phar] no write access, change the "phar.readonly" option to "Off" in "php.ini"');
        
        $phar = new \Phar($tmpPahr, 0);
            $phar->startBuffering();
            $phar->buildFromIterator($ait, $base);
            $phar->setStub(<<<STUB
            #!/usr/bin/env php
            <?php
            Phar::interceptFileFuncs();
            Phar::mapPhar('linux_auto_conf.phar');
            require('phar://linux_auto_conf.phar/main.php');
            __HALT_COMPILER();
            STUB);
        $phar->stopBuffering();
        
        $this->ssh2_run($ssh, 'php -v', $out, $err, false);
        
        if (strpos($err, 'command not found') !== false || version_compare(preg_match_first('/PHP (\d+\.\d+\.\d+)/', $out), '8.2', '<')) {
            Debug::notice('Version is not suitable, pls wait install...');
            
            $this->ssh2_run($ssh, <<<CMD
            apt install -y \
            lsb-release gnupg2 \
            ca-certificates \
            apt-transport-https \
            software-properties-common && \
            add-apt-repository ppa:ondrej/php && \
            apt update && \
            apt install -y php8.2
            CMD);
        } else {
            Debug::notice('Version is suitable');
        }
        
        $needExt = [
            'yaml',
            'mbstring',
            'readline',
            'ssh2',
            'inotify',
            'xml',
            'curl',
            'intl',
            'zstd',
            'bz2',
            'lz4',
        ];
        
        $this->ssh2_run($ssh, 'php -m', $out, $err, false);
        $installedExt = explode("\n", str_replace("\r", '', strtolower($out)));
        $needInstall = [];
        
        foreach ($needExt as $ext) {
            if (!in_array($ext, $installedExt)) {
                $needInstall[] = $ext;
            }
        }
        
        if ($needInstall) {
            Debug::notice('Install php modules: ' . implode(', ', $needInstall));
            $this->ssh2_run($ssh, 'apt install -y ' . implode(' ', array_map(fn($ext) => 'php8.2-' . $ext, $needInstall)));
        }
        
        ssh2_scp_send($ssh, $tmpPahr, '/usr/local/bin/linux_auto_conf');
    }
}