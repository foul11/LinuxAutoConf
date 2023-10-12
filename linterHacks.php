<?php

if (function_exists("1yaml1_")) {
    // class resource {}
    
    function yaml_parse($any){ echo $any; return []; }
    function yaml_emit($any){ return ''; }
    
     /** @param resource $session */
    function ssh2_auth_agent($session, string $username): bool { return false; };
    function ssh2_auth_hostbased_file(
        $session,
        string $username,
        string $hostname,
        string $pubkeyfile,
        string $privkeyfile,
        string $passphrase = null,
        string $local_username = null
    ): bool { return 0; };
     /** @param resource $session */
    function ssh2_auth_none($session, string $username): mixed { return 0; };
     /** @param resource $session */
    function ssh2_auth_password($session, string $username, string $password): bool { return 0; };
    function ssh2_auth_pubkey_file(
        $session,
        string $username,
        string $pubkeyfile,
        string $privkeyfile,
        string $passphrase = null
    ): bool { return 0; };
    /** @return resource|false */
    function ssh2_connect(
        string $host,
        int $port = 22,
        array $methods = [],
        array $callbacks = []
    ) { return 0; };
     /** @param resource $session */
    function ssh2_disconnect($session): bool { return 0; };
    /**
     * @param resource $session
     * @return resource|false
     * */
    function ssh2_exec(
        $session,
        string $command,
        string $pty = null,
        array $env = null,
        int $width = 80,
        int $height = 25,
        int $width_height_type = SSH2_TERM_UNIT_CHARS
    ) { return 0; };
    function ssh2_fetch_stream(\resource $channel, int $streamid): mixed { return 0; };
     /** @param resource $session */
    function ssh2_fingerprint($session, int $flags = SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX): string { return 0; };
    /** @return resource|false */
    function ssh2_forward_accept(\resource $listener) { return 0; };
    function ssh2_forward_listen(
        $session,
        int $port,
        string $host = '',
        int $max_connections = 16
    ): \resource|false { return 0; };
     /** @param resource $session */
    function ssh2_methods_negotiated($session): array { return []; };
    function ssh2_poll(array &$desc, int $timeout = 30): int { return 0; };
    function ssh2_publickey_add(
        \resource $pkey,
        string $algoname,
        string $blob,
        bool $overwrite = false,
        array $attributes = []
    ): bool { return 0; };
     /** @param resource $session @return resource|false */
    function ssh2_publickey_init($session) { return 0; };
    function ssh2_publickey_list(\resource $pkey): array { return []; };
    function ssh2_publickey_remove(\resource $pkey, string $algoname, string $blob): bool { return false; };
     /** @param resource $session */
    function ssh2_scp_recv($session, string $remote_file, string $local_file): bool { return false; };
    function ssh2_scp_send(
        $session,
        string $local_file,
        string $remote_file,
        int $create_mode = 0644
    ): bool { return false; };
    function ssh2_send_eof(\resource $channel): bool { return false; };
    function ssh2_sftp_chmod(\resource $sftp, string $filename, int $mode): bool { return false; };
    function ssh2_sftp_lstat(\resource $sftp, string $path): array { return []; };
    function ssh2_sftp_mkdir(
        \resource $sftp,
        string $dirname,
        int $mode = 0777,
        bool $recursive = false
    ): bool { return 0; };
    function ssh2_sftp_readlink(\resource $sftp, string $link): string { return ''; };
    function ssh2_sftp_realpath(\resource $sftp, string $filename): string { return ''; };
    function ssh2_sftp_rename(\resource $sftp, string $from, string $to): bool { return 0; };
    function ssh2_sftp_stat(\resource $sftp, string $path): array { return []; };
    function ssh2_sftp_symlink(\resource $sftp, string $target, string $link): bool { return 0; };
    function ssh2_sftp_unlink(\resource $sftp, string $filename): bool { return 0; };
     /**
      * @param resource $session
      * @return resource|false
     */
    function ssh2_sftp($session) { return 0; };
    /** @param resource $session @return resource|false */
    function ssh2_shell(
        $session,
        string $termtype = "vanilla",
        ?array $env = null,
        int $width = 80,
        int $height = 25,
        int $width_height_type = SSH2_TERM_UNIT_CHARS
    ) { return 0; };
    
    /** @param resource $session */
    function ssh2_tunnel($session, string $host, int $port): mixed { return 0; };
    define('SSH2_STREAM_STDERR', '');
}
