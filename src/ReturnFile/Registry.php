<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

/**
 * Registry Model with matched fields
 *
 * Once instantiated, the object is immutable
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Registry implements \JsonSerializable
{
    /**
     * Holds the Return File config key
     *
     * @var string
     */
    protected $config;

    /**
     * Holds the Registry data
     *
     * The available keys depend on $config and $type
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
     * @param string $config Return File config to be stored
     * @param string $type   Type to be stored
     * @param array  $data   Data to be stored
     */
    public function __construct(string $config, string $type, array $data)
    {
        $this->config = $config;
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
        }
        throw new \DomainException(sprintf(
            "Invalid field '%s' for a %s registry (CNAB%s)",
            $field,
            $this->type,
            $this->config
        ));
    }

    /**
     * Returns the stored Return File config key
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
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

    /**
     * Outputs useful data from the Registry
     *
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
