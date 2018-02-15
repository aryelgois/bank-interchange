<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\Medools;
use aryelgois\BankInterchange\Models;
use aryelgois\BankInterchange\Utils;

/**
 * Controller class for shipping files
 *
 * A shipping file is a data structure used by banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller
{
    /**
     * Shipping file extension
     *
     * @const string
     */
    const EXTENSION = '.REM';

    /**
     * CNAB type to be used
     *
     * @var string
     */
    protected $cnab;

    /**
     * Holds ShippingFile View objects
     *
     * @var array[]
     */
    protected $views;

    /**
     * Creates a new ShippingFile Controller object
     *
     * @param string $cnab CNAB type to be used
     */
    public function __construct(string $cnab)
    {
        $this->cnab = $cnab;
    }

    /**
     * Generates the CNAB shipping file from data in a ShippingFile
     *
     * @param mixed  $where \Medoo\Medoo $where clause or for ShippingFile or
     *                      its instance
     * @param string $name  Name for the generated shipping file
     */
    public function generate($where, string $name = null)
    {
        $model_class = Models\ShippingFile::class;
        $shipping_file = ($where instanceof $model_class)
            ? $where
            : Medools\ModelManager::getInstance($model_class, $where);

        $view_class = __NAMESPACE__ . "\\Views\\$this->cnab\\"
            . Utils::toPascalCase($shipping_file->assignment->bank->name);

        $view = new $view_class($shipping_file);

        $this->views[] = [
            'file' => $view,
            'name' => Utils::addExtension(
                $name ?? $view->filename(),
                static::EXTENSION
            ),
        ];
    }

    /**
     * Echos the Shipping File with headers
     *
     * If there are more than one shipping file, it outputs a zip
     *
     * @throws \LogicException If there is no view to output
     */
    public function output()
    {
        $count = count($this->views);
        if ($count == 0) {
            throw new \LogicException('You need to generate() first');
        } elseif ($count > 1) {
            return $this->zip();
        }

        $view = $this->views[0];
        $view['file']->output($view['name']);
    }

    /**
     * Echos a zip file containing all shipping files
     *
     * @param string $name Filename
     *
     * @throws \LogicException If there is no view to pack
     */
    public function zip(string $name = null)
    {
        if (count($this->views) == 0) {
            throw new \LogicException('You need to generate() first');
        }
        Utils::checkOutput('ZIP');

        $file = tmpfile();
        $filepath = stream_get_meta_data($file)['uri'];

        $zip = new \ZipArchive();
        $zip->open($filepath, \ZipArchive::OVERWRITE);
        foreach ($this->views as $view) {
            $zip->addFromString($view['name'], $view['file']->output());
        }
        $zip->close();

        $name = Utils::addExtension($name ?? 'download', '.zip');

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($filepath);
        unlink($filepath);
    }
}
