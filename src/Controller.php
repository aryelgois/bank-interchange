<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange;

/**
 * Controller class
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Controller
{
    /**
     * Creates a new ShippingFile Model
     *
     * @param integer[] $title_list A list of Models\Title `id`
     *                  They all must be for the same assignment.
     *
     * @return Models\ShippingFile
     *
     * @throws \InvalidArgumentException If $title_list is empty or invalid
     */
    public static function createShippingFile(array $title_list)
    {
        if (empty($title_list)) {
            throw new \InvalidArgumentException('Title list is empty');
        }

        $check = Models\Title::dump(
            ['id' => array_values($title_list)],
            ['id', 'assignment']
        );
        $assignment = array_unique(array_column($check, 'assignment'));
        if (count($assignment) != 1) {
            throw new \InvalidArgumentException('Title list is invalid');
        }
        $assignment = $assignment[0];

        $shipping_file = (new Models\ShippingFile)->fill([
            'assignment' => $assignment,
            'status' => 0,
        ]);
        $shipping_file->save();
        $id = $shipping_file->id;

        foreach ($title_list as $title_id) {
            $sft = (new Models\ShippingFileTitle)->fill([
                'shipping_file' => $id,
                'title' => $title_id,
            ]);
            $sft->save();
        }

        return $shipping_file;
    }
}
