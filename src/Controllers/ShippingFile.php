<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Controllers;

use aryelgois\BankInterchange as BankI;

/**
 * Controller class for Shipping Files
 *
 * A Shipping File is a document sent to the Bank
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class ShippingFile
{
    /**
     * Creates a new ShippingFile Model
     *
     * @param integer[] $title_list A list of Models\Title `id`
     *                  They all must be for the same assignor.
     *
     * @return integer The id for the new ShippingFile
     *
     * @throws \InvalidArgumentException If $title_list is empty or invalid
     */
    public static function create(array $title_list)
    {
        if (empty($title_list)) {
            throw new \InvalidArgumentException('Title list is empty');
        }

        $check = BankI\Models\Title::dump(['id' => array_values($title_list)]);
        if (
            empty($check)
            || count(array_unique(array_column($check, 'assignor'))) > 1
        ) {
            throw new \InvalidArgumentException('Title list is invalid');
        }

        $shipping_file = new BankI\Models\ShippingFile;
        $shipping_file->set('status', 0);
        $shipping_file->save();
        $id = $shipping_file->get('id');

        foreach ($title_list as $title_id) {
            $sft = new BankI\Models\ShippingFileTitle;
            $sft->set('shipping_file', $id);
            $sft->set('title', $title_id);
            $sft->save();
        }

        return $id;
    }
}
