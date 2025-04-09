<?php

namespace app\helpers;

use Stringable;

// see init.php
// $V = function ($v) { return Utils::strval($v); };



class Utils
{
    /**
     * testProd Teste si environnement de production
     *
     * @return true|false true si environnement de prod
     */
    public static function testProd()
    {
        return (YII_ENV != 'dev' && YII_ENV != 'test') || \str_contains(\strtolower(YII_ENV), 'prod');
    }

    /**
     * filter_trim Vérifie une chaîne de caractère (non-null/non-vide) et trim
     * avec preg_replace
     *
     * @param  string $str
     * @return false|string false si null ou chaîne vide
     */
    public static function filter_trim(string $str): false|string
    {
        if (is_null($str)) return false;
        $ret = $str;
        $ret = preg_replace('/^\\s+|\\s+$/u', '', $ret);
        if ($ret == '') return false;

        return $ret;
    }
    public static function cleanRawData(string|null $data): array
    {
        if (\is_null($data)) return [];
        $ret = $data;
        $ret = str_replace("\n", ';', $ret);
        $ret = str_replace("\r", ';', $ret);
        $ret = str_replace("\t", ';', $ret);
        $ret = str_replace("\v", ' ', $ret);
        $ret = str_replace("\f", ' ', $ret);
        $ret = str_replace("\0", ' ', $ret);
        $ret = str_replace('#', ';', $ret);
        $ret = str_replace('/', ';', $ret);
        $ret = str_replace('\\', ';', $ret);
        $ret = str_replace(',', ';', $ret);
        $ret = str_replace('.', ';', $ret);
        $ret = str_replace('[[:space:]]*', ' ', $ret);
        $var = explode(';', $ret);
        \array_walk($var, 'app\helpers\Utils::filter_trim');
        return $var;
    }

    public static function intminmax(&$value, int|null $min, int|null $max): int
    {
        $value = intval($value);
        if ($min != null && $max != null) {
            return $value = min(max($value, $min), $max);
        }
        if ($min == null && $max != null) {
            return $value = min($value, $max);
        }
        if ($max == null && $min != null) {
            return $value = max($value, $min);
        }
        //$min == null && $max == null
        return $value;
    }

    /*
    $V = function ($v) {
    return Utils::strval($v);
};
    */
    public static function strval($value)
    {
        if (is_array($value)) return \implode(',', $value);
        if ((!is_object($value) && settype($item, 'string') !== false))
            return \strval($value);
        if (is_object($item) && $value instanceof Stringable)
            return \strval($value);

        return var_export($value);
    }

    public static function var_dump_pre($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

    public static function str_var_dump($var, $pre = true): string
    {
        if (!ob_start()) {
            return 'output buffering not supported';
        }
        echo $pre ? '<pre>' : '';
        var_dump($var);
        echo $pre ? '</pre>' : '';
        return ob_get_clean();
    }

    public static function
    str_debug_print_backtrace($pre = true): string
    {
        if (!ob_start()) {
            return 'output buffering not supported';
        }
        echo $pre ? '<pre>' : '';
        debug_print_backtrace();
        echo $pre ? '</pre>' : '';
        return ob_get_clean();
    }


    static function mb_ucfirst(string $str, ?string $encoding = null): string
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, null, $encoding);
    }
}
