<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;
use aryelgois\BankInterchange as BankI;

/**
 * It's who made a covenant with the Bank and has to emit bank billets.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Assignor extends Medools\Model
{
    const TABLE = 'assignors';

    const COLUMNS = [
        'id',
        'person',
        'address',
        'bank',
        'wallet',
        'covenant',   // Covenant provided by the Bank. Max 20 digits, but should have up to 6
        'agency',     // Bank Agency. max 5 digits
        'agency_cd',  // check digit
        'account',    // Bank Account. max 12 digits
        'account_cd', // check digit
        'edi',        // EDI code informed by the Bank
        'logo',       // Absolut path to Assignor's logo
        'url',        // URL to be embeded into Assignor's logo, in the Bank Billet
    ];

    const OPTIONAL_COLUMNS = [
        'logo',
        'url',
    ];

    const FOREIGN_KEYS = [
        'person' => [
            '\aryelgois\Medools\Models\Person',
            'id'
        ],
        'address' => [
            __NAMESPACE__ . '\FullAddress',
            'id'
        ],
        'bank' => [
            __NAMESPACE__ . '\Bank',
            'id'
        ],
        'wallet' => [
            __NAMESPACE__ . '\Wallet',
            'id'
        ],
    ];

    /**
     * Formats Agency/Assignor's code
     *
     * @param integer $agency_length
     * @param integer $account_length
     * @param boolean $symbols        If should include symbols
     *
     * @return string
     *
     * @throws \LengthException @see Utils::padNumber()
     */
    public function formatAgencyAccount(
        $agency_length,
        $account_length,
        $symbols = true
    ) {
        $tmp = [
            BankI\Utils::padNumber($this->get('agency'), $agency_length),
            BankI\Utils::padNumber($this->get('account'), $account_length)
        ];
        $check_digit = $this->get('account_cd');

        if ($symbols) {
            return implode(' / ', $tmp) . '-' . $check_digit;
        }
        return implode('', $tmp) . $check_digit;
    }
}
