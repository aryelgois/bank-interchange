<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet;

use aryelgois\Medools;
use aryelgois\BankInterchange\Models;
use aryelgois\BankInterchange\Utils;

/**
 * Controller class for Bank Billets
 *
 * A Bank Billet is a printable representation of a Title
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller
{
    /**
     * Additional data for the bank billets
     *
     * @var mixed[]
     */
    protected $data;

    /**
     * Paths to directories with logos
     *
     * @var string[]
     */
    protected $logos;

    /**
     * Holds BankBillet View objects
     *
     * @var array[]
     */
    protected $views;

    /**
     * Creates a new BankBillet Controller object
     *
     * @param string[]        $data  Additional data for the bank billets
     * @param string|string[] $logos Paths to directories with logos
     */
    public function __construct(array $data, $logos)
    {
        $this->data = $data;
        $this->logos = (array) $logos;
    }

    /**
     * Generates the Bank Billet from data in a Title
     *
     * @param mixed  $where \Medoo\Medoo $where clause for Title or its instance
     * @param string $name  Name for the generated PDF
     */
    public function generate($where, string $name = null)
    {
        $model_class = Models\Title::class;
        $title = ($where instanceof $model_class)
            ? $where
            : Medools\ModelManager::getInstance($model_class, $where);

        $view_class = __NAMESPACE__ . '\\Views\\'
            . Utils::toPascalCase($title->assignment->bank->name);

        $this->views[] = [
            'file' => new $view_class($title, $this->data, $this->logos),
            'name' => Utils::addExtension($name ?? $title->id, '.pdf'),
        ];
    }

    /**
     * Echos the Bank Billet with headers
     *
     * If there are more than one billet, it outputs a zip
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
        $view['file']->Output('I', $view['name']);
    }

    /**
     * Echos a zip file containing all bank billets
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
            $zip->addFromString($view['name'], $view['file']->Output('S'));
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
