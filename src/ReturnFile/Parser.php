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
     * Which CNAB the Return File might be
     *
     * @var int
     */
    protected $cnab;

    /**
     * Loaded parser config
     *
     * @var array[]
     */
    protected $config;

    /**
     * Contains Return File registries
     *
     * @var string[]
     */
    protected $return_file;

    /**
     * Contains parsed registries
     *
     * @var array[]
     */
    protected $result;

    /**
     * Creates a new return file Parser Object
     *
     * @param string $raw Return File to be parsed
     *
     * @throws \InvalidArgumentException     If Return File is empty
     * @throws \BadMethodCallException       @see loadConfig()
     * @throws \RuntimeException             @see loadConfig()
     * @throws Yaml\Exception\ParseException If could not load config file
     */
    public function __construct(string $raw)
    {
        $return_file = explode("\n", rtrim(str_replace("\r", '', $raw), "\n"));
        if (empty($return_file)) {
            throw new \InvalidArgumentException('Return File is empty');
        }

        $length = max(array_map('strlen', $return_file));
        foreach ($return_file as &$line) {
            $line = str_pad($line, $length);
        }
        unset($line);

        $cnab = ($length <= 240 ? 240 : 400);
        $bank_code = substr($return_file[0], ($cnab === 240 ? 0 : 76), 3);

        $this->cnab = $cnab;
        $this->config = self::loadConfig($cnab, $bank_code);
        $this->return_file = $return_file;

        $this->result = self::parse(self::$cache[$this->config]['structure']);
    }

    /**
     * Loads YAML config file into cache
     *
     * @param int    $cnab      CNAB layout
     * @param string $bank_code Bank code
     *
     * @return string $cache key
     *
     * @throws \BadMethodCallException If called before setConfigPath()
     * @throws \RuntimeException       If config file does not exist
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
     * @return array[]
     */
    public function output()
    {
        return $this->result['registries'];
    }

    /**
     * Recursively follows the Return File structure to extract its fields
     *
     * @param array  $structure Structure tree to be used
     * @param int    $offset    Current item in $return_file
     *
     * @return array With keys 'offset' and 'registries'
     *
     * @throws \DomainException For invalid structure amount
     * @throws ParseException   For invalid registry
     */
    protected function parse(array $structure, int $offset = null)
    {
        $result = [];
        $offset = $offset ?? 0;

        foreach ($structure as $registry_group) {
            $type = reset($registry_group);
            $amount = key($registry_group);

            if (!in_array($amount, ['unique', 'multiple'])) {
                throw new \DomainException(sprintf(
                    "Invalid structure amount '%s' in CNAB%s",
                    $amount,
                    $this->config
                ));
            }

            if (!is_array($type)) {
                $registries = Utils\Utils::arrayWhitelist(
                    self::$cache[$this->config]['registries'],
                    explode(' ', $type)
                );
            }

            do {
                $previous_offset = $offset;
                if (is_array($type)) {
                    try {
                        $rec = self::parse($type, $offset);
                        $result = array_merge($result, [$rec['registries']]);
                        $offset = $rec['offset'];
                    } catch (ParseException $e) {
                        if ($amount !== 'multiple') {
                            throw $e;
                        }
                    }
                } else {
                    $registry = self::pregRegistry(
                        $this->return_file[$offset],
                        $registries
                    );

                    if ($registry !== null) {
                        $result[] = $registry;
                        $offset++;
                    } elseif ($amount === 'unique') {
                        throw ParseException::pregMismatch(
                            $this->cnab,
                            $registries,
                            $offset + 1
                        );
                    }
                }
            } while ($amount === 'multiple' && $offset > $previous_offset);
        }

        return [
            'offset' => $offset,
            'registries' => $result,
        ];
    }

    /**
     * Extracts fields from a Return File registry to fill a Registry instance
     *
     * @param string $registry   Line with fields to be extracted
     * @param array  $registries Sequence of pattern and map
     *
     * @return Registry On success
     * @return null     On failure
     */
    protected function pregRegistry(
        string $registry,
        array $registries
    ) {
        $result = null;
        foreach ($registries as $type => $matcher) {
            if (preg_match($matcher['pattern'], $registry, $matches)) {
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
