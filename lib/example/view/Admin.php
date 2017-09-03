<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\view;

/**
 * Admin page view
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
class Admin extends View
{
    
    /**
     * Twig Environment
     *
     * @var \Twig_Environment
     */
    public $model;
    
    /**
     * Creates a new Admin View object
     *
     * @param Model $model Holds data to be inserted in the view
     */
    public function __construct($model)
    {
        parent::__construct();
        
        $this->model = $model;
    }
    
    /**
     * Renders the admin panel
     */
    public function viewIndex()
    {
        $clients = $this->model->data['clients'];
        foreach ($clients as &$client) {
            $client['document'] = self::formatDocument($client['document']);
        }
        unset($client);
        
        echo $this->twig->render(
            'admin.html',
            [
                'title' => 'Admin - ',
                'products' => $this->model->data['products'],
                'clients' => $clients
            ]
        );
    }
    
    /**
     * Renders an admin form
     *
     * @param string $form The form to be viewed
     */
    public function viewForm($form)
    {
        $contents = [
            'title' => ''
        ];
        
        switch ($form) {
            case 'add_product':
                $contents['title'] = 'New product';
                break;
            case 'add_client':
                $contents['title'] = 'New client';
                $contents['states'] = $this->model->data['address']['states'];
                break;
            default:
                $this->viewIndex();
                return;
        }
        $contents['title'] .= ' - Admin - ';
        
        echo $this->twig->render('admin_' . $form . '.html', $contents);
    }
}
