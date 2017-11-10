<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'wallets';

    const COLUMNS = [
        'id',
        'symbol',
        'name',
        'febraban',
        'operation',
    ];
}
