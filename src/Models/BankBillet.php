<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Model class for BankBillet
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class BankBillet extends namespace\Model
{
    /**
     * Specie code, symbol and formatting style
     *
     * @const string[]
     */
    const SPECIE = [
        'code' => '9',
        'symbol' => 'R$',
        'thousand' => '',
        'decimal' => ','
    ];
    
    /**
     * Creates a new BankBillet Model object
     *
     * @param Database $db_address  An interface to `address` database
     * @param Database $db_banki    An interface to `bank_interchange` database
     * @param integer  $assignor_id Assignor's id from database
     *
     * @throws InvalidArgumentException If there are missing configurations
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $assignor_id
    ) {
        parent::__construct($db_address, $db_banki, $assignor_id);
        
        
    }
}
