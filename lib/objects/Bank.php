<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

use aryelgois\utils\Database;
use aryelgois\cnab240\Cnab240File;

/**
 * A Bank has to keep our money safe!
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.2
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
     * Creates a new Bank object
     *
     * @param string $database ..
     * @param string $id       Bank's id
     */
    public function __construct(Database $database, $id)
    {
        $result = Database::fetch($database->query("SELECT * FROM `banks` WHERE `id` = " . $id))[0]; // @todo Change to getFirst
        
        $this->code = $result['code'];
        $this->name = $result['name'];
        $this->tax = $result['tax'];
    }
}
