<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example\view;

use aryelgois\utils;

/**
 * Core class for example's views
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
abstract class View
{
    /**
     * Path to twig templates
     *
     * @const string
     */
    const TWIG_CACHE = __DIR__ . '/../../../cache';
    
    /**
     * Path to twig templates
     *
     * @const string
     */
    const TWIG_TEMPLATES = __DIR__ . '/../templates';
    
    /**
     * Twig Environment
     *
     * @var \Twig_Environment
     */
    public $twig;
    
    /**
     * Creates a new View object
     */
    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(self::TWIG_TEMPLATES);
        $this->twig = new \Twig_Environment(
            $loader,
            [
                'cache' => self::TWIG_CACHE,
                'auto_reload' => true
            ]
        );
    }
    
    /**
     * Generates <option> from an array
     *
     * @return string[] $data Array with data to be formatted
     */
    protected static function genOptions($data)
    {
        $result = [];
        foreach ($data as $value => $text) {
            $result[] = '<option value="' . $value . '">' . $text . '</option>';
        }
        return implode('', $result);
    }
    
    /**
     * Generates <option> from an array
     *
     * @param string $document Brazilian document to be formatted
     *
     * @return string
     */
    protected static function formatDocument($document)
    {
        $len = strlen($document);
        if ($len == 11) {
            return utils\Format::cpf($document);
        } elseif ($len == 14) {
            return utils\Format::cnpj($document);
        }
        return $document;
    }
}
