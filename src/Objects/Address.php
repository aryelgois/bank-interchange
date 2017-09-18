<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Objects;

use aryelgois\Utils;
use aryelgois\Objects;

/**
 * This class make it easier to access Address data from Database
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Address extends Objects\FullAddress
{
    /**
     * Creates a new Assignor object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $db_address Address Database from aryelgois\databases
     * @param integer  $id         FullAddress' id in the table
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $id
    ) {
        $address_data = Utils\Database::fetch($db_banki->query("SELECT * FROM `fulladdress` WHERE `id` = " . $id));
        if (empty($address_data)) {
            throw new \RuntimeException('Could not load address from database');
        }
        $address_data = $address_data[0];
        
        parent::__construct(
            $db_address,
            $address_data['county'],
            $address_data['neighborhood'],
            $address_data['place'],
            $address_data['number'],
            $address_data['zipcode'],
            $address_data['detail']
        );
    }
    
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
