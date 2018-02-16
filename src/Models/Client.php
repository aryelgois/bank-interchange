<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * Someone who pays for something
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Client extends Medools\Model
{
    const TABLE = 'clients';

    const COLUMNS = [
        'id',
        'person',
        'address',
        'assignor',
    ];

    const FOREIGN_KEYS = [
        'person' => [
            __NAMESPACE__ . '\\Person',
            'id'
        ],
        'address' => [
            __NAMESPACE__ . '\\FullAddress',
            'id'
        ],
        'assignor' => [
            __NAMESPACE__ . '\\Assignor',
            'id'
        ],
    ];
}
