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
        'cnab',
        'status',
        'update',
        'stamp',
    ];

    const STAMP_COLUMNS = [
        'update' => 'auto',
        'stamp' => 'auto',
    ];

    const OPTIONAL_COLUMNS = [
        'status',
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

    /**
     * Sets the `counter` column based on `assignment`
     */
    protected function onFirstSave()
    {
        $assignment = $this->__get('assignment');
        if ($assignment === null) {
            return false;
        }

        $database = self::getDatabase();
        $counter = $database->max(
            static::TABLE,
            'counter',
            [
                'assignment' => $assignment->__get('id')
            ]
        );

        $this->counter = ++$counter;
        return true;
    }
}
