<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * A Bank has to keep our money safe!
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Bank extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'banks';

    const COLUMNS = [
        'id',
        'code', // Defined by a government entity, 3 digits
        'name', // Bank's name, 30 characters, left-padded with spaces
        'view', // Class name for Bank's Billet view
        'logo', // Absolut path to Bank's logo
        'tax',  // (float) Bank's tax for billets
    ];

    const OPTIONAL_COLUMNS = [
        'logo',
    ];
}
