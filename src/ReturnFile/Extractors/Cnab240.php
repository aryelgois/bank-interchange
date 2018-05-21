<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile\Extractors;

use aryelgois\BankInterchange\ReturnFile;

/**
 * Extracts useful data from parsed CNAB 240 Return Files
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
abstract class Cnab240 extends ReturnFile\Extractor
{
    /**
     * Extracts data about the titles charging
     *
     * @return mixed[]
     */
    protected function extractCharging()
    {
        $lot_result = [];

        $count = count($this->registries) - 1;
        for ($lot_id = 1; $lot_id < $count; $lot_id++) {
            $buffer = [];
            $registry = $this->registries[$lot_id][2];

            foreach (static::CHARGING_TYPES as $type) {
                $count = "${type}_count";
                $total = "${type}_total";

                $buffer = array_merge(
                    $buffer,
                    [
                        $count => (int) $registry->{$count},
                        $total => (float) ($registry->{$total} / 100.0),
                    ]
                );
            }
            $buffer['warning'] = (int) $registry->warning;

            $lot_result[] = $buffer;
        }

        $result = array_shift($lot_result);
        foreach ($lot_result as $arr) {
            foreach ($arr as $key => $val) {
                $result[$key] += $val;
            }
        };

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
        $count = count($this->registries) - 1;
        for ($lot_id = 1; $lot_id < $count; $lot_id++) {
            $buffer = [];

            foreach ($this->registries[$lot_id][1] as $registry) {
                switch ($registry->getType()) {
                    case 'TITLE_T':
                        $data = [
                            'assignment' => $this->detectAssignment($registry)->id ?? null,
                            'our_number' => (int) $registry->our_number,
                            'value' => (float) ($registry->value / 100.0),
                            'tax' => (float) ($registry->tax / 100.0),
                            'occurrence' => $this->occurrence($registry) ?? $registry->occurrence,
                        ];
                        break;

                    case 'TITLE_U':
                        $data = [
                            'value_received' => (float) ($registry->value_received / 100.0),
                            'occurrence_date' => static::parseDate($registry->occurrence_date),
                        ];
                        break;
                }

                $key = $registry->lot_registry;
                $buffer[$key] = array_merge($buffer[$key] ?? [], $data);
            }

            $result = array_merge($result, array_values($buffer));
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
     * @return string
     */
    protected function occurrence(ReturnFile\Registry $registry)
    {
        $config = $this->config;

        $movement = $registry->movement;
        $occurrence = $registry->occurrence;

        $result = [
            "Movimento $movement",
            "Ocorrência $occurrence",
        ];
        $glue = ', ';

        $tmp = $config['movement'][$movement] ?? null;
        if ($tmp !== null) {
            $result[0] = $tmp;
            $glue = ': ';
        }

        $group = $config['movement_to_occurrence'][$movement] ?? null;
        if (array_key_exists($group, $config['occurrence'])) {
            $tmp = $config['occurrence'][$group][$occurrence] ?? null;
            if ($tmp !== null) {
                $result[1] = $tmp;
            }
        }

        return implode($glue, $result);
    }
}
