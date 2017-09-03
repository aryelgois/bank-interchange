<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240;

use aryelgois\utils\Validation;
use aryelgois\objects\Address;
use aryelgois\objects\Person;
//use aryelgois\cnab240\objects\Assignor;
//use aryelgois\cnab240\objects\Bank;

/**
 * Base class for Shipping and Return Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
abstract class Cnab240File
{
    /**
     * FEBRABAN's version of file layout
     *
     * @const string
     */
    const VERSION_FILE_LAYOUT = '101';
    
    /**
     * FEBRABAN's version of lot layout
     *
     * @const string
     */
    const VERSION_LOT_LAYOUT = '060';
    
    /**
     * Bank data
     *
     * @var Bank
     */
    protected $bank;
    
    /**
     * Assignor data
     *
     * @var Assignor
     */
    protected $assignor;
    
    /**
     * Every entry of the file
     *
     * @var string[]
     */
    protected $file = [];
    
    /**
     * Controls if it's allowed to add more registries
     *
     * @var boolean
     */
    protected $closed = false;
    
    /**
     * Lot sequence
     *
     * @var integer
     */
    protected $lot = 0;
    
    /**
     * Outputs the Shipping File contents in a multiline string
     *
     * NOTES:
     * - Closes the current Lot
     *
     * @return string A long, long string. Each line with 240 bytes.
     */
    final public function output()
    {
        if (!$this->closed) {
            $this->addLotTrailer();
            $this->addFileTrailer();
        }
        return implode("\n", $this->file);
    }
    
    
    /*
     * Validation
     * =========================================================================
     */
    
    
    /**
     * Formats Control field
     *
     * @param integer $type Code adopted by FEBRABAN to identify the registry type
     *
     * @return string
     */
    protected function fieldControl(integer $type)
    {
        return $this->bank->code . ($type == 9 ? '9999' : self::padNumber($this->lot, 4)) . $type;
    }
    
    /**
     * Validates assignor data
     *
     * NOTES:
     * - May throws exceptions from called methods
     *
     * @param Assignor $assignor Object to be validated
     *
     * @return Assignor
     */
    protected static function validateAssignor(namespace\objects\Assignor $assignor)
    {
        $a = clone $assignor;
        
        $a->name = self::padAlfa($a->name, 30);
        
        $a->document = self::validateAssignorDocument($a->document);
        
        $a->covenant = self::padNumber($a->covenant, 20);
        
        $a->agency['number'] = self::padNumber($a->agency['number'], 5);
        $a->agency['cd'] = self::padNumber($a->agency['cd'], 1);
        
        $a->account['number'] = self::padNumber($a->account['number'], 12);
        $a->account['cd'] = self::padNumber($a->account['cd'], 1);
        
        return $a;
    }
    
    /**
     * Validates a document as CNPJ or CPF
     *
     * @param string $doc Brazilian CNPJ or CPF
     *
     * @return string[] with keys ['type', 'number']
     *
     * @throws UnexpectedValueException If is invalid
     */
    protected static function validateAssignorDocument($doc)
    {
        $type = 1;
        $number = Validation::cnpj($doc);
        if ($number == false) {
            $type = 2;
            $number = Validation::cpf($doc);
        }
        if ($number == false) {
            throw new \UnexpectedValueException('Not a valid document');
        }
        return ['type' => $type, 'number' => self::padNumber($number, 14)];
    }
    
    
    /*
     * Helper
     * =========================================================================
     */
    
    
    /**
     * Adds trailing spaces to a value and trims overflow
     *
     * @param string  $val Value to be formatted
     * @param integer $len Maximum characters allowed
     *
     * @return string
     */
    protected static function padAlfa($val, $len)
    {
        return substr(str_pad($val, $len), 0, $len);
    }
    
    /**
     * Adds leading zeroes to a value
     *
     * @param string  $val Value to be formatted
     * @param integer $len Maximum digits length allowed
     *
     * @return string
     *
     * @throws LengthException If $val overflows
     */
    protected static function padNumber($val, $len)
    {
        $result = str_pad($val, $len, '0', STR_PAD_LEFT);
        if (strlen($result) > $len) {
            throw new \LengthException('Value overflows maximum length');
        }
        return $result;
    }
}
