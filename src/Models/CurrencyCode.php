<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * The currency code used by each Bank in the Shipping Files
 *
 * It was made necessary because banks have different codes, and it was
 * invalidating the Shipping Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class CurrencyCode extends Medools\Model
{
    const TABLE = 'currency_codes';

    const COLUMNS = [
        'currency',
        'bank',
        'billet',
        'cnab240',
        'cnab400',
    ];

    const PRIMARY_KEY = ['currency', 'bank'];

    const FOREIGN_KEYS = [
        'currency' => [
            __NAMESPACE__ . '\\Currency',
            'id'
        ],
        'bank' => [
            __NAMESPACE__ . '\\Bank',
            'id'
        ],
    ];
}
