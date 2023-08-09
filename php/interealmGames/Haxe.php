<?php

namespace InterealmGames;

use \Array_hx; // Some Haxe php compilation will need to be autoloaded for this

class Haxe {
    static public function toHaxe($value, $version = 4) {
        switch($version) {
            case 3: return self::toHaxeV3($value);
            default: return self::toHaxeV4($value);
        }
    }

    static public function toHaxeV3($value) {
        return $value;
    }

    static public function toHaxeV4($value) {
        $converted = $value;

        if(is_array($value)) {
            // requires PHP 8.1
            if (array_is_list($value)) {
                $converted = Array_hx::wrap(array_map('self::toHaxeV4', $value));
            } else {
                $converted = new \haxe\ds\StringMap();
                $converted->data = $value;
            }
        }

        return $converted;
    }

    static public function toPhp($value, $version = 4) {
        switch($version) {
            case 3: return self::toPhpV3($value);
            default: return self::toPhpV4($value);
        }
    }

    static public function toPhpV3($value) {
        $converted = $value;
        if(!is_bool($value) && !is_numeric($value) && !is_string($value) && $value !== null) {
            switch(get_class($value)) {
                case '_hx_array' :
                    $converted = array_map(array('Haxe','toPhpV3'), $value->a);
                    break;
                case '_hx_anonymous' :
                    //$converted = array_map(array('Haxe','toPhpV3'), $value->a);
                    $converted = [];
                    foreach($value as $k => $v) {
                        $converted[$k] = self::toPhpV3($v);
                    }
                    break;
            }
        }
        return $converted;
    }

    static public function toPhpV4($value) {
        $converted = $value;
	
        if(!is_bool($value) && !is_numeric($value) && !is_string($value) && !is_callable($value) && $value !== null) {
            if(is_array($value)) {
                if(array_key_exists('arr', $value)) {
                    $converted = array_map(array('self','toPhpV4'), $value['arr']);
                }
            } else {
                switch(get_class($value)) {
                    case 'Array_hx' :
                        $converted = array_map(array('self','toPhpV4'), $value->arr);
                        break;
                    //case 'php\_Boot\HxAnon' :
                    case 'haxe\ds\StringMap' :
                        $converted = (array)$value->data;
                    default :
                        if(property_exists($value, 'arr')) {
                            $converted = array_map(array('self','toPhpV4'), $value->arr);
                        } elseif(property_exists($value, 'data')) {
                            $converted = (array)$value->data;
                        } else {
                            //$converted = array_map(array('Haxe','toPhpV4'), $value->a);
                            $converted = new \stdClass();
                            foreach($value as $k => $v) {
                                $converted->$k = self::toPhpV4($v);
                            }
                        }
                        break;
                }
            }
        }
        return $converted;
    }
}
