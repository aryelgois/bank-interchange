<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\controller;

use aryelgois\cnab240\example;

/**
 * Admin page controller
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
class Admin extends Controller
{
    public function __construct()
    {
        $model = new example\model\Admin();
        
        if (!empty($_POST)) {
            $model->formHandler();
            /*
             * This avoid resending the form on page refresh
             * and returns to main page
             */
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            die();
        }
        
        $view = new example\view\Admin($model);
        if (isset($_GET['form'])) {
            $view->viewForm($_GET['form']);
        } else {
            $view->viewIndex();
        }
    }
}
