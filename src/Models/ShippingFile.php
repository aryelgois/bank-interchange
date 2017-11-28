<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
        'assignor',
        'counter',
        'status',
        'stamp',
        'update',
    ];

    const OPTIONAL_COLUMNS = [
        'status',
        'stamp',
        'update',
    ];

    const FOREIGN_KEYS = [
        'assignor' => [
            __NAMESPACE__ . '\Assignor',
            'id'
        ],
    ];

    /**
     * Sets the `counter` column based on current `assignor`
     *
     * Intended to be used only when creating a new entry
     *
     * NOTE:
     * - Be sure to save() soon
     *
     * @throws \LogicException If assignor is not set
     */
    public function setCounter()
    {
        $assignor = $this->assignor;
        if ($assignor === null) {
            throw new \LogicException('You MUST set `assignor` column first');
        }

        $database = self::getDatabase();
        $counter = $database->max(
            static::TABLE,
            'counter',
            [
                'assignor' => $assignor->id
            ]
        );

        $this->counter = ++$counter;
    }
}
