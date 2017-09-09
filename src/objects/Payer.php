<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Objects;

use aryelgois\Utils;
use aryelgois\Objects;
use aryelgois\BankInterchange as BankI;

/**
 * A Payer object loaded from a database
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 */
class Payer extends Objects\Person
{
    /**
     * Contains a string ready to be inserted into the Shipping File
     *
     * @var string
     */
    public $cnab240_string;
    
    /**
     * Creates a new Payer object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $db_cnab240 Database with an `payers` table
     * @param Database $db_address Address Database from aryelgois\databases
     * @param integer  $id         Payer's id in the table
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(
        Utils\Database $db_cnab240,
        Utils\Database $db_address,
        $id
    ) {
        // load from database
        $payer = Utils\Database::fetch($db_cnab240->query("SELECT * FROM `payers` WHERE `id` = " . $id . " LIMIT 1"));
        if (empty($payer)) {
            throw new \RuntimeException('Could not load payer from database');
        }
        $payer = $payer[0];
        
        $address_data = Utils\Database::fetch($db_cnab240->query("SELECT * FROM `fulladdress` WHERE `id` = " . $payer['address']));
        if (empty($address_data)) {
            throw new \RuntimeException('Could not load payer\'s address from database');
        }
        $address_data = $address_data[0];
        
        $address = new Objects\FullAddress(
            $db_address,
            $address_data['county'],
            $address_data['neighborhood'],
            $address_data['place'],
            $address_data['number'],
            $address_data['zipcode'],
            $address_data['detail']
        );
        
        parent::__construct($payer['name'], $payer['document']);
        $this->address = [$address];
        
        $this->formatCnab240();
    }
    
    /**
     * Formats Payer's data to be CNAB240 compliant
     */
    protected function formatCnab240()
    {
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
}
