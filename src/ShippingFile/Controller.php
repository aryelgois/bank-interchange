<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\Medools;
use aryelgois\BankInterchange;
use aryelgois\MedoolsRouter\Resource;

/**
 * Controller class for shipping files
 *
 * A shipping file is a data structure used by banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller extends BankInterchange\FilePack\Controller
{
    const EXTENSION = '.REM';

    const MODEL_CLASS = BankInterchange\Models\ShippingFile::class;

    /**
     * Creates a new instance with models in a Resource, then outputs it
     *
     * If the request contains a 'with_billets' query parameter, it returns
     * withBillets()
     *
     * @param Resource $resource Processed MedoolsRouter Resource
     *
     * @return boolean For success or failure
     */
    public static function fromResource(Resource $resource)
    {
        if (isset($resource->query['with_billets'])) {
            return static::withBillets($resource);
        }
        return parent::fromResource($resource);
    }

    /**
     * Returns a ShippingFile View
     *
     * @param Medools\Model $model A ShippingFile Model
     *
     * @return BankInterchange\FilePack\ViewInterface
     */
    protected function getView(Medools\Model $model)
    {
        $assignment = $model->assignment;

        $view_class = __NAMESPACE__ . "\\Views\\Cnab$assignment->cnab\\"
            . BankInterchange\Utils::toPascalCase($assignment->bank->name);

        return new $view_class($model);
    }

    /**
     * Creates a zip file with shipping files and their billets
     *
     * If $resource is a collection, the generated files are organized in
     * directories for each shipping file.
     *
     * @param Resource $resource Processed MedoolsRouter Resource
     *
     * @return boolean For success or failure
     */
    public static function withBillets(Resource $resource)
    {
        $controller = new static;

        $list = $resource->getList();
        if (empty($list)) {
            return false;
        }

        foreach ($list as $id) {
            if (!$controller->generate($id)) {
                return false;
            }

            $title_list = BankInterchange\Models\Title::dump(
                ['shipping_file' => $id['id']],
                BankInterchange\Models\Title::PRIMARY_KEY
            );

            $bankbillet = new BankInterchange\BankBillet\Controller;
            foreach ($title_list as $title_id) {
                if (!$bankbillet->generate($title_id)) {
                    return false;
                }
            }
            $billets = $bankbillet->dump() ?? [];

            if ($resource->kind === 'collection') {
                $model = (static::MODEL_CLASS)::getInstance($id);
                $directory = "{$model->assignment->id}-$model->counter/";

                $current = count($controller->views) - 1;
                $filename = $controller->views[$current]['name'];
                $controller->views[$current]['name'] = $directory . $filename;

                foreach ($billets as $billet_id => $billet) {
                    $billets[$billet_id]['name'] = $directory . $billet['name'];
                }
            }

            $controller->views = array_merge($controller->views, $billets);
        }

        $controller->zip();

        return true;
    }
}
