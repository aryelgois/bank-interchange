<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\BankInterchange as BankI;
use aryelgois\Medools;

/**
 * Someone who pays for something
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Payer extends Medools\Model
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    const TABLE = 'payers';

    const COLUMNS = [
        'id',
        'person',
        'address',
    ];

    const FOREIGN_KEYS = [
        'person' => [
            '\aryelgois\Medools\Models\Person',
            'id'
        ],
        'address' => [
            '\aryelgois\Medools\Models\Address\FullAddress',
            'id'
        ],
    ];

    /*
     * Old members
     * =======================================================================
     */

    /**
     * Cache for toCnab240()
     *
     * @var string
     */
    protected $cnab240_string;

    /**
     * Cache for toCnab400()
     *
     * @var string
     */
    protected $cnab400_string;

    /**
     * Formats Payer's data to be CNAB240 compliant
     */
    public function toCnab240()
    {
        if ($this->cnab240_string == null) {
            $a = $this->address[0];
            $result = BankI\Utils::formatDocument($this, 15)
                    . BankI\Utils::padAlfa($this->name, 40)
                    . BankI\Utils::padAlfa($a->place . ', ' . $a->number . ($a->detail != '' ? ', ' . $a->detail : ''), 40)
                    . BankI\Utils::padAlfa($a->neighborhood, 15)
                    . BankI\Utils::padNumber($a->zipcode, 8)
                    . BankI\Utils::padAlfa($a->county['name'], 15)
                    . BankI\Utils::padAlfa($a->state['code'], 2);
            $this->cnab240_string = $result;
        }
        return $this->cnab240_string;
    }

    /**
     * Formats Payer's data to be CNAB400 compliant
     */
    public function toCnab400()
    {
        if ($this->cnab400_string == null) {
            $a = $this->address[0];
            $result = '0' . BankI\Utils::formatDocument($this)
                    . BankI\Utils::padAlfa($this->name, 40)
                    . BankI\Utils::padAlfa($a->place . ', ' . $a->number . ', ' . $a->neighborhood, 40)
                    . BankI\Utils::padAlfa($a->detail, 12)
                    . BankI\Utils::padNumber($a->zipcode, 8)
                    . BankI\Utils::padAlfa($a->county['name'], 15)
                    . BankI\Utils::padAlfa($a->state['code'], 2);
            $this->cnab400_string = $result;
        }
        return $this->cnab400_string;
    }
}
