<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

use aryelgois\Utils\Format;

/**
 * A registry could not be parsed from a registry type pattern
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ParseException extends \RuntimeException
{
    /**
     * The Parser could not match a registry
     *
     * @param string $config     Contains CNAB and Bank code
     * @param array  $registries Tried registry types
     * @param int    $line       Return file line
     *
     * @return self
     */
    public static function pregMismatch(
        string $config,
        array $registries,
        int $line
    ) {
        $message = "Invalid registry at line $line: expecting "
            . Format::naturalLanguageJoin(array_keys($registries), 'or')
            . " (CNAB$config)";

        return new self($message);
    }
}
