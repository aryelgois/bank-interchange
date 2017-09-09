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
 * A basic controller to create the shipping file
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class Controller
{
    /**
     * Holds user configurations
     *
     * Until now, these are used:
     * - 'assignor': assignor id who will generate a Shipping File
     *
     * @var mixed[]
     */
    protected $config;
    
    /**
     * Holds data from database and manipulates some tables
     *
     * @var Model
     */
    protected $model;
    
    /**
     * ShippingFile content after execute()
     *
     * @var string
     */
    public $result = '';
    
    /**
     * ShippingFile id in the database
     *
     * @var integer
     */
    public $id = 0;
    
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
        if (!empty(array_diff(['assignor'], array_keys($config)))) {
            throw new \InvalidArgumentException('There are missing configurations');
        }
        $this->config = $config;
        
        $this->model = new BankI\Models\Model($db_address, $db_banki, $config['assignor']);
    }
    
    /**
     * Generates the ShippingFile from data in the model
     *
     * @return boolean for success or failure
     */
    public function execute()
    {
        $id = $this->model->getNextId();
        $view = new BankI\Views\View($this->model, $id);
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
     * Creates a new ShippingFile Controller object
     *
     * @param string $path Path to where the file will be saved
     *
     * @return string Filename or false on failure
     *
     * @throws InvalidArgumentException If there are missing configurations
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
        
        $this->model->insertFile($filename);
        $this->model->updateStatus(1);
        
        return $filename;
    }
}
