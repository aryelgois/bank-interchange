<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;
use FPDF;

/**
 * Generates Bank Billets to be sent to clients/payers
 *
 * Extends FPDF by Olivier Plathey
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class BankBillet extends FPDF
{
    /**
     * Creates a new BankBillet View object
     *
     * @param mixed[] $data Data for output
     */
    public function __construct($data)
    {
        
        parent::__construct();
    }
}
