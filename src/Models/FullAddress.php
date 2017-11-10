<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\Medools;

/**
 * This class make it easier to access Address data from Database
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class FullAddress extends Medools\Models\Address\FullAddress
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    /*
     * Old methods
     * =======================================================================
     */

    public function outputLong()
    {
        $result = $this->place . ', '
                . $this->number . ', '
                . ($this->detail != '' ? ', ' . $this->detail : '')
                . $this->neighborhood . "\n"
                . $this->county['name'] . '/' . $this->state['code'] . ' - '
                . 'CEP: ' . Utils\Validation::cep($this->zipcode);
        return $result;
    }

    public function outputShort()
    {
        $result = $this->place . ', '
                . $this->number . ', '
                . $this->neighborhood . ', '
                . $this->county['name'] . '/' . $this->state['code'] . ' '
                . Utils\Validation::cep($this->zipcode);
        return $result;
    }
}
