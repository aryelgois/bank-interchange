<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Objects;

use aryelgois\Utils;

/**
 * A Bank has to keep our money safe!
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 */
class Bank
{
    /**
     * Defined by a government entity, 3 digits
     *
     * @var string
     */
    public $code;
    
    /**
     * Bank's name, 30 characters, left-padded with spaces
     *
     * @var string
     */
    public $name;
    
    /**
     * Bank's tax for billets
     *
     * @var float
     */
    public $tax;
    
    /**
     * Filename to Bank's logo, inside res/
     *
     * @var string
     */
    public $logo;
    
    /**
     * Creates a new Bank object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $database Database with an `banks` table
     * @param integer  $id       Bank's id in the table
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(Utils\Database $database, $id)
    {
        // load from database
        $result = Utils\Database::fetch($database->query("SELECT * FROM `banks` WHERE `id` = " . $id . " LIMIT 1"));
        if (empty($result)) {
            throw new \RuntimeException('Could not load bank from database');
        }
        $result = $result[0];
        
        $this->code = $result['code'];
        $this->name = $result['name'];
        $this->tax  = $result['tax'];
        //$this->logo = $result['logo'];
    }
}
