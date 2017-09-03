<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\model;

use aryelgois\utils;

/**
 * Core class for example's models
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
abstract class Model
{
    /**
     * Path to database directory
     *
     * @const string
     */
    const DIR = __DIR__ . '/../data';
    
    /**
     * Holds loaded database
     *
     * @var array[]
     */
    public $data = [];
    
    /**
     * Creates a new Model object
     *
     * @param string[] $tables List of "tables" to load from the database
     *
     * @throws RuntimeException If a table could not be loaded
     */
    public function __construct(array $tables = [])
    {
        if (!empty($tables)) {
            $result = $this->loadData($tables);
            if ($result !== true) {
                throw new RuntimeException('Could not load '. $result . '.json');
            }
        }
    }
    
    /**
     * Loads database from JSON files
     *
     * @param string[] $tables List of "tables" to load from the database
     *
     * @return true on success or string on failure (name of table)
     */
    public function loadData(array $tables)
    {
        foreach ($tables as $table) {
            $file = self::DIR . '/' . $table . '.json';
            if (!file_exists($file)) {
                return $table;
            }
            $this->data[$table] = json_decode(file_get_contents($file), true);
        }
        return true;
    }
    
    /**
     * Updates database files
     *
     * @param string $table Name of "table" to update
     *
     * @return boolean for success or failure
     */
    public function updateData($table)
    {
        $file = fopen(self::DIR . '/' . $table . '.json', 'w');
        if ($file === false) {
            return false;
        }
        fwrite($file, json_encode($this->data[$table], JSON_PRETTY_PRINT));
        fclose($file);
        return true;
    }
    
    /**
     * Validates Brazilian cnpj|cpf
     *
     * @param string $doc Document to be validated
     *
     * @return string
     *
     * @throws InvalidArgumentException If $doc is invalid
     */
    public static function documentValidate($doc)
    {
        $valid = utils\Validation::cnpj($doc);
        if ($valid === false) {
            $valid = utils\Validation::cpf($doc);
        }
        if ($valid === false) {
            throw new InvalidArgumentException('Invalid document');
        }
        return $valid;
    }
    
    /**
     * Gets the type of a Brazilian document
     *
     * @return integer 0 is undefined, 1 is CPF, 2 is CNPJ (last two defined by FEBRABAN)
     */
    public static function documentType($document)
    {
        if (utils\Validation::cpf($document) !== false) {
            return 1;
        }
        if (utils\Validation::cnpj($document) !== false) {
            return 2;
        }
        return 0;
    }
}
