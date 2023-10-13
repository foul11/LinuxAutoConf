<?php
namespace Config;
use Abstracts\AConfig;
use Arturka\CLI\Debug;

class KeyVal extends AConfig {
    protected $flatmap = [];
    protected $flatmap_rev = [];
    
    protected ?string $opt_nl = null;         // Разделитель новых строк, используется прасинге и генерации (1 символ)
    protected ?string $opt_delimiter = null;  // Разделитель между Key -> Value (Может быть регулярным выражением)
    protected ?string $opt_join = null;       // Строка для склейки Key [join] Value (если не задан равняется opt_delimiter)
    protected array $opt_comment = ['#'];     // Массив символов с которого начинается комментарий (строки не больше 1-ой длины)
    protected bool $opt_parse_quotes = false; // Автоматически подставлять и парсить двойные кавычки
    protected bool $opt_dup_parm_last = true; // Если true то если есть дублирующию ключи будет записан последний из них
    protected bool $opt_case_ignore = false;  // Игнорировать регистр ключа
    
    protected ?string $quote_chars = null;
    
    function __construct(&$opts) {
        $this->opts = $opts;
    }
    
    protected function parseOpts() {
        foreach ($this->opts as $key => $opt) {
            switch ($key) {
                case 'nl': $this->opt_nl = $opt; break;
                case 'delimiter': $this->opt_delimiter = $opt; break;
                case 'comment': $this->opt_comment = is_string($opt) ? [$opt] : $opt; break;
                case 'parse_quotes': $this->opt_parse_quotes = $opt; break;
                case 'dup_parm_last': $this->opt_dup_parm_last = $opt; break;
                case 'case_ignore': $this->opt_case_ignore = $opt; break;
                case 'join': $this->opt_join = $opt; break;
                
                default: throw new \Exception("Option '$key' not found");
            }
        }
        
        foreach ($this->opt_comment as $key => $char) {
            if (preg_match('/^\s+$/', $char)) {
                $this->opt_comment[$key] = '\s';
            }
        }
        
        if (!count($this->opt_comment))
            throw new \Exception('There must be at least one comment character');
        
        if (is_null($this->opt_delimiter))
            throw new \Exception('You need to pass the delimiter');
        
        if (is_null($this->opt_join))
            $this->opt_join = $this->opt_delimiter;
        
        $this->quote_chars = implode('', $this->opt_comment) . ' ';
    }
    
    static function newInstance(string &$source, $opts = []) {
        $clazz = parent::newInstance($source, $opts);
        
        if (!$clazz instanceof self)
            throw new \Exception('Why is there a different class here?');
        
        if ($clazz->opt_nl === null) {
            if (preg_match('/(\R)/', $source, $matches)) {
                $clazz->opt_nl = $matches[1];
            } else $clazz->opt_nl = "\n";
        }
        
        $clazz->parseOpts();
        $clazz->parseSource();
        
        return $clazz;
    }
    
    protected function buildRegex() {
        $d = $this->opt_delimiter;
        $c = preg_quote(implode('', $this->opt_comment), '/');
        $Ccr = "\r\n";
        $s = "[^\S$Ccr]";
        $RCmnt = "(?:[$c].*)?";
        
        // ^\s*(?:([#;].*)|(([^#;]+?)=(\s*".*"|[^#;\n]*)([#;].+)?))$
        return '/' . <<<REGEX
            ^$s*
                (?:
                    # (?<section>\[[^\]]*\]) $s* (?<comment>$RCmnt) | # ini sections
                      (?<comment>$RCmnt)
                    | (?:
                        (?<key>[^$c$Ccr]+?)
                        \s* $d $s*
                        (?:
                            "(?<value_quote>.*)"
                            | (?<value>(?:[^$c$Ccr\\\\\s]*[\s]??(?:\\\\.)*)*)
                        ) $s* (?<comment>$RCmnt)
                    )
                    | (?<error>.*)
                )
            $
        REGEX . '/Jimx';
    }
    
    protected function parseSource() {
        $this->flatmap = explode($this->opt_nl, $this->source);
        preg_match_all($this->buildRegex(), $this->source, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
        
        if ($this->flatmap[count($this->flatmap) - 1] === '') {
            $matches[] = [ '', 'error' => null, 'key' => null, 'value' => null, 'value_quote' => null ]; // Bug fix, regex striped last endline
        }
        
        foreach ($this->flatmap as $key => $fval) {
            unset($entry);
            $entry = $matches[$key];
            
            if ($fval !== $entry[0]){
                Debug::error(bin2hex($fval));
                Debug::error(bin2hex($entry[0]));
                
                throw new \Exception("Mismatched state between regular and lines: '{$fval}' !== '{$entry[0]}'");
            }
            
            if ($entry['error'] !== null)
                throw new \Exception('An error occurred during parsing');
            
            $this->flatmap[$key] = $entry;
            $entry = &$this->flatmap[$key];
            
            if ($entry['key'] === null)
                continue;
            
            if ($entry['value'] === null && $entry['value_quote'] === null)
                throw new \Exception('Strange situation: the key is there, but the meaning is lost');
            
            if ($this->opt_case_ignore)
                $entry['key'] = strtolower($entry['key']);
            
            if (!$this->opt_parse_quotes && $entry['value_quote'] !== null) {
                $entry['value'] = '"' . $entry['value_quote'] . '"';
                $entry['value_quote'] = null;
            }
            
            $conf_key = $entry['key'];
            $conf_value = $entry['value'] ?? $entry['value_quote'];
            
            if (!$this->opt_dup_parm_last && isset($this->store[$conf_key]))
                continue;
            
            $this->flatmap_rev[$conf_key] = $key;
            $this->store[$conf_key] = $conf_value;
        }
    }
    
    function __toString() {
        $out = [];
        
        foreach ($this->flatmap as $key => $entry) {
            if (isset($entry['_edited']) && $entry['_edited']) {
                $value = $entry['value'] ?? '"' . $entry['value_quote'] . '"';
                $comment = $entry['comment'] ?? '';
                $out[] = "{$entry['key']}{$this->opt_join}{$value}{$comment}";
            } else $out[] = $entry[0];
        }
        
        return implode($this->opt_nl, $out);
    }
    
    protected function _get(string $name) {
        if ($this->opt_case_ignore)
            $name = strtolower($name);
        
        return parent::_get($name);
    }
    
    protected function _set(string $name, $value) {
        if ($this->opt_case_ignore)
            $name = strtolower($name);
        
        $value = (string)$value;
        $this->store[$name] = $value;
        
        $ekey = $this->flatmap_rev[$name] ?? null;
        $quoted_idx = $this->opt_parse_quotes && strpbrk($value, $this->quote_chars) !== false ? 'value_quote' : 'value';
        
        if (isset($this->flatmap[$ekey]) && $this->flatmap[$ekey] !== null) {
            $this->flatmap[$ekey][$quoted_idx] = $value;
            $this->flatmap[$ekey]['_edited'] = true;
        } else {
            $len = array_push($this->flatmap, [ 'key' => $name, $quoted_idx => $value, '_edited' => true ]);
            $this->flatmap_rev[$name] = $len - 1;
        }
    }
    
    protected function _unset(string $name) {
        $this->set($name, null);
    }
    
    protected function _isset(string $name) {
        return $this->get($name) !== null;
    }
}