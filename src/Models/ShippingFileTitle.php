<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;

/**
 * Creates a relation of Titles to Shipping Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ShippingFileTitle extends Medools\Model
{
    const TABLE = 'shipping_file_titles';

    const COLUMNS = [
        'shipping_file',
        'title',
    ];

    const PRIMARY_KEY = [
        'shipping_file',
        'title',
    ];

    const AUTO_INCREMENT = null;

    const FOREIGN_KEYS = [
        'shipping_file' => [
            __NAMESPACE__ . '\\ShippingFile',
            'id'
        ],
        'title' => [
            __NAMESPACE__ . '\\Title',
            'id'
        ],
    ];
}
