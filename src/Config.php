<?php

namespace inix;

class Config {
    static private $file;
    static private $filecontents;
    static private $loaded = false;
    static private $sectioned;
    static public $config = [];
    
    static public function load(string $file, bool $sectioned = true) {
      if(self::$loaded) return true;
      if(!file_exists($file)) return false;
      
      self::$file = $file;
      self::$filecontents = file_get_contents($file);
      self::$sectioned = $sectioned;
      self::$config = parse_ini_string(self::$filecontents, $sectioned, INI_SCANNER_TYPED);
      self::$loaded = true;
      
      return true;
    }
    
    static public function get($config) {
        if(strpos($config, ".")) {
            return self::$config[strstr($config, ".", true)][substr(strstr($config, "."), 1)] ?? null;
        }

        return self::$config[$config] ?? null;
    }
    
    static public function set($config, $value): bool {
        if(is_array($value)) return false; // todo: add support for writing arrays
        $writevalue = $value;

        // boolean write fix
        if($value === false) $writevalue = "false";
        elseif($value === true) $writevalue = "true";

        // setting a value in a section
        if(strpos($config, ".") !== false && self::$sectioned) {
            $domain = strstr($config, ".", true);
            $key = substr(strstr($config, "."), 1);

            if(!isset(self::$config[$domain])) {
                self::$config[$domain] = [];
                self::$filecontents .= "\n\n[$domain]\n$key = $writevalue\n";
            } else {
                self::$filecontents = preg_replace("/\[{$domain}\](.*)?{$key}( ?)\=( ?)([^\n;]+)/s", "[{$domain}]$1{$key}$2=$3$writevalue ", self::$filecontents);
            }

            self::$config[$domain][$key] = $value;

        // setting an unsectioned value (could still be parsed as sectioned though)
        } else {
            // value not found
            if(!isset(self::$config[$config])) {
                self::$filecontents = "$config = $writevalue\n" . self::$filecontents; // add to beginning where there is no section

            // value found, only replace first occurrence
            } else {
                self::$filecontents = preg_replace("/{$config}( ?)\=( ?)([^\n;]+)/s", "{$config}$1=$2$writevalue ", self::$filecontents, 1);
            }

            self::$config[$config] = $value;
        }

        file_put_contents(self::$file, self::$filecontents);
        return true;

    }
  }