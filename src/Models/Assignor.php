<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;
use aryelgois\BankInterchange as BankI;

/**
 * It's who made a covenant with the Bank and has to emit bank billets.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Assignor extends Medools\Model
{
    const TABLE = 'assignors';

    const COLUMNS = [
        'id',
        'person',
        'address',
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
    ];
}
