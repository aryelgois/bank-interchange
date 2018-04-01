<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * The definitions of money
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Currency extends Medools\Model
{
    const TABLE = 'currencies';

    const COLUMNS = [
        'id',
        'symbol',
        'name',
        'name_plural',
        'decimals',
        'thousand',
        'decimal',
    ];

    const OPTIONAL_COLUMNS = [
        'name_plural',
    ];

    /**
     * Outputs a value formatted to this model
     *
     * @param number $value  Some monetary value to be formatted
     * @param string $format If should prepend 'symbol' or append 'name', or if
     *                       should return without any ('raw'), or even ignore
     *                       masks ('nomask')
     *
     * @return string
     */
    public function format($value, string $format = null)
    {
        $formatted = number_format(
            $value,
            $this->decimals,
            $format === 'nomask' ? '' : $this->decimal,
            $format === 'nomask' ? '' : $this->thousand
        );

        switch ($format) {
            case 'name':
                $name = $this->name_plural;
                if (!$name || (float) $value === 1.0) {
                    $name = $this->name;
                }
                return $formatted . ' ' . $name;
                break;

            case 'raw':
            case 'nomask':
                return $formatted;
                break;

            case 'symbol':
            default:
                return $this->symbol . ' ' . $formatted;
                break;
        }
    }
}
