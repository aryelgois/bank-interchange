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
 * A basic abstract controller
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
abstract class Controller
{
    /**
     * List of required $config keys
     *
     * @const string[]
     */
    const CONFIG_KEYS = [];
    
    /**
     * Holds user configurations
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
     * Controller result after execute()
     *
     * @var string
     */
    public $result = '';
    
    /**
     * New entry id in the database after execute()
     *
     * @var integer
     */
    public $id = 0;
    
    /**
     * Creates a new Controller object
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
        if (!empty(array_diff(static::CONFIG_KEYS, array_keys($config)))) {
            throw new \InvalidArgumentException('There are missing configurations');
        }
        $this->config = $config;
    }
    
    /**
     * Generates a view output from data in the model
     *
     * @return boolean for success or failure
     */
    public abstract function execute();
}
