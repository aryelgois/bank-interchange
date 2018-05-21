<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile\Extractors\Cnab400;

use aryelgois\BankInterchange\ReturnFile;

/**
 * Extracts useful data from parsed Banco do Nordeste's CNAB 400 Return Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class BancoDoNordeste extends ReturnFile\Extractors\Cnab400
{
    const CHARGING_TYPES = [
        'cs',
    ];

    /**
     * Computes a human readable occurrence from a title
     *
     * @param ReturnFile\Registry $registry Registry with title's data
     *
     * @return string|null
     */
    protected function occurrence(ReturnFile\Registry $registry)
    {
        $occurrence = parent::occurrence($registry);

        $errors = array_intersect_key(
            $this->config['error_table'],
            array_filter(str_split($registry->error_table))
        );

        return (empty($errors))
            ? $occurrence
            : implode("\n", array_merge([$occurrence ?? 'Erros:'], $errors));
    }
}
