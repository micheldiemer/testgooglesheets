<?php


namespace app\helpers;

class ArrayToString
{
    /**
     * Transforme récursivement un tableau php en chaîne de caractères
     * recursive_implode
     *
     * @param  array|null $data le tableau
     * @param  string $glue le caractère qui lie les données
     * @param  bool $include_keys inclure les clés ou non
     * @param  bool $trim_all supprimer systématiquement les blancs
     * @return string tableau fusionné (chaîne vide si erreur)
     */
    public static function toString(array|null $data, string $glue = ',', bool $include_keys = false, bool $trim_all = true): string
    {
        if ($data === null) return '';
        return self::recursive_implode($data, $glue, $include_keys, $trim_all);
    }

    /**
     * Recursively implodes an array with optional key inclusion
     * https://gist.github.com/jimmygle/2564610
     *
     * Example of $include_keys output: key, value, key, value, key, value
     *
     * @param   array   $array         multi-dimensional array to recursively implode
     * @param   string  $glue          value that glues elements together
     * @param   bool    $include_keys  include keys before their values
     * @param   bool    $trim_all      trim ALL whitespace from string
     * @return  string  imploded array
     */
    private static function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, $trim_all, &$glued_string) {
            $include_keys and $glued_string .= ($trim_all ? trim($key) : $key) . $glue;
            if ($value instanceof \DateTime):
                $glued_string .= $value->format('Y-m-D') . $glue;
            else:
                $glued_string .= ($trim_all ? trim($value) : $value) . $glue;
            endif;
        });

        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

        // Trim ALL whitespace
        // $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }
}
