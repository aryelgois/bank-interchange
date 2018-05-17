<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

use aryelgois\Utils;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses Return Files into an array of Registry instances
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Parser
{
    /**
     * Map of CNAB layout to Bank code location in first registry
     *
     * const int[]
     */
    const DETECT_LAYOUT = [
        240 => 0,
        400 => 76,
    ];

    /**
     * Holds loaded configs
     *
     * @var array[]
     */
    protected static $cache = [];

    /**
     * Path to directory with config files
     *
     * @var string
     */
    protected static $config_path;

    /**
     * Bank that generated the Return File
     *
     * @var string
     */
    protected $bank_code;

    /**
     * Which CNAB the Return File might be
     *
     * @var int
     */
    protected $cnab;

    /**
     * Config from $cache being used
     *
     * @var string
     */
    protected $config;

    /**
     * Contains Return File registries
     *
     * @var string[]
     */
    protected $registries;

    /**
     * Contains parsed registries
     *
     * @var mixed[]
     */
    protected $result;

    /**
     * Creates a new return file Parser Object
     *
     * @param string $raw Return File to be parsed
     *
     * @throws \InvalidArgumentException If Return File is empty
     * @throws \UnexpectedValueException If there are non-empty lines after the
     *                                   Return File
     */
    public function __construct(string $raw)
    {
        $return_file = rtrim(str_replace("\r", '', $raw), "\n");
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }
        $registries = explode("\n", $return_file);

        $this->detect($registries);

        foreach ($registries as &$registry) {
            $registry = str_pad($registry, $this->cnab);
        }
        unset($registry);

        $this->config = self::loadConfig($this->cnab, $this->bank_code);
        $this->registries = $registries;

        $this->result = self::parse(self::$cache[$this->config]['structure']);
        $last = $this->result['offset'];
        if ($last < count($registries)) {
            $message = 'Unexpected content at line ' . ($last + 1)
                . ': expecting EOF';
            throw new \UnexpectedValueException($message);
        }
    }

    /**
     * Detects the CNAB layout and the Bank Code
     *
     * @param string[] $registries Return File registries
     *
     * @throws \DomainException If could not detect Return File layout
     */
    protected function detect(array $registries)
    {
        $length = max(array_map('strlen', $registries));

        $found = false;
        foreach (self::DETECT_LAYOUT as $cnab => $start) {
            if ($length <= $cnab) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \DomainException('Could not detect Return File layout');
        }

        $this->bank_code = substr($registries[0], $start, 3);
        $this->cnab = $cnab;
    }

    /**
     * Loads YAML config file into cache
     *
     * @param int    $cnab      CNAB layout
     * @param string $bank_code Bank code
     *
     * @return string $cache key
     *
     * @throws \BadMethodCallException       If called before setConfigPath()
     * @throws \RuntimeException             If config file does not exist
     * @throws Yaml\Exception\ParseException If could not load config file
     */
    protected static function loadConfig($cnab, $bank_code)
    {
        if (self::$config_path === null) {
            throw new \BadMethodCallException('Config path is null');
        }

        $key = "$cnab/$bank_code";

        if (!array_key_exists($key, self::$cache)) {
            $config_file = self::$config_path . "/cnab$key.yml";
            if (file_exists($config_file)) {
                self::$cache[$key] = Yaml::parseFile($config_file);
            } else {
                throw new \RuntimeException(sprintf(
                    'Config file for Bank %s in CNAB%s not found',
                    $bank_code,
                    $cnab
                ));
            }
        }

        return $key;
    }

    /**
     * Outputs parsed registries
     *
     * @return mixed[]
     */
    public function output()
    {
        return [
            'bank_code' => $this->bank_code,
            'cnab' => $this->cnab,
            'registries' => $this->result['registries'],
        ];
    }

    /**
     * Recursively follows the Return File structure to extract its fields
     *
     * @param array  $structure Structure tree to be used
     * @param int    $offset    Current item in $registries
     *
     * @return mixed[]
     *
     * @throws ParseException For invalid registry
     */
    protected function parse(array $structure, int $offset = null)
    {
        $result = [];
        $current = $offset ?? 0;

        $count_structure = 0;
        foreach ($structure as $registry_group) {
            if (is_array($registry_group)) {
                $buffer = [];
                do {
                    try {
                        $rec = self::parse($registry_group, $current);
                        $nested = (count($registry_group) > 1)
                            ? [$rec['registries']]
                            : $rec['registries'];
                        $buffer = array_merge($buffer, $nested);
                        $current = $rec['offset'];
                    } catch (ParseException $e) {
                        if (count($buffer) === 0) {
                            throw $e;
                        } else {
                            $first = $registry_group[0];
                            if (is_string($first)) {
                                $first = explode(' ', $first);
                            }
                            $in_first = array_intersect(
                                $first,
                                $e->getRegistries()
                            );
                            if (count($in_first) === 0) {
                                throw $e;
                            }
                            break;
                        }
                    }
                } while ($current < count($this->registries));
                $nested = (count($registry_group) > 1)
                    ? $buffer
                    : [$buffer];
                $result = array_merge($result, $nested);
            } else {
                $types = explode(' ', $registry_group);
                $registry = self::pregRegistry($current, $types);

                if ($registry !== null) {
                    $result[] = $registry;
                    $current++;
                } elseif (count($result) === 0 || $count_structure > 0) {
                    throw new ParseException(
                        $this->config,
                        $types,
                        $current + 1
                    );
                }
            }
            $count_structure++;
        }

        return [
            'offset' => $current,
            'registries' => $result,
        ];
    }

    /**
     * Extracts fields from a Return File registry to fill a Registry instance
     *
     * @param int      $id    $registries id to be parsed
     * @param string[] $types Registry types to test
     *
     * @return Registry On success
     * @return null     On failure
     */
    protected function pregRegistry(int $id, array $types)
    {
        $registry_types = Utils\Utils::arrayWhitelist(
            self::$cache[$this->config]['registries'],
            $types
        );

        $result = null;
        foreach ($registry_types as $type => $matcher) {
            if (preg_match(
                $matcher['pattern'],
                $this->registries[$id] ?? '',
                $matches
            )) {
                $match = array_combine(
                    $matcher['map'],
                    array_map('trim', array_slice($matches, 1))
                );
                $result = new Registry($this->cnab, $type, $match);
                break;
            }
        }
        return $result;
    }

    /**
     * Sets path to directory with config files
     *
     * @param string $path Path to directory with config files
     */
    public static function setConfigPath(string $path)
    {
        self::$config_path = $path;
        self::$cache = [];
    }
}
