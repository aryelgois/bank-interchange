<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

use aryelgois\utils;
use aryelgois\objects\Person;
use aryelgois\cnab240\Cnab240File as Cnab240;

/**
 * It is You. or, the website's owner.
 *
 * Actually, whoever has the covenant with the Bank.
 *
 * NOTES:
 * - $document is string[] with keys 'type' and 'number'
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.2.1
 */
class Assignor extends Person
{
    /**
     *
     */
    const SELECT_QUERY = "SELECT * FROM `assignors` INNER JOIN `people` ON `assignors`.`id`=`people`.`id` WHERE `assignors`.`id` = ";
    
    /**
     * Bank id
     *
     * @var integer
     */
    public $bank;
    
    /**
     * Covenant provided by the Bank, 20 digits
     *
     * @var string
     */
    public $covenant;
    
    /**
     * Contains:
     *
     * 'number' => 5 digits,  // bank agency
     * 'cd' => 1 digit        // check digit
     *
     * @var string[]
     */
    public $agency;
    
    /**
     * Contains:
     *
     * 'number' => 12 digits, // bank account
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
     * Creates a new Person object
     *
     * @param string   $name     Person's name
     * @param string   $document Person's document
     * @param string   $covenant Assignor's covenant with the Bank
     * @param string[] $agency   Assignor's agency in the Bank
     * @param string[] $account  Agency's account in the Bank
     */
    public function __construct(utils\Database $database, $id)
    {
        $result = utils\Database::fetch($database->query(self::SELECT_QUERY . $id))[0]; // @todo Change to getFirst
        
        parent::__construct($result['name'], $result['document']);
        $this->validateDocument();
        
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
    
    /**
     * Validates $this document as CNPJ or CPF
     *
     * @throws UnexpectedValueException If is invalid
     */
    protected function validateDocument()
    {
        $type = 1;
        $number = utils\Validation::cpf($this->document);
        if ($number == false) {
            $type = 2;
            $number = utils\Validation::cnpj($this->document);
        }
        if ($number == false) {
            throw new \UnexpectedValueException('Not a valid document');
        }
        $this->document = ['type' => $type, 'number' => $number];
    }
}
