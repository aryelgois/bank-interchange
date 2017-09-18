<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Abstracts\Views;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates Shipping Files to be sent to banks
 *
 * ABSTRACTS:
 * - open()
 * - addRegistry(BankI\Objects\Title $title, $opt = null)
 * - close()
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class ShippingFile
{
    /**
     * Holds data from database and manipulates some tables
     *
     * @var Model
     */
    protected $model;
    
    /**
     * Total registries in the file
     *
     * integer
     */
    protected $registries = 0;
    
    /**
     * Every registry in the file
     *
     * Some may span multiple lines
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
     * Creates a new ShippingFile view object
     *
     * @param Model   $model   Contains data fetched from database
     * @param integer $file_id Sequential file number, max 6 digits
     */
    public function __construct(BankI\Abstracts\Models\ShippingFile $model, $file_id)
    {
        $this->model = $model;
        $this->file_id = $file_id;
        
        $this->open();
    }
    
    /**
     * Adds a File Header
     */
    abstract protected function open();
    
    /**
     * Adds a new Title registry
     *
     * @param Title   $title Contains data for the registry
     * @param mixed[] $opt   Optional data used by CNAB* implementation
     *
     * @return boolean For success or failure
     *
     * @throws OverflowException If there are too many registries
     */
    abstract public function addRegistry(BankI\Objects\Title $title, $opt = null);
    
    /**
     * Adds a File Trailer
     */
    abstract protected function close();
    
    /**
     * Outputs the contents in a multiline string
     *
     * NOTES:
     * - Closes the Shipping File
     *
     * @param string $nl  Newline delimiter to be used
     * @param string $eof Character added at the end in it's own line
     *                    - Used in CNAB400
     *
     * @return string
     */
    final public function output($nl = "\n", $eof = null)
    {
        $this->close();
        $result = implode($nl, $this->file);
        if ($eof !== null) {
            $result .= $nl . $eof;
        }
        return $result;
    }
    
    
    /*
     * Formatting
     * =========================================================================
     */
    
    
    /**
     * Formats Assignor's Agency and Account with check digits
     *
     * @return string
     */
    protected function assignorAgencyAccount()
    {
        $a = $this->model->assignor;
        $result = BankI\Utils::padNumber($a->agency['number'], 5) . $a->agency['cd']
                . BankI\Utils::padNumber($a->account['number'], 12) . $a->account['cd'];
        $result .= static::assignorAgencyAccountCheck($result);
        return $result;
    }
    
    /**
     * Calculates Agency/Account check digit
     *
     * @param string $agency_account String whose check digit will be calculated
     *
     * @return string
     */
    protected static function assignorAgencyAccountCheck($agency_account)
    {
        $cd = Utils\Validation::mod10($agency_account);
        return $cd;
    }
}
