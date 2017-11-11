<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
class FullAddress extends Medools\Models\Address\FullAddress
{
    const DATABASE_NAME_KEY = 'bank_interchange';

    /**
     * Outputs Model's data in a long format
     *
     * @return string
     */
    public function outputLong()
    {
        $data = $this->getData(true);

        $result = $data['place'] . ', '
                . $data['number'] . ', '
                . ($data['detail'] != '' ? ', ' . $data['detail'] : '')
                . $data['neighborhood'] . "\n"
                . $data['county']['name'] . '/'
                . $data['county']['state']['code'] . ' - '
                . 'CEP: ' . Validation::cep($data['zipcode']);

        return $result;
    }

    /**
     * Outputs Model's data in a short format
     *
     * @return string
     */
    public function outputShort()
    {
        $data = $this->getData(true);

        $result = $data['place'] . ', '
                . $data['number'] . ', '
                . $data['neighborhood'] . ', '
                . $data['county']['name'] . '/'
                . $data['county']['state']['code'] . ' '
                . Validation::cep($data['zipcode']);

        return $result;
    }
}
