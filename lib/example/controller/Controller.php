<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\controller;

//use aryelgois\utils;

/**
 * Core class for example's controllers
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
abstract class Controller
{
    /**
     * Creates a new Controller object
     *
     * @param string $dir Path to database directory
     */
    public function __construct()
    {
        
    }
    
    /**
     * Generates <option> with states from database
     *
     * @return string
     */
    public function genStatesOptions()
    {
        //$result = [];
        if (!array_key_exists('address', $this->data)) {
            $this->loadData(['address']);
        }
        /*foreach ($data['address']['states'] as $code => $name) {
            $result[] = '<option value="' . $code . '">' . $name . '</option>';
        }
        return implode('', $result);*/
    }
}
