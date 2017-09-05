<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

use aryelgois\utils\Database;

/**
 * A Service might be a product or something your enterprise does
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
class Service
{
    /**
     * [desc]
     *
     * @var string
     */
    public $description;
    
    /**
     * [desc]
     *
     * @var float
     */
    public $value;
    
    /**
     * Creates a new Service object
     *
     * @param Database $database ..
     * @param integer  $id       ..
     *
     * @throws RuntimeException If it can not load from database
     */
    public function __construct(Database $db_cnab240, $id)
    {
        $service = Database::fetch($db_cnab240->query("SELECT * FROM `services` WHERE `id` = " . $id));
        if (empty($service)) {
            throw new \RuntimeException('Could not load service from database');
        }
        $service = $service[0];
        
        $this->description = $service['description'];
        $this->value = (float)$service['value'];
        
        $this->formatCnab240();
    }
    
    /**
     * Formats $this data to be CNAB240 compliant
     */
    protected function formatCnab240()
    {
        $result = '';
        
        $this->cnab240_string = $result;
    }
}
