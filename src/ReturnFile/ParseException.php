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
     * Which Registry types did not match
     *
     * @var string[]
     */
    protected $registries;

    /**
     * Creates a new ParseException object
     *
     * @param string   $config     Contains CNAB and Bank code
     * @param string[] $registries Tried registry types
     * @param int      $line       Return file line
     */
    public function __construct(
        string $config,
        array $registries,
        int $line,
        Throwable $previous = null
    ) {
        $this->registries = $registries;

        $message = "Invalid registry at line $line: expecting "
            . Format::naturalLanguageJoin($registries, 'or')
            . " (CNAB$config)";

        parent::__construct($message, 0, $previous);
    }
}
