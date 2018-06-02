<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
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
    const TABLE = 'shipping_files';

    const COLUMNS = [
        'id',
        'assignment',
        'counter',
        'notes',
        'update',
        'stamp',
    ];

    const STAMP_COLUMNS = [
        'update' => 'auto',
        'stamp' => 'auto',
    ];

    const OPTIONAL_COLUMNS = [
        'notes',
    ];

    const FOREIGN_KEYS = [
        'assignment' => [
            Assignment::class,
            'id'
        ],
    ];

    /**
     * Returns a Iterator of Title models related to this object
     *
     * @return Medools\ModelIterator
     */
    public function getTitles()
    {
        return Title::getIterator(['shipping_file' => $this->id]);
    }
}
