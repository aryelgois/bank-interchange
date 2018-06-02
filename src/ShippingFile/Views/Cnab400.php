<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile\Views;

use aryelgois\BankInterchange;
use aryelgois\BankInterchange\Models;

/**
 * Generates CNAB400 shipping file to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Cnab400 extends BankInterchange\ShippingFile\View
{
    const TITLE_LIMIT = 999997;

    /**
     * Does initial steps for creating a shipping file
     */
    protected function open()
    {
        $this->registry_count++;
        $this->registries[] = $this->generateHeader();
    }

    /**
     * Adds a Title registry
     *
     * @param Models\Title $title Contains data for the registry
     */
    protected function add(Models\Title $title)
    {
        $this->registry_count++;
        $this->registries[] = $this->generateTransaction($title);
    }

    /**
     * Does final steps for creating a shipping file
     */
    protected function close()
    {
        $this->registry_count++;
        $this->registries[] = $this->generateTrailer();
    }

    /*
     * Abstracts
     * =========================================================================
     */

    /**
     * Generates Header registry
     *
     * @return string
     */
    abstract protected function generateHeader();

    /**
     * Generates Transaction registry
     *
     * @param Models\Title $title Contains data for the registry
     *
     * @return string
     */
    abstract protected function generateTransaction(Models\Title $title);

    /**
     * Generates Trailer registry
     *
     * @return string
     */
    abstract protected function generateTrailer();
}
