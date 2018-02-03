<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * A billing method used by the Bank
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Wallet extends Medools\Model
{
    const TABLE = 'wallets';

    const COLUMNS = [
        'id',
        'bank',
        'cnab',
        'code',
        'operation',
        'symbol',
        'name',
    ];

    const FOREIGN_KEYS = [
        'bank' => [
            __NAMESPACE__ . '\\Bank',
            'id'
        ],
    ];
}
