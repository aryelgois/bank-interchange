<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * Creates a relation of Titles to Shipping Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ShippingFileMovements extends Medools\Model
{
    const TABLE = 'shipping_file_movements';

    const COLUMNS = [
        'id',
        'bank',
        'cnab',
        'code',
        'name',
    ];

    const FOREIGN_KEYS = [
        'bank' => [
            __NAMESPACE__ . '\\Bank',
            'id'
        ],
    ];
}
