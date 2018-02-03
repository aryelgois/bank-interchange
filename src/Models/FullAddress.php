<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils\Validation;
use aryelgois\Medools;

/**
 * This class make it easier to access Address data from Database
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class FullAddress extends \aryelgois\Databases\Models\Address\FullAddress
{
    /**
     * Outputs Model's data in a long format
     *
     * @return string
     */
    public function outputLong()
    {
        $result = $this->place . ', '
            . $this->number . ', '
            . ($this->detail != '' ? ', ' . $this->detail : '')
            . $this->neighborhood . "\n"
            . $this->county->name . '/'
            . $this->county->state->code . ' - '
            . 'CEP: ' . Validation::cep($this->zipcode);

        return $result;
    }

    /**
     * Outputs Model's data in a short format
     *
     * @return string
     */
    public function outputShort()
    {
        $result = $this->place . ', '
            . $this->number . ', '
            . $this->neighborhood . ', '
            . $this->county->name . '/'
            . $this->county->state->code . ' '
            . Validation::cep($this->zipcode);

        return $result;
    }
}
