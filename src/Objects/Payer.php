<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class Payer extends Objects\Person
{
    /**
     * Payer's id
     *
     * @var integer
     */
    public $id;
    
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
     * @param Database $db_address Address Database from aryelgois\databases
     * @param Database $db_banki   Database with an `payers` table
     * @param integer  $id         Payer's id in the table
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $id
    ) {
        // load from database
        $payer = Utils\Database::fetch($db_banki->query("SELECT * FROM `payers` WHERE `id` = " . $id . " LIMIT 1"));
        if (empty($payer)) {
            throw new \RuntimeException('Could not load payer from database');
        }
        $payer = $payer[0];
        
        parent::__construct($payer['name'], $payer['document']);
        
        $this->id = $id;
        $this->address[] = new namespace\Address($db_address, $db_banki, $payer['address']);
        
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
