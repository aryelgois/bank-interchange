<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\model;

/**
 * Admin page model
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
class Admin extends Model
{
    /**
     * Creates a new Admin Model object
     */
    public function __construct()
    {
        parent::__construct(['products', 'clients', 'address']);
    }
    
    /**
     * Handles forms sent by POST with a 'submit' argument
     *
     * @return boolean for success or failure
     */
    public function formHandler()
    {
        switch ($_POST['submit']) {
            case 'add_product':
                $this->data['products'][] = [
                    'desc' => $_POST['desc'],
                    'value' => (float)$_POST['value'],
                    'stock' => (integer)$_POST['stock'] ?? 0
                ];
                $this->updateData('products');
                break;
            
            case 'change_stock':
                foreach ($_POST['prod_stock'] as $i => $v) {
                    $this->data['products'][$i]['stock'] = (float)$v ?? 0;
                }
                $this->updateData('products');
                break;
            
            case 'add_client':
                $this->data['clients'][] = [
                    'name' => $_POST['name'],
                    'document' => self::documentValidate($_POST['document']),
                    'address' => [
                        'street' => $_POST['street'],
                        'number' => (integer)$_POST['number'],
                        'neighborhood' => $_POST['neighborhood'],
                        'zipcode' => $_POST['zipcode'],
                        'state' => $_POST['state'],
                        'county' => $_POST['county']
                    ]
                ];
                $this->updateData('clients');
                break;
                //echo '<pre>', var_dump($data), '</pre>'; die();
            
            default:
                return false;
        }
        return true;
    }
}
