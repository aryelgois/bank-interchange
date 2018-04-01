<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile;

use aryelgois\Medools;
use aryelgois\BankInterchange;

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
     * Returns a ShippingFile View
     *
     * @param Medools\Model $model A ShippingFile Model
     *
     * @return BankInterchange\FilePack\ViewInterface
     */
    protected function getView(Medools\Model $model)
    {
        $assignment = $model->assignment;

        $view_class = __NAMESPACE__ . "\\Views\\Cnab$model->cnab\\"
            . BankInterchange\Utils::toPascalCase($assignment->bank->name);

        return new $view_class($model);
    }
}
