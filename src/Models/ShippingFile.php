<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * Group of Titles to be set to the Bank
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ShippingFile extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'shipping_files';

    const COLUMNS = [
        'id',
        'status',
        'stamp',
        'update',
    ];

    const OPTIONAL_COLUMNS = [
        'status',
        'stamp',
        'update',
    ];
}
