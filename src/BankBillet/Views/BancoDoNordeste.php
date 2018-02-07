<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Views;

use aryelgois\BankInterchange;

/**
 * Generates bank billets for Banco do Nordeste
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class BancoDoNordeste extends CaixaEconomicaFederal
{
    const OUR_NUMBER_LENGTH = 7;

    const ACCOUNT_LENGTH = 7;

    /**
     * Temporary way to set the document specie
     */
    const SPECIE_DOC = 'RC';

    /**
     * Calculates Our number's check digit
     *
     * @return string
     */
    protected function checkDigitOurNumber()
    {
        $our_number = BankInterchange\Utils::padNumber($this->models['title']->our_number, 7);

        return $this->models['title']->checkDigitOurNumberAlgorithm($our_number, 8);
    }

    /**
     * Free space, defined by Bank.
     *
     * Here: Agency/Assignor . Our number . Wallet operation . '000'
     */
    protected function generateFreeSpace()
    {
        $result = $this->formatAgencyAccount()
            . $this->formatOurNumber()
            . '21'
            . '000';
        return $result;
    }

    /**
     * Banco do Banese requires the wallet field to be the wallet operation
     */
    protected function drawBillet()
    {
        $this->fields['wallet']['value'] = $this->models['wallet']->operation;
        parent::drawBillet();
    }
}
