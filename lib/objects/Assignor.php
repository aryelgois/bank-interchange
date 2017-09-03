<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

use aryelgois\objects\Person;

/**
 * A Person object defines someone in the real world
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/objects
 * @version 0.1
 */
class Assignor extends Person
{
    /**
     * Covenant provided by the Bank
     *
     * @var string
     */
    public $covenant;
    
    /**
     * Must contain keys 'number' and 'cd'
     *
     * @var string[]
     */
    public $agency;
    
    /**
     * Must contain keys 'number' and 'cd'
     *
     * @var string[]
     */
    public $account;
    
    /**
     * Creates a new Person object
     *
     * @param string   $name     Person's name
     * @param string   $document Person's document
     * @param string   $covenant Assignor's covenant with the Bank
     * @param string[] $agency   Assignor's agency in the Bank
     * @param string[] $account  Agency's account in the Bank
     */
    public function __construct(
        string $name,
        string $document,
        string $covenant,
        array  $agency,
        array  $account
    ) {
        parent::__construct($name, $document);
        $this->covenant = $covenant;
        $this->agency = $agency;
        $this->account = $account;
    }
}
