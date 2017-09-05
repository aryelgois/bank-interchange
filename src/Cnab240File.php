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
 * @version 0.1.1
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
    public static function padAlfa($val, $len)
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
    public static function padNumber($val, $len)
    {
        $result = str_pad($val, $len, '0', STR_PAD_LEFT);
        if (strlen($result) > $len) {
            throw new \LengthException('Value overflows maximum length');
        }
        return $result;
    }
}
