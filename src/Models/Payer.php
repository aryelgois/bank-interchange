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
            __NAMESPACE__ . '\FullAddress',
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
     * Formats Payer's data to be CNAB240 compliant
     */
    public function toCnab240()
    {
        if ($this->cnab240_string == null) {
            $person = $this->getForeign('person');
            $address = $this->getForeign('address');

            $result = BankI\Utils::padNumber($person->documentFormat(), 15)
                    . BankI\Utils::padAlfa($person->get('name'), 40)
                    . BankI\Utils::padAlfa($address->get('place') . ', ' . $address->get('number') . ($address->get('detail') != '' ? ', ' . $address->get('detail') : ''), 40)
                    . BankI\Utils::padAlfa($address->get('neighborhood'), 15)
                    . BankI\Utils::padNumber($address->get('zipcode'), 8)
                    . BankI\Utils::padAlfa($address->getForeign('county')->get('name'), 15)
                    . BankI\Utils::padAlfa($address->getForeign('county')->getForeign('state')->get('code'), 2);

            $this->cnab240_string = $result;
        }
        return $this->cnab240_string;
    }
}
