<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
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
class BancoDoNordeste extends namespace\CaixaEconomicaFederal
{
    /**
     * Length used to zero-pad "Our Number"
     */
    const ONUM_LEN = 7;

    /**
     * Length used to zero-pad the account
     */
    const ACCOUNT_LEN = 7;

    /**
     * Free space, defined by Bank.
     *
     * Here: Agency/Assignor's code . Our number . Wallet operation . '000'
     */
    protected function generateFreeSpace()
    {
        $result = $this->formatAgencyCode(false)
                . $this->formatOnum(false)
                . '21'
                . '000';
        return $result;
    }
}
