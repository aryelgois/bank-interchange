<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange;

use aryelgois\objects;
use VRia\Utils\NoDiacritic;

/**
 * Useful methods for this package
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
abstract class Utils
{
    /**
     * Adds trailing spaces to a value and trims overflow
     *
     * @param string  $val Value to be formatted
     * @param integer $len Maximum characters allowed
     *
     * @return string
     */
    public static function padAlfa($val, $len)
    {
        return strtoupper(substr(str_pad(NoDiacritic::filter($val), $len), 0, $len));
    }
    
    /**
     * Adds leading zeroes to a value
     *
     * @param string  $val  Value to be formatted
     * @param integer $len  Maximum digits length allowed
     * @param boolean $trim If left digits should be trimmed (disable throw)
     *
     * @return string
     *
     * @throws LengthException If $val overflows
     */
    public static function padNumber($val, $len, $trim = false)
    {
        $result = str_pad($val, $len, '0', STR_PAD_LEFT);
        if (strlen($result) > $len) {
            if ($trim) {
                return substr($result, - $len);
            } else {
                throw new \LengthException('Value overflows maximum length');
            }
        }
        return $result;
    }
    
    /**
     * Formats a Person Document
     *
     * @param Person  $person Person object whose document will be formatted
     * @param integer $len    Document length to be padded (not returned length)
     *
     * @return string
     */
    public static function formatDocument(objects\Person $person, $len = 14)
    {
        return $person->document['type'] . self::padNumber($person->document['number'], $len);
    }
}
