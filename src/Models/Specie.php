<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
class Specie extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'species';

    const COLUMNS = [
        'id',
        'symbol',
        'name',
        'name_plural',
        'febraban',
        'decimals',
        'thousand',
        'decimal',
    ];

    const OPTIONAL_COLUMNS = [
        'name_plural',
    ];

    /**
     * Outputs a value formated to this model
     *
     * @param number $value  Some monetary value to be formated
     * @param string $format If should prepend 'symbol' or append 'name', or if
     *                       should return without any ('raw')
     *
     * @return string
     */
    public function format($value, $format = 'symbol')
    {
        $formatted = number_format(
            $value,
            $this->get('decimals'),
            $this->get('decimal'),
            $this->get('thousand')
        );

        switch ($format) {
            case 'name':
                $name = $this->get('name_plural');
                if (!$name || (float) $value === 1.0) {
                    $name = $this->get('name');
                }
                return $formatted . ' ' . $name;
                break;

            case 'raw':
                return $formatted;
                break;

            case 'symbol':
            default:
                return $this->get('symbol') . ' ' . $formatted;
                break;
        }
    }
}
