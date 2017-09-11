<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Controllers;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Controller class for Bank Billet
 *
 * A Bank Billet is a printable representation of a Title
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class BankBillet extends namespace\Controller
{
    /**
     * List of required $config keys
     *
     * 'assignor': assignor id who will generate the Shipping File
     * 'title': columns from `titles` @see data/database.sql and example
     * 'billet':
     *     'payment_place': Where it's aceptable to pay the billet
     *     'demonstrative': Multiline text
     *     'instructions':  Multiline text
     *
     * @const string[]
     */
    const CONFIG_KEYS = ['assignor', 'title', 'billet'];
    
    /**
     * Holds the FPDF object with the bank billet
     *
     * @var Views\BankBillet
     */
    protected $view;
    
    /**
     * Creates a new BankBillet Controller object
     *
     * @param Database $db_address An interface to `address` database
     * @param Database $db_banki   An interface to `bank_interchange` database
     * @param mixed    $config     Configurations for this Controller
     *
     * @throws InvalidArgumentException If there are missing configurations
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $config
    ) {
        parent::__construct(...func_get_args());
        
        $this->model = new BankI\Models\BankBillet($db_address, $db_banki, $config);
    }
    
    /**
     * Generates the Bank Billet from data in the model
     *
     * @return boolean for success or failure
     */
    public function execute()
    {
        $view_class = '\\aryelgois\\BankInterchange\\Views\\BankBillets\\' . $this->model->bank->view;
        $this->view = new $view_class($this->model, $this->config['billet']);
        return true;
    }
    
    /**
     * Outputs the Bank Billet
     *
     * @see http://www.fpdf.org/en/doc/output.htm
     *
     * @param string $dest Destination where to send the document.
     * @param string $name The name of the file. It is ignored in case of destination S.
     *
     * @return ...
     */
    public function output($dest = 'I', $name = 'doc.pdf')
    {
        return $this->view->Output($dest, $name);
    }
}
