<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile\Extractors\Cnab240;

use aryelgois\BankInterchange\ReturnFile;

/**
 * Extracts useful data from parsed Banese's CNAB 240 Return Files
 *
 * NOTE:
 * - detectAssignment() may fail because 'assignment_agency' and
 *   'assignment_account' usually are empty
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Banese extends ReturnFile\Extractors\Cnab240
{
    const CHARGING_TYPES = [
        'cs',
        'cv',
        'cc',
        'cd',
    ];
}
