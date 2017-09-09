<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Objects;

use aryelgois\Utils;
use aryelgois\Objects;

/**
 * It's who made a covenant with the Bank and has to emit bank billets.
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 */
class Assignor extends Objects\Person
{
    /**
     * Assignor's id
     *
     * @var integer
     */
    public $id;
    
    /**
     * Bank's id
     *
     * @var integer
     */
    public $bank;
    
    /**
     * Covenant provided by the Bank. Max 20 digits, but should have up to 6
     *
     * @var string
     */
    public $covenant;
    
    /**
     * Bank Agency. Contains:
     *
     * 'number' => 5 digits,
     * 'cd' => 1 digit        // check digit
     *
     * @var string[]
     */
    public $agency;
    
    /**
     * Bank Account. Contains:
     *
     * 'number' => 12 digits,
     * 'cd' => 1 digit        // check digit
     *
     * @var string[]
     */
    public $account;
    
    /**
     * EDI7 code informed by the Bank
     *
     * @var string
     */
    public $edi7;
    
    /**
     * Creates a new Assignor object from data in a Database
     *
     * @see data/database.sql
     *
     * @param Database $database Database with an `assignors` table
     * @param integer  $id       Assignor's id in the table
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(Utils\Database $database, $id)
    {
        // load from database
        $result = Utils\Database::fetch($database->query("SELECT * FROM `assignors` WHERE `id` = " . $id . " LIMIT 1"));
        if (empty($result)) {
            throw new \RuntimeException('Could not load assignor from database');
        }
        $result = $result[0];
        
        parent::__construct($result['name'], $result['document']);
        
        $this->id = $id;
        $this->bank = $result['bank'];
        $this->covenant = $result['covenant'];
        $this->agency = [
            'number' => $result['agency'],
            'cd' => $result['agency_cd']
        ];
        $this->account = [
            'number' => $result['account'],
            'cd' => $result['account_cd']
        ];
        $this->edi7 = $result['edi7'];
    }
}
