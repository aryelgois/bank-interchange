<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Medools;
use aryelgois\BankInterchange\Utils;

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
    const TABLE = 'assignments';

    const COLUMNS = [
        'id',
        'assignor',
        'address',
        'bank',
        'document_kind',
        'wallet',
        'cnab',
        'covenant',
        'agency',
        'agency_cd',
        'account',
        'account_cd',
        'agency_account_cd',
        'edi',
    ];

    const FOREIGN_KEYS = [
        'assignor' => [
            __NAMESPACE__ . '\\Assignor',
            'person'
        ],
        'address' => [
            __NAMESPACE__ . '\\FullAddress',
            'id'
        ],
        'bank' => [
            __NAMESPACE__ . '\\Bank',
            'id'
        ],
        'document_kind' => [
            __NAMESPACE__ . '\\DocumentKind',
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
     * @param boolean $mask           If should include mask
     *
     * @return string
     *
     * @throws \LengthException @see Utils::padNumber()
     */
    public function formatAgencyAccount(
        int $agency_length,
        int $account_length,
        bool $mask = true
    ) {
        return Utils::padNumber($this->agency, $agency_length)
            . ($mask ? '/' : '')
            . Utils::padNumber($this->account, $account_length)
            . ($mask ? '-' : '')
            . $this->account_cd;
    }
}
