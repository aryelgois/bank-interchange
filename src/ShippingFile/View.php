<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\Utils\Utils;
use aryelgois\BankInterchange\FilePack;
use aryelgois\BankInterchange\Models;
use VRia\Utils\NoDiacritic;

/**
 * Generates CNAB compliant shipping files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class View implements FilePack\ViewInterface
{
    /**
     * Added at the file end
     *
     * @const string
     */
    const EOF = "";

    /**
     * Added at the end of every line
     *
     * @const string
     */
    const EOL = "\r\n";

    /**
     * How many titles can fit in this shipping file
     *
     * @const int
     */
    const TITLE_LIMIT = 0;

    /**
     * File registries
     *
     * @var string[]
     */
    protected $registries = [];

    /**
     * Count registries in the file
     *
     * @var integer
     */
    protected $registry_count = 0;

    /**
     * Model with data to be used
     *
     * @const Models\ShippingFile
     */
    protected $shipping_file;

    /**
     * Creates a new ShippingFile View object
     *
     * @param Models\ShippingFile $shipping_file A Shipping File whose Titles
     *                                           will be used
     *
     * @throws \OverflowException If $shipping_file has too many titles
     */
    public function __construct(Models\ShippingFile $shipping_file)
    {
        $this->shipping_file = $shipping_file;
        $shipped_titles = $shipping_file->getShippedTitles();
        $count = count($shipped_titles);
        if ($count > static::TITLE_LIMIT) {
            throw new \OverflowException(sprintf(
                '%s(%s) has %s titles, but only %s are allowed',
                get_class($shipping_file),
                $shipping_file->id,
                $count,
                static::TITLE_LIMIT
            ));
        }

        $this->open();
        foreach ($shipped_titles as $title) {
            $this->add($title);
        }
        $this->close();
    }

    /**
     * Outputs the file contents in a multiline string
     *
     * @param string $name File name
     *
     * @return string If no name is passed
     * @return null   If a name is passed (result is printed with header)
     */
    final public function output(string $name = null)
    {
        $result = implode(static::EOL, $this->registries) . static::EOL
            . static::EOF;

        if ($name === null) {
            return $result;
        }

        Utils::checkOutput(pathinfo($name)['extension'] ?? '');

        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($result));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo $result;
    }

    /*
     * FilePack\ViewInterface
     * =========================================================================
     */

    /**
     * Generates a filename (without extension)
     *
     * @return string
     */
    public function filename()
    {
        $assignment = $this->shipping_file->assignment;

        $format = 'COB.%03.3s.%06.6s.%08.8s.%05.5s.%05.5s';

        $data = [
            $this->shipping_file->cnab,
            $assignment->edi,
            date('Ymd', strtotime($this->shipping_file->stamp)),
            $this->shipping_file->counter,
            $assignment->covenant,
        ];

        return vsprintf($format, $data);
    }

    /**
     * Returns the View contents
     *
     * @return string
     */
    public function getContents()
    {
        return $this->output();
    }

    /**
     * Outputs the View with appropriated headers
     *
     * @param string $filename File name to be outputed
     */
    public function outputFile(string $filename)
    {
        $this->output($filename);
    }

    /*
     * Abstracts
     * =========================================================================
     */

    /**
     * Does initial steps for creating a shipping file
     */
    abstract protected function open();

    /**
     * Adds a Title registry
     *
     * @param Models\Title $title Contains data for the registry
     */
    abstract protected function add(Models\Title $title);

    /**
     * Does final steps for creating a shipping file
     */
    abstract protected function close();

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Removes diacritics and convert to upper case
     *
     * @param string[] $data Data to be normalized
     */
    protected static function normalize($data)
    {
        foreach ($data as $id => $value) {
            $data[$id] = strtoupper(NoDiacritic::filter($value));
        }
        return $data;
    }

    /**
     * Remove unwanted characters
     *
     * @param string $field Field to be filtered
     */
    protected static function filter($field)
    {
        $field = preg_replace('/[\.\/\\:;,?$*!#_-]/', '', $field);
        $field = preg_replace('/  +/', ' ', $field);
        return $field;
    }
}
