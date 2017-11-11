<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * It's who made a covenant with the Bank and has to emit bank billets.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Assignor extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'assignors';

    const COLUMNS = [
        'id',
        'person',
        'address',
        'bank',
        'wallet',
        'covenant',   // Covenant provided by the Bank. Max 20 digits, but should have up to 6
        'agency',     // Bank Agency. max 5 digits
        'agency_cd',  // check digit
        'account',    // Bank Account. max 12 digits
        'account_cd', // check digit
        'edi',        // EDI code informed by the Bank
        'logo',       // Absolut path to Assignor's logo
        'url',        // URL to be embeded into Assignor's logo, in the Bank Billet
    ];

    const OPTIONAL_COLUMNS = [
        'logo',
        'url',
    ];

    const FOREIGN_KEYS = [
        'person' => [
            '\aryelgois\Medools\Models\Person',
            'id'
        ],
        'address' => [
            __NAMESPACE__ . '\FullAddress',
            'id'
        ],
        'bank' => [
            __NAMESPACE__ . '\Bank',
            'id'
        ],
        'wallet' => [
            __NAMESPACE__ . '\Wallet',
            'id'
        ],
    ];
}
