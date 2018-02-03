<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;
use aryelgois\BankInterchange as BankI;

/**
 * The relation between an assignor and a Bank
 *
 * The same assignor may have multiple accounts with the same or other banks.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Assignment extends Medools\Model
{
    const TABLE = 'assignment';

    const COLUMNS = [
        'id',
        'assignor',
        'bank',
        'wallet',
        'covenant',   // Covenant provided by the Bank. Max 20 digits, but should have up to 6
        'agency',     // Bank Agency. max 5 digits
        'agency_cd',  // check digit
        'account',    // Bank Account. max 12 digits
        'account_cd', // check digit
        'edi',        // EDI code informed by the Bank
    ];

    const FOREIGN_KEYS = [
        'assignor' => [
            __NAMESPACE__ . '\\Assignor',
            'id'
        ],
        'bank' => [
            __NAMESPACE__ . '\\Bank',
            'id'
        ],
        'wallet' => [
            __NAMESPACE__ . '\\Wallet',
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
            BankI\Utils::padNumber($this->agency, $agency_length),
            BankI\Utils::padNumber($this->account, $account_length)
        ];
        $check_digit = $this->account_cd;

        if ($symbols) {
            return implode(' / ', $tmp) . '-' . $check_digit;
        }
        return implode('', $tmp) . $check_digit;
    }
}
