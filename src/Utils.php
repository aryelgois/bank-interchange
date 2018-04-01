<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange;

use aryelgois\Utils\Validation;
use VRia\Utils\NoDiacritic;

/**
 * Useful methods for this package
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Utils
{
    /**
     * Checks if there is an extension and adds it if does not
     *
     * @param string $file      File name
     * @param string $extension Extension (dot included)
     *
     * @return string
     */
    public static function addExtension(string $file, string $extension)
    {
        if (substr($file, strlen($extension) * -1) !== $extension) {
            $file .= $extension;
        }
        return $file;
    }

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
     * @throws \LengthException If $val overflows
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
     * Converts a string to PascalCase
     *
     * @param string $string To be converted
     *
     * @return string
     */
    public static function toPascalCase(string $string)
    {
        return str_replace(
            [' ', '_', '.', '-'],
            '',
            ucwords(NoDiacritic::filter($string), ' _.-')
        );
    }
}
