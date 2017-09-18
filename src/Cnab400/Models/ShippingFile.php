<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Cnab400\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Model class for CNAB400 ShippingFile
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ShippingFile extends BankI\Abstracts\Models\ShippingFile
{
    /**
     * Which CNAB is being implemented
     *
     * @var string
     */
    const CNAB_NUMBER = '400';
}
