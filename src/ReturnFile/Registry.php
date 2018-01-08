<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

/**
 * Registry Model with fields extracted
 *
 * Once instantiated, the object is immutable
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Registry
{
    /**
     * Holds the Registry CNAB
     *
     * @var string
     */
    protected $cnab;

    /**
     * Holds the Registry data
     *
     * The available keys depend on $cnab and $type
     *
     * @var array
     */
    protected $data;

    /**
     * Holds the Registry type
     *
     * @var string
     */
    protected $type;

    /**
     * Creates a new Registry
     *
     * @param string $cnab CNAB to be stored
     * @param string $type Type to be stored
     * @param array  $data Data to be stored
     */
    public function __construct(string $cnab, string $type, array $data)
    {
        $this->cnab = $cnab;
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Returns a field stored in $data
     *
     * @param string $field A valid $data key
     *
     * @return mixed Almost always string, but may have a numeric value
     *
     * @throws \DomainException If the field is not found
     */
    public function __get($field)
    {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        } else {
            $message = "Invalid Registry field '$field' for a "
                . "$this->type ($this->cnab)";
            throw new \DomainException($message);
        }
    }

    /**
     * Returns the stored CNAB
     *
     * @return string
     */
    public function getCnab()
    {
        return $this->cnab;
    }

    /**
     * Returns the stored data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the stored type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
