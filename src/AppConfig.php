<?php
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Noodlehaus\Exception\EmptyDirectoryException;
use Noodlehaus\Exception\FileNotFoundException;
use Noodlehaus\Parser\ParserInterface;

class AppConfig extends Config {
    protected function getDefaults() {
        return [
            'include' => [],
            'scripts' => [],
            'remote' => [],
        ];
    }
    
    public function merge(ConfigInterface $config) {
        $this->data = array_merge_recursive($this->data, $config->all());
        $this->cache = [];
        
        return $this;
    }
    
    protected function loadFromString($configuration, ParserInterface $parser) {
        $this->data = [];

        // Try to parse string
        $this->data = array_merge_recursive($this->data, $parser->parseString($configuration));
    }
    
    protected function loadFromFile($path, ParserInterface $parser = null) {
        $paths      = $this->getValidPath($path);
        $this->data = [];
        
        foreach ($paths as $path) {
            if ($parser === null) {
                // Get file information
                $info      = pathinfo($path);
                $parts     = explode('.', $info['basename']);
                $extension = array_pop($parts);

                // Skip the `dist` extension
                if ($extension === 'dist') {
                    $extension = array_pop($parts);
                }

                // Get file parser
                $parser = $this->getParser($extension);

                // Try to load file
                $this->data = array_merge_recursive($this->data, $parser->parseFile($path));

                // Clean parser
                $parser = null;
            } else {
                // Try to load file using specified parser
                $this->data = array_merge_recursive($this->data, $parser->parseFile($path));
            }
        }
    }
    
    /** @inheritDoc */
    protected function getValidPath($path) {
        // If `$path` is array
        if (is_array($path)) {
            return $this->getPathFromArray($path);
        }

        // If `$path` is a directory
        if (is_dir($path)) {
            $paths = iterator_to_array(new RegexIterator(new FilesystemIterator($path), '/^.+\/.*\..*$/')); // glob($path . '/*.*');
            
            if (empty($paths)) {
                throw new EmptyDirectoryException("Configuration directory: [$path] is empty");
            }

            return $paths;
        }

        // If `$path` is not a file, throw an exception
        if (!file_exists($path)) {
            throw new FileNotFoundException("Configuration file: [$path] cannot be found");
        }

        return [$path];
    }
}