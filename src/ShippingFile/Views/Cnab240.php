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
 * Generates CNAB240 shipping file to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Cnab240 extends BankInterchange\ShippingFile\View
{
    const TITLE_LIMIT = 999977;

    /**
     * FEBRABAN's version of file layout
     *
     * @const string
     */
    const VERSION_FILE_LAYOUT = '101';

    /**
     * FEBRABAN's version of lot layout
     *
     * @const string
     */
    const VERSION_LOT_LAYOUT = '060';

    /**
     * Count lots in the file
     *
     * @var integer
     */
    protected $lot_count = 0;

    /**
     * Count registries in the current lot
     *
     * @var int
     */
    protected $current_lot;

    /**
     * Does initial steps for creating a shipping file
     */
    protected function open()
    {
        $this->registry_count++;
        $this->registries[] = $this->generateFileHeader();
    }

    /**
     * Adds a Title registry
     *
     * @param Models\ShippingFileTitle $sft Contains data for the registry
     */
    protected function add(Models\ShippingFileTitle $sft)
    {
        if ($this->current_lot === 99999) {
            $this->registry_count++;
            $this->current_lot += 2;
            $this->registries[] = $this->generateLotTrailer();
            $this->current_lot = null;
        }

        if ($this->current_lot === null) {
            $this->registry_count++;
            $this->lot_count++;
            $this->current_lot = 0;
            $this->registries[] = $this->generateLotHeader();
        }

        $this->registry_count++;
        $this->current_lot++;

        $this->registries = array_merge(
            $this->registries,
            $this->generateLotDetail($sft)
        );
    }

    /**
     * Does final steps for creating a shipping file
     */
    protected function close()
    {
        if ($this->current_lot !== null) {
            $this->registry_count++;
            $this->current_lot += 2;
            $this->registries[] = $this->generateLotTrailer();
            $this->current_lot = null;
        }

        $this->registry_count++;
        $this->registries[] = $this->generateFileTrailer();
    }

    /*
     * Abstracts
     * =========================================================================
     */

    /**
     * Generates FileHeader registry
     *
     * @return string
     */
    abstract protected function generateFileHeader();

    /**
     * Generates LotHeader registry
     *
     * @return string
     */
    abstract protected function generateLotHeader();

    /**
     * Generates LotDetail registry
     *
     * @param Models\ShippingFileTitle $sft Contains data for the registry
     *
     * @return string[]
     */
    abstract protected function generateLotDetail(
        Models\ShippingFileTitle $sft
    );

    /**
     * Generates LotTrailer registry
     *
     * @return string
     */
    abstract protected function generateLotTrailer();

    /**
     * Generates FileTrailer registry
     *
     * @return string
     */
    abstract protected function generateFileTrailer();
}
