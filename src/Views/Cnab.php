<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views;

use aryelgois\Utils;
use aryelgois\Medools;
use aryelgois\BankInterchange as BankI;
use VRia\Utils\NoDiacritic;

/**
 * Generates CNAB compliant Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Cnab
{
    /**
     * Sequence added at the end of every line
     *
     * @const string
     */
    const LINE_END = "\r\n";

    /**
     * Character added at the file end
     *
     * @const string
     */
    const FILE_END = "\u{1a}";

    /**
     * CNAB Shipping File registries
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
     * ...
     *
     * @const Models\ShippingFile
     */
    protected $shipping_file;

    /**
     * Creates a new ShippingFile view object
     *
     * @param Models\ShippingFile $shipping_file A Shipping File whose Titles
     *                                           will be used
     */
    public function __construct(BankI\Models\ShippingFile $shipping_file)
    {
        $this->shipping_file = $shipping_file;

        $title_list = BankI\Models\ShippingFileTitle::dump([
            'shipping_file' => $shipping_file->get('id')
        ]);
        $title_list = array_column($title_list, 'title');

        $shipping_file_titles = new Medools\ModelIterator(
            new BankI\Models\Title,
            ['id' => $title_list]
        );

        $this->open();
        foreach ($shipping_file_titles as $title) {
            $this->add($title);
        }
        $this->close();
    }

    /**
     * Outputs the file contents in a multiline string
     *
     * @return string
     */
    final public function output()
    {
        return implode(static::LINE_END, $this->registries)
             . static::LINE_END . static::FILE_END;
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
     * @param Models\Title $title Contains data for the registry
     *
     * @throws OverflowException If there are too many registries
     */
    protected function add(BankI\Models\Title $title)
    {
        $this->increment(999998);
        $this->addTitle($title);
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
            $data[$id] = strtoupper(NoDiacritic::filter($value));
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
    abstract protected function addTitle(BankI\Models\Title $title);

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
