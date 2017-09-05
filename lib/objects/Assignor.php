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
 * - $document is string[]
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.2
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
     * Must contains:
     *
     * 'number' => 5 digits,  // bank agency
     * 'cd' => 1 digit        // check digit
     *
     * @var string[]
     */
    public $agency;
    
    /**
     * Must contains:
     *
     * 'number' => 12 digits, // bank account
     * 'cd' => 1 digit        // check digit
     *
     * @var string[]
     */
    public $account;
    
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
    }
    
    /**
     * Validates $this data
     *
     * NOTES:
     * - May throws exceptions from called methods
     */
    protected function validate()
    {
        $this->name = Cnab240::padAlfa($this->name, 30);
        
        $this->document = self::validateDocument($this->document);
        
        $this->covenant = Cnab240::padNumber($this->covenant, 20);
        
        $this->agency['number'] = Cnab240::padNumber($this->agency['number'], 5);
        $this->agency['cd'] = Cnab240::padNumber($this->agency['cd'], 1);
        
        $this->account['number'] = Cnab240::padNumber($this->account['number'], 12);
        $this->account['cd'] = Cnab240::padNumber($this->account['cd'], 1);
    }
    
    /**
     * Validates $this document as CNPJ or CPF
     *
     * @param string $doc Brazilian CNPJ or CPF
     *
     * @return string[] with keys ['type', 'number']
     *
     * @throws UnexpectedValueException If is invalid
     */
    protected static function validateDocument($doc)
    {
        $type = 1;
        $number = utils\Validation::cpf($doc);
        if ($number == false) {
            $type = 2;
            $number = utils\Validation::cnpj($doc);
        }
        if ($number == false) {
            throw new \UnexpectedValueException('Not a valid document');
        }
        return ['type' => $type, 'number' => $number];
    }
}
