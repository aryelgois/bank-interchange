<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile\Extractors;

use aryelgois\BankInterchange\ReturnFile;

/**
 * Extracts useful data from parsed CNAB 400 Return Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Cnab400 extends ReturnFile\Extractor
{
    const DATE_FORMAT = 'dmy';

    /**
     * Extracts data about the titles charging
     *
     * @return mixed[]
     */
    protected function extractCharging()
    {
        $result = [];
        $trailer = $this->registries[2];

        foreach (static::CHARGING_TYPES as $type) {
            $count = "${type}_count";
            $total = "${type}_total";
            $warning = "${type}_warning";

            $result = array_merge(
                $result,
                [
                    $count => (int) $trailer->{$count},
                    $total => (float) ($trailer->{$total} / 100.0),
                    $warning => (int) $trailer->{$warning},
                ]
            );
        }

        return $result;
    }

    /**
     * Extracts data about each Title
     *
     * @return array[]
     */
    protected function extractTitles()
    {
        $result = [];

        foreach ($this->registries[1] as $id => $title) {
            $result[] = [
                'assignment' => $this->detectAssignment($title)->id ?? null,
                'our_number' => (int) $title->our_number,
                'value' => (float) ($title->value / 100.0),
                'tax' => (float) ($title->tax / 100.0),
                'value_received' => (float) ($title->value_received / 100.0),
                'occurrence' => $this->occurrence($title) ?? $title->occurrence,
                'occurrence_date' => static::parseDate($title->occurrence_date),
            ];
        }

        return $result;
    }

    /*
     * Helper
     * =========================================================================
     */

    /**
     * Computes a human readable occurrence from a title
     *
     * @param ReturnFile\Registry $registry Registry with title's data
     *
     * @return string|null
     */
    protected function occurrence(ReturnFile\Registry $registry)
    {
        return $this->config['occurrence'][$registry->occurrence] ?? null;
    }
}
