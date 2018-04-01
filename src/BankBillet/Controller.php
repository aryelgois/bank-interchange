<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet;

use aryelgois\Medools;
use aryelgois\BankInterchange;

/**
 * Controller class for bank billets
 *
 * A bank billet is a printable representation of a Title
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Controller extends BankInterchange\FilePack\Controller
{
    const EXTENSION = '.pdf';

    const MODEL_CLASS = BankInterchange\Models\Title::class;

    /**
     * Additional data for the bank billets
     *
     * @var mixed[]
     */
    protected static $data = [];

    /**
     * Paths to directories with logos
     *
     * @var string[]
     */
    protected static $logos = [];

    /**
     * Loads data for the bank billets
     *
     * @param string[]        $data  Additional data for the bank billets
     * @param string|string[] $logos Paths to directories with logos
     */
    public static function loadData(array $data, $logos)
    {
        static::$data = $data;
        static::$logos = (array) $logos;
    }

    /**
     * Returns a BankBillet View
     *
     * @param Medools\Model $model A Title Model
     *
     * @return BankInterchange\FilePack\ViewInterface
     */
    protected function getView(Medools\Model $model)
    {
        $assignment = $model->assignment;

        $view_class = __NAMESPACE__ . '\\Views\\'
            . BankInterchange\Utils::toPascalCase($assignment->bank->name);

        return new $view_class($model, static::$data, static::$logos);
    }
}
