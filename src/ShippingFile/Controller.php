<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\Utils;
use aryelgois\Medools;
use aryelgois\BankInterchange;

/**
 * Controller class for shipping files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller
{
    /**
     * List of available CNABs
     *
     * @const string[]
     */
    const STANDARDS = [
        '240',
        '400',
    ];

    /**
     * Renders the Shipping File in a Cnab standard
     *
     * @var Views\Cnab
     */
    protected $view;

    /**
     * ...
     *
     * @var string
     */
    protected $filename;

    /**
     * Creates a new Cnab Controller object
     *
     * @param integer  $cnab  Number of CNAB model
     * @param mixed[]  $where \Medoo\Medoo $where clause for Models\ShippingFile
     *
     * @throws \InvalidArgumentException If $cnab is invalid
     */
    public function __construct($cnab, $where)
    {
        if (!in_array($cnab, self::STANDARDS)) {
            throw new \InvalidArgumentException('Invalid CNAB');
        }

        $shipping_file = new BankInterchange\Models\ShippingFile($where);

        $view_class = '\\aryelgois\\BankInterchange\\Views\\Cnabs\\'
            . 'Cnab' . $cnab;

        $this->view = new $view_class($shipping_file);

        $this->filename = $this->view->filename($cnab);
    }

    /**
     * Outputs the generated Shipping File
     *
     * @param string $directory Where to save the Shipping File.
     *                          If empty, outputs to stdout.
     *
     * @return string Name for generated Shipping File
     * @return false  For failure
     */
    public function output($directory = '')
    {
        return $this->view->output();
    }

    /**
     * ...
     *
     * @return string
     */
    public function filename()
    {
        return $this->filename;
    }
}
