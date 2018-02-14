<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * Identifies the title's billing kind
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class DocumentKind extends Medools\Model
{
    const TABLE = 'document_kinds';

    const COLUMNS = [
        'id',
        'bank',
        'cnab',
        'code',
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
