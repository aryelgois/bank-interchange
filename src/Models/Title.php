<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\Medools;

/**
 * A Title represents something a Client got from its Assignor
 *
 * It might be a product or service
 *
 * NOTE:
 * - `assignment` must be valid for the client's assignor
 * - The pair `assignment` and `our_number` must be UNIQUE
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Title extends Medools\Model
{
    const TABLE = 'titles';

    const COLUMNS = [
        'id',
        'shipping_file',
        'movement',
        'assignment',
        'client',
        'guarantor',
        'currency',
        'kind',
        'doc_number',
        'our_number',
        'accept',
        'status',
        'value',
        'value_paid',
        'ioc_iof',
        'rebate',
        'tax_value',
        'tax_included',
        'fine_type',
        'fine_date',
        'fine_value',
        'interest_type',
        'interest_date',
        'interest_value',
        'discount1_type',
        'discount1_date',
        'discount1_value',
        'discount2_type',
        'discount2_date',
        'discount2_value',
        'discount3_type',
        'discount3_date',
        'discount3_value',
        'description',
        'emission',
        'due',
        'update',
        'stamp',
    ];

    const STAMP_COLUMNS = [
        'update' => 'auto',
        'stamp' => 'auto',
    ];

    const OPTIONAL_COLUMNS = [
        'shipping_file',
        'movement',
        'guarantor',
        'accept',
        'status',
        'value_paid',
        'fine_date',
        'fine_value',
        'interest_date',
        'interest_value',
        'discount1_date',
        'discount1_value',
        'discount2_date',
        'discount2_value',
        'discount3_date',
        'discount3_value',
    ];

    const FOREIGN_KEYS = [
        'shipping_file' => [
            __NAMESPACE__ . '\\ShippingFile',
            'id'
        ],
        'movement' => [
            __NAMESPACE__ . '\\ShippingFileMovements',
            'id'
        ],
        'assignment' => [
            __NAMESPACE__ . '\\Assignment',
            'id'
        ],
        'client' => [
            __NAMESPACE__ . '\\Client',
            'id'
        ],
        'guarantor' => [
            __NAMESPACE__ . '\\Client',
            'id'
        ],
        'currency' => [
            __NAMESPACE__ . '\\Currency',
            'id'
        ],
        'kind' => [
            __NAMESPACE__ . '\\DocumentKind',
            'id'
        ],
    ];

    /**
     * Calculates this model's `our_number` check digit
     *
     * @param integer $base @see aryelgois\Utils\Validation::mod11() $base
     *
     * @return string
     */
    public function checkDigitOurNumber($base = 9)
    {
        return self::checkDigitOurNumberAlgorithm($this->our_number, $base);
    }

    /**
     * Calculates Our number check digit
     *
     * @param string  $our_number Value to calculate the check digit
     * @param integer $base       @see aryelgois\Utils\Validation::mod11() $base
     *
     * @return string
     */
    public static function checkDigitOurNumberAlgorithm($our_number, $base = 9)
    {
        $digit = Utils\Validation::mod11($our_number, $base);

        $digit = ($digit > 1)
            ? $digit - 11
            : 0;

        return abs($digit);
    }

    /**
     * Returns the correct CurrencyCode
     *
     * @return CurrencyCode
     * @return null         If foreigns aren't set
     */
    public function getCurrencyCode()
    {
        if (!isset($this->currency, $this->assignment->bank)) {
            return null;
        }

        return CurrencyCode::getInstance([
            'currency' => $this->currency->id,
            'bank' => $this->assignment->bank->id
        ]);
    }

    /**
     * Sets the `our_number` column based on current `assignor`
     *
     * Intended to be used only when creating a new entry
     *
     * NOTE:
     * - Be sure to save() soon
     *
     * @throws \LogicException If assignor is not set
     */
    public function setOurNumber()
    {
        $assignor = $this->assignor;
        if ($assignor === null) {
            throw new \LogicException('You MUST set `assignor` column first');
        }

        $database = self::getDatabase();
        $our_number = $database->max(
            static::TABLE,
            'our_number',
            [
                'assignor' => $assignor->id
            ]
        );

        $this->our_number = ++$our_number;
    }
}
