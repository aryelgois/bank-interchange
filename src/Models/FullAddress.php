<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils\Validation;

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
        return implode("\n", array_filter([
            implode(', ', array_filter([
                $this->place,
                $this->number,
                $this->detail,
                ($this->detail == '' ? $this->neighborhood : ''),
            ])),
            implode(', ', array_filter([
                ($this->detail != '' ? $this->neighborhood : ''),
                implode(' - CEP: ', array_filter([
                    $this->county->name . '/' . $this->county->state->code,
                    Validation::cep($this->zipcode),
                ])),
            ])),
        ]));
    }

    /**
     * Outputs Model's data in a short format
     *
     * @return string
     */
    public function outputShort()
    {
        return implode(', ', array_filter([
            $this->place,
            $this->number,
            $this->detail,
            $this->neighborhood,
            implode(' ', array_filter([
                $this->county->name . '/' . $this->county->state->code,
                Validation::cep($this->zipcode),
            ]))
        ]));
    }
}
