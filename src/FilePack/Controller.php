<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\FilePack;

use aryelgois\Utils\Utils;
use aryelgois\Medools;
use aryelgois\BankInterchange;
use aryelgois\MedoolsRouter\Resource;

/**
 * Wrapper controller that can make one or more files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Controller
{
    /**
     * File extension
     *
     * @const string
     */
    const EXTENSION = '';

    /**
     * Model class for generating the View
     *
     * @const string
     */
    const MODEL_CLASS = '';

    /**
     * Holds View objects
     *
     * @var array[]
     */
    protected $views;

    /**
     * Creates a new FilePack Controller object
     *
     * @throws \LogicException If MODEL_CLASS is not subclass of Medools\Model
     */
    public function __construct()
    {
        if (!is_subclass_of(static::MODEL_CLASS, Medools\Model::class)) {
            $message = 'Invalid MODEL_CLASS in ' . static::class;
            throw new \LogicException($message);
        }
    }

    /**
     * Creates a new instance with models in a Resource, then outputs it
     *
     * @param Resource $resource Processed MedoolsRouter Resource
     *
     * @return boolean For success or failure
     *
     * @throws \LogicException If $resource->model_class is invalid
     */
    public static function fromResource(Resource $resource)
    {
        if (!is_subclass_of($resource->model_class, Medools\Model::class)) {
            $message = 'Invalid Resource model_class in ' . static::class . ': '
                . $resource->model_class;
            throw new \LogicException($message);
        }

        $controller = new static;

        $list = $resource->getList();
        if (empty($list)) {
            return false;
        }

        foreach ($list as $id) {
            if (!$controller->generate($id)) {
                return false;
            }
        }

        if ($resource->content_type === 'application/zip') {
            $controller->zip();
        } else {
            $controller->output();
        }

        return true;
    }

    /**
     * Generates the file View from data in a Medools Model
     *
     * @param mixed  $where \Medoo\Medoo $where clause for MODEL_CLASS
     * @param string $name  Name for the generated file
     *
     * @return boolean For success or failure
     */
    public function generate($where, string $name = null)
    {
        $model = (static::MODEL_CLASS)::getInstance($where);
        if ($model === null) {
            return false;
        }

        $view = static::getView($model);

        $this->views[] = [
            'file' => $view,
            'name' => BankInterchange\Utils::addExtension(
                $name ?? $view->filename(),
                static::EXTENSION
            ),
        ];

        return true;
    }

    /**
     * Generates the View object from a Model
     *
     * @param Medools\Model $model A MODEL_CLASS instance
     *
     * @return ViewInterface
     */
    abstract protected function getView(Medools\Model $model);

    /**
     * Outputs the View with appropriated headers
     *
     * If there are more than one view objects, it calls zip() instead
     *
     * @throws \LogicException If there is no view to output
     */
    public function output()
    {
        $count = count($this->views);

        if ($count === 0) {
            throw new \LogicException('You need to generate() first');
        } elseif ($count > 1) {
            return $this->zip();
        }

        $view = $this->views[0];
        $view['file']->outputFile($view['name']);
    }

    /**
     * Outputs a zip file containing all views
     *
     * @param string $name Filename
     *
     * @throws \LogicException If there is no view to pack
     */
    public function zip(string $name = null)
    {
        if (count($this->views) === 0) {
            throw new \LogicException('You need to generate() first');
        }

        Utils::checkOutput('ZIP');

        $file = tmpfile();
        $filepath = stream_get_meta_data($file)['uri'];

        $zip = new \ZipArchive();
        $zip->open($filepath, \ZipArchive::OVERWRITE);
        foreach ($this->views as $view) {
            $zip->addFromString($view['name'], $view['file']->getContents());
        }
        $zip->close();

        $name = BankInterchange\Utils::addExtension(
            $name ?? 'download',
            '.zip'
        );

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        readfile($filepath);
        unlink($filepath);
    }
}
