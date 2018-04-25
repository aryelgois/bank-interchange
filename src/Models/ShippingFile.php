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
        'status',
        'notes',
        'update',
        'stamp',
    ];

    const STAMP_COLUMNS = [
        'update' => 'auto',
        'stamp' => 'auto',
    ];

    const OPTIONAL_COLUMNS = [
        'status',
        'notes',
    ];

    const FOREIGN_KEYS = [
        'assignment' => [
            __NAMESPACE__ . '\\Assignment',
            'id'
        ],
    ];

    /**
     * Returns a Iterator of ShippingFileTitle models for this object
     *
     * @return Medools\ModelIterator
     */
    public function getShippedTitles()
    {
        return ShippingFileTitle::getIterator(['shipping_file' => $this->id]);
    }
}
