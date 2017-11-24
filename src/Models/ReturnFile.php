<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;
use VRia\Utils\NoDiacritic;

/**
 * Interprets Return Files sent by banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class ReturnFile
{
    /**
     * Contains the Return File, with lines splitted
     *
     * @var string[]
     */
    protected $return_file;

    /**
     * Tells how to extract data from Return File and the meaning of some fields
     *
     * @var array[]
     */
    protected $config;

    /**
     * Which CNAB the Return File might be
     *
     * @var string
     */
    protected $cnab;

    /**
     * Which registries are enabled during validate()
     *
     * @var string[]
     */
    private $matcher_enabled;

    /**
     * All the data matched from Return File
     *
     * @var array[]
     */
    protected $matches;

    /**
     * Human readable messages post validation
     *
     * There are 'info', 'error' and 'warning' keys
     *
     * @var array[]
     */
    protected $messages = [];

    /**
     * Registries data
     *
     * @var array[]
     */
    protected $registries;

    /**
     * Changes to be applied in Titles
     *
     * @var array[]
     */
    protected $changes;

    /**
     * Creates a new ReturnFile Controller object
     *
     * @param string  $return_file The Return File to be processed
     * @param array[] $config      @see self::$config
     *
     * @throws \InvalidArgumentException If $return_file is invalid
     */
    public function __construct($return_file, $config)
    {
        /*
         * Splits lines and removes empty lines
         */
        $return_file = array_filter(
            explode("\n", str_replace("\r", '', $return_file))
        );
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        /*
         * Lines length
         */
        $lengths = array_map('strlen', $return_file);

        /*
         * Defines CNAB by longest line
         */
        $cnab = max($lengths);
        if (!array_key_exists($cnab, $config)) {
            throw new \InvalidArgumentException(
                'Invalid CNAB: ' . $cnab . ' positions'
            );
        }

        /*
         * Pads shorter lines (maybe are missing ' '. if not, will fail later)
         */
        $shorter = array_filter($lengths, function ($len) use ($cnab) {
            return $len != $cnab;
        });
        foreach (array_keys($shorter) as $line) {
            $return_file[$line] = str_pad($return_file[$line], $cnab);
        }

        /*
         * Store data
         */
        $this->return_file = $return_file;
        $this->cnab = $cnab;
        $this->config = $config[$cnab];
        $this->messages = [
            'error' => [],
            'info' => [],
            'warning' => [],
        ];
        $this->registries = [
            'meta' => [],
            'lots' => [],
        ];

        /*
         * Validate Return File
         */
        $this->validate();

        /*
         * analyze Return File
         */
        $this->analyze();
    }

    /**
     * Returns stored messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Applies changes to Titles in the Database
     */
    public function apply()
    {
        foreach($this->changes as $title_id => $data) {
            # code...
        }
    }

    /**
     * Validates the Return File registries
     */
    protected function validate()
    {
        $this->matcher_enabled = ['file_header'];
        $matcher_enabled_old = null;
        foreach ($this->return_file as $line => $registry) {
            $matched = false;
            if ($matcher_enabled_old != $this->matcher_enabled) {
                $matcher = Utils\Utils::arrayWhitelist(
                    $this->config['matcher'],
                    $this->matcher_enabled
                );
                $matcher_enabled_old = $this->matcher_enabled;
            }
            foreach ($matcher as $matcher_name => $matcher_data) {
                if (preg_match($matcher_data['pattern'], $registry, $matches)) {
                    $match = array_combine(
                        $matcher_data['map'],
                        array_map('trim', array_slice($matches, 1))
                    );
                    $matched = $matcher_name;
                    break;
                }
            }

            if ($matched) {
                $this->process($line, $matched, $match);
            } else {
                $this->messages['error'][] = 'Registry mismatch on line ' . ($line + 1);
            }
        }
    }

    /**
     * Specific operations
     */
    protected function process($line, $matched, $match)
    {
        switch ($this->cnab) {
            case 240:
                switch ($matched) {
                    case 'file_header':
                        $meta = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'record_date',
                                'record_time',
                                'file_sequence',
                                'assignor_use',
                            ]
                        );

                        $assignor = $this->findAssignor($line, $match);
                        if ($assignor) {
                            $meta['assignor'] = $assignor;
                        }

                        $this->registries['meta'] = $meta;
                        $this->matcher_enabled = ['lot_header', 'file_trailer'];
                        break;

                    case 'lot_header':
                        $lot = [
                            'meta' => Utils\Utils::arrayWhitelist(
                                $match,
                                [
                                    'message1',
                                    'message2',
                                    'shipping_return_number',
                                    'shipping_return_record_date',
                                    'credit_date',
                                ]
                            ),
                            'data' => [],
                            'registries' => 1,
                        ];

                        $assignor = $this->findAssignor($line, $match);
                        if ($assignor) {
                            $lot['meta']['assignor'] = $assignor;
                        }

                        $this->registries['lots'][(int) $match['lot']] = $lot;
                        $this->matcher_enabled = [
                            'title_t',
                            'title_u',
                            'lot_trailer'
                        ];
                        break;

                    case 'title_t':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'movement',
                                'doc_number',
                                'our_number',
                                'due',
                                'value',
                                'receiver_bank',
                                'receiver_agency',
                                'receiver_agency_cd',
                                'assignor_use',
                                'specie',
                                'contract',
                                'tax',
                                'occurrence',
                            ]
                        );

                        $title = $this->findTitle($line, $match);
                        if ($title) {
                            $data['title'] = $title;
                        }

                        $this->registries['lots'][(int) $match['lot']]['data'][(int) $match['lot_registry']] = array_merge(
                            $this->registries['lots'][(int) $match['lot']]['data'][(int) $match['lot_registry']] ?? [],
                            $data
                        );
                        $this->registries['lots'][(int) $match['lot']]['registries']++;
                        break;

                    case 'title_u':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'value_paid',
                                'value_net',
                                'expenses',
                                'credits',
                                'occurrence_date',
                                'credit_date',
                                'payer_occurrence_code',
                                'payer_occurrence_date',
                                'payer_occurrence_value',
                                'payer_occurrence_detail',
                                'corresponding_bank',
                                'corresponding_bank_our_number',
                            ]
                        );

                        $this->registries['lots'][(int) $match['lot']]['data'][(int) $match['lot_registry']] = array_merge(
                            $this->registries['lots'][(int) $match['lot']]['data'][(int) $match['lot_registry']] ?? [],
                            $data
                        );
                        $this->registries['lots'][(int) $match['lot']]['registries']++;
                        break;

                    case 'lot_trailer':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'title_cs_count',
                                'title_cs_total',
                                'title_cv_count',
                                'title_cv_total',
                                'title_cc_count',
                                'title_cc_total',
                                'title_cd_count',
                                'title_cd_total',
                                'warning',
                            ]
                        );

                        if (++$this->registries['lots'][(int) $match['lot']]['registries'] != (int) $match['lot_registry_count']) {
                            $this->messages['error'][] = "Lot {$match['lot']} has different ammount of registries from it's Trailer";
                        }

                        $this->registries['lots'][(int) $match['lot']]['meta'] = array_merge(
                            $this->registries['lots'][(int) $match['lot']]['meta'] ?? [],
                            $data
                        );
                        $this->matcher_enabled = [
                            'lot_header',
                            'file_trailer'
                        ];
                        break;

                    case 'file_trailer':
                        if (count($this->registries['lots']) != $match['lot_count']) {
                            $this->messages['error'][] = "Lot count differ";
                        }

                        $registry_count = array_sum(
                            array_column(
                                $this->registries['lots'],
                                'registries'
                            )
                        );
                        if ($registry_count + 2 != $match['registry_count']) {
                            $this->messages['error'][] = "Registry count differ";
                        }

                        $this->matcher_enabled = [];
                        break;
                }
                break;

            case 400:
                switch ($matched) {
                    case 'file_header':
                        $meta = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'record_date',
                                'agency_account',
                                'assignor_document',
                                'file_sequence',
                            ]
                        );
                        $lot = [
                            'meta' => [],
                            'data' => [],
                        ];

                        // how the assignor account is stored differ from banks

                        $this->registries['meta'] = $meta;
                        $this->registries['lots'][0] = $lot;
                        $this->matcher_enabled = ['title', 'file_trailer'];
                        break;

                    case 'title':
                        $data = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'assignor_use',
                                'our_number',
                                'occurrence',
                                'occurrence_date',
                                'your_number',
                                'due',
                                'value',
                                'receiver_bank',
                                'receiver_agency',
                                'tax',
                                'expenses',
                                'value_paid',
                                'late_fine',
                                'credits',
                                'late_confirm',
                                'late_confirm_charge',
                                'discount_confirm',
                                'discount_value_confirm',
                                'instruction1_confirm',
                                'instruction2_confirm',
                                'protest_confirm',
                                'specie',
                            ]
                        );

                        $title = $this->findTitle($line, $match);
                        if ($title) {
                            $data['title'] = $title;
                        }

                        $this->registries['lots'][0]['data'][(int) $match['registry']] = $data;
                        $this->matcher_enabled = ['title', 'file_trailer'];
                        break;

                    case 'file_trailer':
                        $meta = Utils\Utils::arrayWhitelist(
                            $match,
                            [
                                'title_cs_count',
                                'title_cs_total',
                                'warning_cs',
                                'title_cv_count',
                                'title_cv_total',
                                'warning_cv',
                                'title_cc_count',
                                'title_cc_total',
                                'warning_cc',
                                'title_cd_count',
                                'title_cd_total',
                                'warning_cd',
                            ]
                        );

                        $count = $match['title_cs_count']
                               + $match['title_cv_count']
                               + $match['title_cc_count']
                               + $match['title_cd_count'];

                        if (count($this->registries['lots'][0]['data']) != $count) {
                            $this->messages['error'][] = "Title count differ";
                        }

                        $this->registries['lots'][0]['meta'] = $meta;
                        $this->matcher_enabled = [];
                        break;
                }
                break;
        }
        $this->matches[] = $match;
    }

    /**
    * Analyzes each registry in the Return File
    *
    * It looks for messages from the Bank and for changes to apply in the
    * Database.
    */
    protected function analyze()
    {
        if ($this->cnab == 240) {
            $movements = $this->config['fields']['movement'];
            $movements_flatten = array_replace(...array_values($movements));
        }
        $occurrences = $this->config['fields']['occurrence'];

        foreach ($this->registries['lots'] as $lot_id => $lot) {
            foreach ($lot['data'] as $registry_id => $registry) {
                $message = [
                    'our_number' => $registry['our_number'],
                ];
                $date = [
                    'format' => '',
                    'value' => null
                ];
                switch ($this->cnab) {
                    case 240:
                        $movement = $registry['movement'];
                        $occurrence = $registry['occurrence'];

                        $message['movement'] = $movements_flatten[$movement];
                        $occurrence_group = null;
                        foreach ($this->config['fields']['relation'] as $group => $list) {
                            if (in_array($movement, $list)) {
                                $occurrence_group = $group;
                                break;
                            }
                        }

                        if ($occurrence_group) {
                            $message['occurrence'] = $occurrences[$occurrence_group][$occurrence];
                        } else {
                            $this->messages['warning'][] = 'Unknown occurrence in registry ' . $registry_id . ' (lot ' . $lot_id . ')';
                        }

                        if (isset($registry['occurrence_date'])) {
                            $date['format'] = 'dmY';
                            $date['value'] = $registry['occurrence_date'];
                        }

                        if (isset($registry['title'])) {
                            if (array_key_exists($movement, $movements['error'])) {
                                $this->changes[$registry['title']->id] = [
                                    'status' => 1
                                ];
                            } elseif (array_key_exists($movement, $movements['info'])) {
                                $data = ['status' => 0];
                                if (isset($registry['value_paid'])) {
                                    $data['value_paid'] = ($registry['value_paid'] / 10 ** $registry['title']->specie->decimals);
                                }
                                $this->changes[$registry['title']->id] = $data;
                            } else {
                                $this->messages['warning'][] = 'Could not identify movement in registry ' . $registry_id . ' (lot ' . $lot_id . ')';
                            }
                        }
                        break;

                    case 400:
                        $occurrence = $registry['occurrence'];

                        $message['occurrence'] = $this->config['fields']['occurrence'][$occurrence];

                        if (isset($registry['occurrence_date'])) {
                            $date['format'] = 'dmy';
                            $date['value'] = $registry['occurrence_date'];
                        }

                        if (isset($registry['title'])) {
                            if ($occurrence == "51") {
                                $this->changes[$registry['title']->id] = [
                                    'status' => 1
                                ];
                            } else {
                                $data = ['status' => 0];
                                $value_paid = $registry['value_paid'] / 10 ** $registry['title']->specie->decimals;
                                if ($value_paid > 0) {
                                    $data['value_paid'] = $value_paid;
                                }
                                $this->changes[$registry['title']->id] = $data;
                            }
                        }
                        break;
                }
                if ($date['value']) {
                    $date = \DateTime::createFromFormat(...array_values($date));
                    if ($date) {
                        $message['occurrence_date'] = $date->format('Y-m-d');
                    }
                }
                $this->messages['info'][] = $message;
            }
        }
    }

    /*
     * Helper
     * =========================================================================
     */

    protected function findAssignor($line, $match)
    {
        $assignor = new BankI\Models\Assignor;
        $database = $assignor->getDatabase();

        // Find based on document and covenant
        $result = $database->select(
            'assignors',
            [
                '[><]people' => ['person' => 'id'],
            ],
            ['assignors.id'],
            [
                'people.document[~]' => (int) $match['assignor_document'],
                'assignors.covenant[~]' => (int) $match['assignor_covenant'],
            ]
        );

        if (empty($result)) {
            $this->messages['warning'][] = 'Assignor in line ' . ($line + 1) . ' not found in the Database';
        } else {
            $id = $result[0]['id'];
            if (count($result) > 1) {
                $this->messages['warning'][] = 'Multiple Assignors found in line ' . ($line + 1) . ". Using id $id";
            }
            $assignor->load($id);
            return $assignor;
        }
    }

    protected function findTitle($line, $match)
    {
        $title = new BankI\Models\Title;

        $loaded = $title->load(
            ['our_number[~]' => (int) $match['our_number']]
        );

        if (!$loaded) {
            $this->messages['warning'][] = 'Our number in line ' . ($line + 1) . ' not found in the Database';
        } else {
            return $title;
        }
    }
}
