<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Views;

use aryelgois\BankInterchange as BankI;

/**
 * Generates Bank Billets in banco do Nordeste's layout
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class BancoDoNordeste extends CaixaEconomicaFederal
{
    /**
     * Length used to zero-pad "Our Number"
     */
    const OUR_NUMBER_LENGTH = 7;

    /**
     * Length used to zero-pad the account
     */
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
        $our_number = BankI\Utils::padNumber($this->models['title']->our_number, 7);

        return $this->models['title']->checkDigitOurNumberAlgorithm($our_number, 8);
    }

    /**
     * Free space, defined by Bank.
     *
     * Here: Agency/Assignor's code . Our number . Wallet operation . '000'
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
     * Prepare some data to be used during Draw
     *
     * @param mixed[] $data Data for the bank billet
     */
    protected function beforeDraw()
    {
        $this->models['wallet']->symbol = $this->models['wallet']->operation;
        parent::beforeDraw();
    }
}
