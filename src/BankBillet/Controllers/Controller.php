<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Controllers;

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
class Controller extends BankI\Abstracts\Controllers\Controller
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
        
        $this->model = new BankI\BankBillet\Models\Model($db_address, $db_banki, $config);
    }
    
    /**
     * Generates the Bank Billet from data in the model
     *
     * After this method, the view is ready to output the pdf.
     *
     * @return boolean for success or failure
     */
    public function execute()
    {
        $view_class = '\\aryelgois\\BankInterchange\\BankBillet\\Views\\' . $this->model->bank->view;
        $this->view = new $view_class($this->model, $this->config['billet']);
        return true;
    }
    
    /**
     * Echos the Bank Billet with headers
     *
     * @param string $name The name of the file.
     */
    public function output($name = '')
    {
        $this->view->Output('I', $name);
    }
    
    /**
     * Writes the Bank Billet to a local file and updates the Database
     *
     * @param string $path   Path to directory where the file will be saved
     * @param string $prefix String inserted before the file index
     *
     * @return string Filename or false on failure
     */
    public function saveFile($path, $prefix = '')
    {
        $filename = $prefix . ($prefix != '' ? '_' : '')
                  . BankI\Utils::padNumber($this->model->title->id, 15) . '_'
                  . date('Y-m-d_h-i-s')
                  . '.pdf';
        
        $file = @fopen($path . '/' . $filename, 'w');
        if ($file === false) {
            return false;
        }
        fwrite($file, $this->view->Output('S'));
        fclose($file);
        
        $this->model->insertEntry();
        
        return $filename;
    }
}
