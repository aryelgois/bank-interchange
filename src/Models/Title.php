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
        'protest_code',
        'protest_days',
        'description',
        'occurrence',
        'occurrence_date',
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
        'value_paid',
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
        'protest_code',
        'protest_days',
        'description',
        'occurrence',
        'occurrence_date',
    ];

    const FOREIGN_KEYS = [
        'shipping_file' => [
            ShippingFile::class,
            'id'
        ],
        'movement' => [
            ShippingFileMovements::class,
            'id'
        ],
        'assignment' => [
            Assignment::class,
            'id'
        ],
        'client' => [
            Client::class,
            'id'
        ],
        'guarantor' => [
            Client::class,
            'id'
        ],
        'currency' => [
            Currency::class,
            'id'
        ],
        'kind' => [
            DocumentKind::class,
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
     * Returns the Title value, considering its tax
     *
     * @return float
     */
    public function getActualValue()
    {
        $val = $this->value + ($this->tax_included ? 0 : $this->tax_value);
        return (float) $val;
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
}
