<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views\Cnabs;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates CNAB400 Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Cnab400 extends BankI\Views\Cnab
{
    /**
     * Adds a File Header
     */
    protected function registerFileHeader()
    {
        $assignor = $this->shipping_file->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');

        $rg = '01REMESSA'
            . '01COBRANCA       '
            . $this->formatAgencyAccount()
            . '      '
            . BankI\Utils::padAlfa($assignor_person->get('name'), 30)
            . $bank->get('code')
            . BankI\Utils::padAlfa($bank->get('name'), 15)
            . date('dmy')
            . BankI\Utils::padNumber($assignor->get('edi'), 3, true)
            . str_repeat(' ', 291)
            . BankI\Utils::padNumber($this->registries, 6);

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a Transaction
     *
     * @param integer $movement ...
     * @param Title   $title    Holds data about the title and the related payer
     */
    protected function registerTransaction(BankI\Models\Title $title)
    {
        $rg = '1'
            . '                '
            . $this->assignorAgencyAccount()
            . '00' // fine percent
            . '    '
            . BankI\Utils::padNumber($title->get('id'), 25)
            . BankI\Utils::padNumber($title->get('our_number'), 7)
            . BankI\Utils::checkDigitOurNumber($title->get('our_number'))
            . '0000000000' // contract
            . self::secondDiscount($title)
            . '        '
            . $title->getForeign('assignor')->getForeign('wallet')->get('febraban')
            . BankI\Utils::padNumber($config['service'] ?? '00', 2)
            . BankI\Utils::padNumber($title->get('id'), 10)
            . date('dmy', strtotime($title->get('due')))
            . BankI\Utils::padNumber(number_format($title->get('value'), 2, '', ''), 13)
            . '000'  // Charging bank
            . '0000' // the agency
            . ' '    // and it's check digit
            . BankI\Utils::padNumber($title->get('kind'), 2)
            . 'A' // accept
            . date('dmy', strtotime($title->get('stamp')))
            . BankI\Utils::padNumber($config['instruction_code'] ?? '0', 4)
            . '0000000000000' // one day fine
            . ($title->get('discount_date') != '' ? date('dmy', strtotime($title->get('discount_date'))) : '000000')
            . BankI\Utils::padNumber(number_format($title->get('discount_value'), 2, '', ''), 13)
            . BankI\Utils::padNumber(number_format($title->get('iof'), 2, '', ''), 13)
            . BankI\Utils::padNumber(number_format($title->get('rebate'), 2, '', ''), 13)
            . $title->getForeign('payer')->toCnab400()
            . str_repeat(' ', 40) // can be message or guarantor->name
            . '99' // protest deadline
            . $title->getForeign('specie')->get('febraban')
            . BankI\Utils::padNumber($this->registries, 6);

        $this->file .= $rg . static::LINE_END;
    }

    /**
     * Adds a Trailer
     */
    protected function registerFileTrailer()
    {
        $rg = '9'
            . str_repeat(' ', 393)
            . BankI\Utils::padNumber($this->registries, 6);

        $this->file .= $rg . static::LINE_END;
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
        $assignor = $this->shipping_file->getForeign('assignor');
        $assignor_person = $assignor->getForeign('person');
        $bank = $assignor->getForeign('bank');

        $result = BankI\Utils::padNumber($assignor->get('agency'), 4)
                . '00' //BankI\Utils::padNumber($assignor->get('agency_cd'), 2)
                . BankI\Utils::padNumber($assignor->get('account'), 7)
                . BankI\Utils::padNumber($assignor->get('account_cd'), 1);

        return $result;
    }

    protected static function secondDiscount($title)
    {
        $result = '000000'
                . '0000000000000';

        return $result;
    }
}
