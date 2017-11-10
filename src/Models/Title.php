<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * A Title represents something a Payer got from an Assignor.
 *
 * It might be one or products/services
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Title extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'titles';

    const COLUMNS = [
        'id',
        'assignor',
        'payer',          // Who the Title is destined
        'guarantor',      // Someone that would be charged if the Payer could not pay
        'specie',
        'our_number',
        'status',
        'doc_type',
        'kind',
        'value',          // (float)
        'iof',            // (float) A Brazilian tax
        'rebate',         // (float)
        'fine_type',
        'fine_date',
        'fine_value',     // (float)
        'discount_type',
        'discount_date',
        'discount_value', // (float)
        'description',
        'due',            // Must be between 1997-10-07 and 2025-02-21, inclusives; or should be empty/with a message
        'stamp',          // When Title was generated
        'update',
    ];

    const OPTIONAL_COLUMNS = [
        'guarantor',
        'status',
        'doc_type',
        'fine_type',
        'fine_date',
        'fine_value',
        'discount_type',
        'discount_date',
        'discount_value',
        'stamp',
        'update',
    ];

    const FOREIGN_KEYS = [
        'assignor' => [
            __NAMESPACE__ . '\Assignor',
            'id'
        ],
        'payer' => [
            __NAMESPACE__ . '\Payer',
            'id'
        ],
        'guarantor' => [
            __NAMESPACE__ . '\Payer',
            'id'
        ],
        'specie' => [
            __NAMESPACE__ . '\Specie',
            'id'
        ],
    ];
}
