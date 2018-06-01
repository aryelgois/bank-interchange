<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\FilePack;

/**
 * Contract accepted by FilePack Controller
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
interface ViewInterface
{
    /**
     * Generates a filename (without extension)
     *
     * @return string
     */
    public function filename();

    /**
     * Returns the View contents
     *
     * @return string
     */
    public function getContents();

    /**
     * Outputs the View with appropriated headers
     *
     * @param string $filename File name to be outputed
     */
    public function outputFile(string $filename);
}
