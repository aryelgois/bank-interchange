<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
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
    const TABLE = 'assignors';

    const COLUMNS = [
        'person',
        'url',
    ];

    const PRIMARY_KEY = ['person'];

    const AUTO_INCREMENT = null;

    const OPTIONAL_COLUMNS = [
        'url',
    ];

    const FOREIGN_KEYS = [
        'person' => [
            Person::class,
            'id'
        ],
    ];
}
