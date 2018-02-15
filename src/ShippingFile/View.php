<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\BankInterchange\Models;
use VRia\Utils\NoDiacritic;

/**
 * Generates CNAB compliant shipping files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class View
{
    /**
     * Added at the end of every line
     *
     * @const string
     */
    const EOL = "\r\n";

    /**
     * Added at the file end
     *
     * @const string
     */
    const EOF = "";

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
     * Total registries in the file
     *
     * integer
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
        foreach ($shipped_titles as $sft) {
            $this->add($sft);
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
        $result = strtoupper(NoDiacritic::filter(
            implode(static::EOL, $this->registries) . static::EOL . static::EOF
        ));

        if ($name === null) {
            return $result;
        }

        Utils::checkOutput(pathinfo($name)['extension'] ?? '');

        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($result));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        echo $result;
    }

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
            $cnab,
            $assignment->edi,
            date('Ymd', strtotime($this->shipping_file->stamp)),
            $this->shipping_file->counter,
            $assignment->covenant,
        ];

        return sprintf($format, ...$data);
    }

    /*
     * Main methods
     * =========================================================================
     */

    /**
     * Adds a File Header
     */
    protected function open()
    {
        $this->registry_count++;
        $this->addFileHeader();
    }

    /**
     * Adds a Title registry
     *
     * @param Models\ShippingFileTitle $sft Contains data for the registry
     *
     * @throws \OverflowException If there are too many registries
     */
    protected function add(Models\ShippingFileTitle $sft)
    {
        $this->increment(999998);
        $this->addTitle($sft->title);
    }

    /**
     * Actually inserts the registry in $this->registries
     *
     * @param string   $format A sprintf() format
     * @param string[] $data   Registry data to be inserted
     */
    final protected function register($format, $data)
    {
        // data cleanup
        foreach ($data as $id => $value) {
            $data[$id] = preg_replace('/:;,\.\/\\\?\$\*!#_-/', '', $data[$id]);
        }

        $this->registries[] = sprintf($format, ...$data);
    }

    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        $this->increment(999999);
        $this->addFileTrailer();
    }

    /*
     * Abstracts
     * =========================================================================
     */

    /**
     * ...
     */
    abstract protected function addFileHeader();

    /**
     * ...
     *
     * @param Models\Title $title ...
     */
    abstract protected function addTitle(Models\Title $title);

    /**
     * ...
     */
    abstract protected function addFileTrailer();

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Increments the Registries counter
     *
     * @param integer $limit Defines the overflow limit
     *
     * @throws \OverflowException If there are too many registries
     */
    protected function increment($limit)
    {
        if ($this->registry_count > $limit) {
            throw new \OverflowException('The File got too many registries');
        }
        $this->registry_count++;
    }
}
