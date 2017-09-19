<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Cnab400\Views;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates CNAB400 Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ShippingFile extends BankI\Abstracts\Views\ShippingFile
{
    /**
     * Adds a File Header
     */
    protected function open()
    {
        $this->registries++;
        $this->registerHeader();
    }
    
    /**
     * Adds a new Title entry
     *
     * @param Title   $title Contains data for the registry
     * @param mixed[] $opt   ...
     *
     * @return boolean For success or failure
     */
    public function addRegistry(BankI\Objects\Title $title, $opt = null)
    {
        // Check if the file is closed
        if ($this->closed) {
            return false;
        }
        
        $this->incrementRegistries(999998);
        $this->registerTransaction($title, $opt);
        return true;
    }
    
    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        if (!$this->closed) {
            $this->incrementRegistries(999999);
            $this->registerTrailer();
            $this->closed = true;
        }
    }
    
    
    /*
     * Internals
     * =========================================================================
     */
    
    
    /**
     * Adds a Header
     */
    protected function registerHeader()
    {
        $registry = '01REMESSA'
                  . '01COBRANCA       '
                  . $this->assignorAgencyAccount()
                  . '      '
                  . BankI\Utils::padAlfa($this->model->assignor->name, 30)
                  . $this->model->bank->code
                  . BankI\Utils::padAlfa($this->model->bank->name, 15)
                  . date('dmy')
                  . BankI\Utils::padNumber($this->model->assignor->edi, 3)
                  . str_repeat(' ', 291)
                  . BankI\Utils::padNumber($this->registries, 6);
        $this->file[] = $registry;
    }
    
    /**
     * Adds a Transaction
     *
     * @param integer $movement ...
     * @param Title   $title    Holds data about the title and the related payer
     */
    protected function registerTransaction(BankI\Objects\Title $title, $config)
    {
        $registry = '1'
                  . '                '
                  . $this->assignorAgencyAccount()
                  . '00' // fine percent
                  . '    '
                  . BankI\Utils::padNumber($title->id, 25)
                  . BankI\Utils::padNumber($title->onum, 7)
                  . BankI\Utils::checkDigitOnum($title->onum)
                  . '0000000000' // contract
                  . self::secondDiscount($title)
                  . '        '
                  . $title->wallet['cnab400']
                  . BankI\Utils::padNumber($config['service'], 2)
                  . BankI\Utils::padNumber($title->id, 10)
                  . date('dmy', strtotime($title->due))
                  . BankI\Utils::padNumber(number_format($title->value, 2, '', ''), 13)
                  . '000'  // Charging bank
                  . '0000' // the agency
                  . ' '    // and it's check digit
                  . BankI\Utils::padNumber($title->kind, 2)
                  . 'A' // accept
                  . date('dmy', strtotime($title->stamp))
                  . BankI\Utils::padNumber($config['instruction_code'] ?? '0', 4)
                  . '0000000000000' // one day fine
                  . ($title->discount['date'] != '' ? date('dmy', strtotime($title->discount['date'])) : '000000')
                  . BankI\Utils::padNumber(number_format($title->discount['value'], 2, '', ''), 13)
                  . BankI\Utils::padNumber(number_format($title->iof, 2, '', ''), 13)
                  . BankI\Utils::padNumber(number_format($title->rebate, 2, '', ''), 13)
                  . $title->payer->toCnab400()
                  . str_repeat(' ', 40) // can be message or guarantor->name
                  . '99' // protest deadline
                  . $title->specie['cnab400']
                  . BankI\Utils::padNumber($this->registries, 6);
        $this->file[] = $registry;
    }
    
    /**
     * Adds a Trailer
     */
    protected function registerTrailer()
    {
        $registry = '9'
                  . str_repeat(' ', 393)
                  . BankI\Utils::padNumber($this->registries, 6);
        $this->file[] = $registry;
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
        $result = BankI\Utils::padNumber($a->agency['number'], 4)
                . '00' //BankI\Utils::padNumber($a->agency['cd'], 2)
                . BankI\Utils::padNumber($a->account['number'], 7)
                . BankI\Utils::padNumber($a->account['cd'], 1);
        return $result;
    }
    
    protected static function secondDiscount($title)
    {
        $result = '000000'
                . '0000000000000';
        return $result;
    }
    
    
    /*
     * Helper
     * =========================================================================
     */
    
    
    /**
     * Increments the Registries counter
     *
     * @param integer $limit Defines the overflow limit
     *
     * @throws OverflowException If there are too many registries
     */
    protected function incrementRegistries($limit)
    {
        if ($this->registries > $limit) {
            throw new \OverflowException('The File got too many registries');
        }
        $this->registries++;
    }
}
