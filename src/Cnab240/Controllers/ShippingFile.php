<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Cnab240\Controllers;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * A controller to generate Shipping Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class ShippingFile extends BankI\Abstracts\Controllers\Controller
{
    /**
     * List of required $config keys
     *
     * 'assignor': assignor id who will generate the Shipping File
     *
     * @const string[]
     */
    const CONFIG_KEYS = ['assignor'];
    
    /**
     * Creates a new ShippingFile Controller object
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
        
        $this->model = new BankI\Cnab240\Models\ShippingFile($db_address, $db_banki, $config['assignor']);
    }
    
    /**
     * Generates the ShippingFile from data in the model
     *
     * @return boolean for success or failure
     */
    public function execute()
    {
        $id = $this->model->getNextId();
        $view = new BankI\Cnab240\Views\ShippingFile($this->model, $id);
        if (empty($this->model->titles)) {
            return false;
        }
        foreach ($this->model->titles as $title) {
            $view->addEntry(1, $title);
        }
        $this->result = $view->output();
        $this->id = $id;
        return true;
    }
    
    /**
     * Writes the Shipping File to a local file and updates the Database
     *
     * @param string $path Path to directory where the file will be saved
     *
     * @return string Filename or false on failure
     */
    public function saveFile($path)
    {
        $filename = 'COB.240.'
                  . BankI\Utils::padNumber($this->model->assignor->edi7, 6) . '.'
                  . date('Ymd') . '.'
                  . BankI\Utils::padNumber($this->id, 5) . '.'
                  . BankI\Utils::padNumber($this->model->assignor->covenant, 5, true) // @todo verify if covenant is actually small and the Headers exagerate the covenant lenght
                  . '.REM';
        
        $file = @fopen($path . '/' . $filename, 'w');
        if ($file === false) {
            return false;
        }
        fwrite($file, $this->result);
        fclose($file);
        
        $this->model->insertEntry($filename);
        $this->model->updateStatus('titles', array_column($this->model->titles, 'id'), 1);
        
        return $filename;
    }
}
