<?php
namespace Config;
use Abstracts\AConfig;

class INI extends AConfig {
    protected $flatmap = [];
    protected $flatmap_rev = [];
    protected $insert = [];
    
    protected ?string $opt_nl = null;                  // Разделитель новых строк, используется прасинге и генерации (1 символ)
    protected string $opt_delimiter = '=';             // Разделитель между Key -> Value (Может быть больше чем 1 символ)
    protected array $opt_comment = ['#',';'];          // Массив символов с которого начинается комментарий (строки не больше 1-ой длины)
    protected bool $opt_parse_quotes = false;          // Автоматически подставлять и парсить двойные кавычки
    protected bool $opt_dup_parm_last = true;          // Если true то если есть дублирующию ключи будет записан последний из них
    protected bool $opt_case_ignore = false;           // Игнорировать регистр ключа
    protected bool $opt_zend_array = false;            // Массивы будут в стиле zend framwork: a[] = ... \n a[] = ...
    protected string $opt_array_delimiter = ',';       // Устанавливает разделитель для массивов, не работает вместе с zend_array
    protected bool $opt_ignore_section_error = false;  // Позволяет отключить ошибку для вложености секций, но их редактирование может привести к повреждению структуры INI-файла
                                                       //     ключи в них лучше все равно не редактировать
    protected ?bool $opt_dup_section_last = null;      // Что делать с дубликатами секций: null - merge, false - first, true - last
    
    protected ?string $quote_chars = null;
    protected ?string $re_Name = null;
    
    function __construct(&$opts) {
        $this->opts = $opts;
    }
    
    function parseOpts() {
        foreach ($this->opts as $key => $opt) {
            switch ($key) {
                case 'nl': $this->opt_nl = $opt; break;
                case 'delimiter': $this->opt_delimiter = $opt; break;
                case 'comment': $this->opt_comment = is_string($opt) ? [$opt] : $opt; break;
                case 'parse_quotes': $this->opt_parse_quotes = $opt; break;
                case 'dup_parm_last': $this->opt_dup_parm_last = $opt; break;
                case 'case_ignore': $this->opt_case_ignore = $opt; break;
                case 'zend_array': $this->opt_zend_array = $opt; break;
                case 'array_delimiter': $this->opt_array_delimiter = $opt; break;
                case 'ignore_section_error': $this->opt_ignore_section_error = $opt; break;
                
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
        
        $this->quote_chars = implode('', $this->opt_comment) . ' ' . $this->opt_array_delimiter;
        $this->re_Name = $this->buildRegex_Name();
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
                      (?<section>\[[^\]]*\]) $s* (?<comment>$RCmnt)  # ini sections
                    | (?<comment>$RCmnt)
                    | (?:
                        (?<key>[^$c]+?)
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
    
    protected function buildRegex_Array() {
        $d = preg_quote($this->opt_array_delimiter, '/');
        
        return '/' . <<<REGEX
            (?: \s*
                "(?<value_quote>(?:[^"\\\\]*(?:\\\\.)*)+)"
                | (?<value>(?:[^\\\\\s$d]+[\s]??(?:\\\\.)*)+)
            ) (?:\s* (?<comma>$d))?
        REGEX . '/Jimsx';
    }
    
    protected function buildRegex_Name() {
        return '/' . <<<REGEX
            ^\s*
                (?:
                    (?:
                        (?<section>\[[^\]]*\]) \s*
                        (?<key>.+?) \s*
                    )
                    | (?<error>.*)
                )
            $
        REGEX . '/Jisx';
    }
    
    protected function parseSource() {
        $regex_array = $this->buildRegex_Array();
        
        $this->flatmap = explode($this->opt_nl, $this->source);
        preg_match_all($this->buildRegex(), $this->source, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
        
        if ($this->flatmap[count($this->flatmap) - 1] === '')
            $matches[] = [ '', 'error' => null, 'section' => null, 'key' => null, 'value' => null, 'value_quote' => null ]; // Bug fix, regex striped last endline
        
        $currSection = '[]';
        
        foreach ($this->flatmap as $key => $fval) {
            unset($entry);
            $entry = $matches[$key];
            
            if ($fval !== $entry[0]){
                throw new \Exception("Mismatched state between regular and lines: '{$fval}' !== '{$entry[0]}'");
            }
            
            if ($entry['error'] !== null)
                throw new \Exception('An error occurred during parsing');
            
            $this->flatmap[$key] = $entry;
            $entry = &$this->flatmap[$key];
            
            if ($entry['section'] !== null) {
                if (!$this->opt_ignore_section_error && substr($entry['section'], 1, 1) === '.')
                    throw new \Exception('Nested sections are disabled, you can disable this exception with the option, but writing to these sections will be prohibited');
                
                if ($this->opt_dup_section_last !== null && isset($this->flatmap_rev[$currSection])) {
                    if ($this->opt_dup_section_last === true) {
                        $this->flatmap_rev[$currSection] = [];
                        $this->store[$currSection] = [];
                    } else continue;
                }
                
                $currSection = $entry['section'];
                
                if ($this->opt_case_ignore)
                    $currSection = strtolower($currSection);
            }
            
            if ($entry['key'] !== null) {
                if ($entry['value'] === null && $entry['value_quote'] === null)
                    throw new \Exception('Strange situation: the key is there, but the meaning is lost');
                
                if ($this->opt_case_ignore)
                    $entry['key'] = strtolower($entry['key']);
                
                if (!$this->opt_parse_quotes && $entry['value_quote'] !== null) {
                    $entry['value'] = '"' . $entry['value_quote'] . '"';
                    $entry['value_quote'] = null;
                }
                
                if (substr($entry[0], -1) === '\\')
                    throw new \Exception("Parser does not support line wrapping");
                
                if (!isset($this->flatmap_rev[$currSection]))
                    $this->flatmap_rev[$currSection] = [];
                
                if (!isset($this->store[$currSection]))
                    $this->store[$currSection] = [];
                
                $is_array = false;
                $conf_key = &$entry['key'];
                
                if (isset($entry['value'])) $conf_value = &$entry['value'];
                if (isset($entry['value_quote'])) $conf_value = &$entry['value_quote'];
                
                if ($this->opt_zend_array) {
                    if (substr($conf_key, -2) === '[]') {
                        $section = $this->flatmap_rev[$currSection];
                        $conf_key = substr($conf_key, 0, -2);
                        $is_array = true;
                        
                        if (isset($section[$conf_key]))
                            $this->flatmap[$section[$conf_key]] = null;
                    }
                } else {
                    if (preg_match_all($regex_array, $conf_value, $matches_array, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL) !== false) {
                        foreach ($matches_array as $arr_entry) {
                            if ($arr_entry['comma'] !== null)
                                $is_array = true;
                        }
                        
                        if ($is_array) {
                            $conf_value = [];
                            
                            foreach ($matches_array as $arr_entry) {
                                $conf_value[] = $arr_entry['value'] ?? $arr_entry['value_quote'];
                            }
                        }
                    }
                }
                
                if (!$this->opt_dup_parm_last && isset($this->store[$conf_key]))
                    continue;
                
                $this->flatmap_rev[$currSection][$conf_key] = $key;
                $this->store[$currSection][$conf_key] = $conf_value;
            }
        }
    }
    
    protected function entryToStr($entry) {
        if (is_array($entry['value'])) {
            $entry['value'] = implode($this->opt_array_delimiter, $entry['value']);
        }
        
        $value = $entry['value'] ?? '"' . $entry['value_quote'] . '"';
        $comment = $entry['comment'] ?? '';
        return "{$entry['key']}{$this->opt_delimiter}{$value}{$comment}";
    }
    
    function __toString() {
        $insert = $this->insert;
        $currSection = '[]';
        $out = [];
        
        foreach ($this->flatmap as $key => $entry) {
            if ($entry === null)
                continue;
            
            if (isset($entry['section']) && $entry['section'] !== null) {
                foreach ($insert[$currSection] ?? [] as $ins_entry) {
                    $out[] = $this->entryToStr($ins_entry);
                }
                
                unset($insert[$currSection]);
                $currSection = $entry['section'];
                $out[] = $entry[0];
            } else {
                if (isset($entry['_edited']) && $entry['_edited']) {
                    $out[] = $this->entryToStr($entry);
                } else $out[] = $entry[0];
            }
        }
        
        foreach ($insert as $insertCurrSection => $section) {
            if ($currSection != $insertCurrSection)
                $out[] = $insertCurrSection;
            
            foreach ($section as $key => $entry) {
                $out[] = $this->entryToStr($entry);
            }
        }
        
        return implode($this->opt_nl, $out);
    }
    
    protected function parseName(string $name) {
        if ($this->opt_case_ignore)
            $name = strtolower($name);
        
        if (preg_match($this->re_Name, $name, $matches, PREG_UNMATCHED_AS_NULL) === false)
            throw new \Exception("Regex error for get name: $name");
        
        if ($matches['error'] !== null)
            throw new \Exception("Name is incorrectly formed: $name");
        
        return [ $matches['section'], $matches['key'] ];
    }
    
    protected function _get(string $name) {
        [ $section, $key ] = $this->parseName($name);
        
        if (!isset($this->store[$section]))
            return null;
        
        return $this->store[$section][$key] ?? null;
    }
    
    protected function _set(string $name, $value) {
        [ $section, $key ] = $this->parseName($name);
        
        if (is_array($value)) {
            $value = array_map(fn($val) => (string)$val, $value);
        } else $value = (string)$value;
        
        if (!isset($this->store[$section])) {
            $this->store[$section] = [];
            $this->insert[$section] = [];
            $this->flatmap_rev[$section] = [];
        }
        
        $this->store[$section][$key] = $value;
        
        $ekey = $this->flatmap_rev[$section][$key] ?? null;
        $entry = null;
        
        if (isset($this->flatmap[$ekey]) && $this->flatmap[$ekey] !== null) {
            $entry = &$this->flatmap[$ekey];
        }
        
        if ($entry === null && isset($this->insert[$section][$key])) {
            $entry = &$this->insert[$section][$key];
        }
        
        $quoted_idx = $this->opt_parse_quotes && strpbrk($value, $this->quote_chars) !== false && !is_array($value) ? 'value_quote' : 'value';
        
        if ($entry !== null) {
            $entry[$quoted_idx] = $value;
            $entry['_edited'] = true;
        } else {
            $this->insert[$section][$key] = [ 'key' => $key, $quoted_idx => $value ];
        }
    }
    
    protected function _unset(string $name) {
        $this->set($name, null);
    }
    
    protected function _isset(string $name) {
        return $this->get($name) !== null;
    }
}