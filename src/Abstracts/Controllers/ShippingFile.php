<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Abstracts\Controllers;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * A controller to generate Shipping Files
 *
 * ABSTRACTS:
 * - getFilename()
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class ShippingFile extends namespace\Controller
{
    /**
     * Writes the Shipping File to a local file
     *
     * @param string $path Path to directory where the file will be saved
     *
     * @return string Filename or false on failure
     */
    public function save($path)
    {
        $filename = $this->getFilename();
        
        $file = @fopen($path . '/' . $filename, 'w');
        if ($file === false) {
            return false;
        }
        fwrite($file, $this->result);
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Generates the filename to save the Shipping File
     *
     * @return string
     */
    abstract protected function getFilename();
}
