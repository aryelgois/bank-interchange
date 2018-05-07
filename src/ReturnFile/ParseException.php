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
    public static function pregMismatch(
        string $cnab,
        array $registries,
        int $line
    ) {
        $message = "Invalid registry at line $line. Expecting "
            . Format::naturalLanguageJoin(array_keys($registries), 'or')
            . " ($cnab)";

        return new self($message);
    }
}
