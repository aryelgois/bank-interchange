<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

/**
 * A Person object defines someone in the real world
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/objects
 * @version 0.1
 */
class Bank
{
    /**
     * Defined by a government entity
     *
     * @var string
     */
    public $code;
    
    /**
     * Bank's name
     *
     * @var string
     */
    public $name;
    
    /**
     * Creates a new Bank object
     *
     * @param string $code Bank's code
     * @param string $name Bank's name
     */
    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }
}
