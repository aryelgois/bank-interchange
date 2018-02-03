<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Controller class for Bank Billets
 *
 * A Bank Billet is a printable representation of a Title
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller
{
    /**
     * Holds the FPDF object with the bank billet
     *
     * @var Views\BankBillet
     */
    protected $view;

    /**
     * Creates a new BankBillet Controller object
     *
     * Generates the Bank Billet from data in the Title
     *
     * @param mixed[]  $where \Medoo\Medoo $where clause for Models\Title
     * @param string[] $data  Additional data for the view @see Views\BankBillet
     * @param string   $logos Path to directory with logos
     */
    public function __construct($where, $data, $logos)
    {
        $title = new BankI\Models\Title($where);

        $bank = $title->assignment->bank;

        $view_class = __NAMESPACE__ . '\\Views\\' . $bank->view;

        $this->view = new $view_class($title, $data, $logos);
    }

    /**
     * Echos the Bank Billet with headers
     *
     * @param string $name The filename
     */
    public function output($name = '')
    {
        $this->view->Output('I', $name);
    }
}
