<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\objects;

use aryelgois\utils\Database;

/**
 * A Title defines that a Payer got something from an Assignor.
 *
 * It might be one or products or something the latter does
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1
 */
class Title
{
    /**
     * [desc]
     *
     * @var integer
     */
    public $id;
    
    /**
     * [desc]
     *
     * @var integer
     */
    public $onum;
    
    /**
     * [desc]
     *
     * @var integer
     */
    public $wallet;
    
    /**
     * [desc]
     *
     * @var string
     */
    public $doc_type;
    
    /**
     * [desc]
     *
     * @var integer
     */
    public $kind;
    
    /**
     * [desc]
     *
     * @var integer
     */
    public $specie;
    
    /**
     * [desc]
     *
     * @var float
     */
    public $value;
    
    /**
     * [desc]
     *
     * @var float
     */
    public $iof;
    
    /**
     * [desc]
     *
     * @var float
     */
    public $rebate;
    
    /**
     * [desc]
     *
     * @var string
     */
    public $description;
    
    /**
     * [desc]
     *
     * @var string
     */
    public $due;
    
    /**
     * [desc]
     *
     * @var string
     */
    public $stamp;
    
    /**
     * [desc]
     *
     * @var mixed[]
     */
    public $fine;
    
    /**
     * [desc]
     *
     * @var mixed[]
     */
    public $discount;
    
    /**
     * [desc]
     *
     * @var Payer
     */
    public $payer;
    
    /**
     * [desc]
     *
     * @var Payer
     */
    public $guarantor;
    
    /**
     * Creates a new Title object
     *
     * @param Database $db_cnab240 ..
     * @param Database $db_address ..
     * @param integer  $id         ..
     * @param Payer[]  &$cache     ..
     *
     * @throws RuntimeException If it can not load title from database
     */
    public function __construct(
        Database $db_cnab240,
        Database $db_address,
        $id,
        &$cache = null
    ) {
        // load title
        $title = Database::fetch($db_cnab240->query("SELECT * FROM `titles` WHERE `id` = " . $id));
        if (empty($title)) {
            throw new \RuntimeException('Could not load title from database');
        }
        $title = $title[0];
        
        // is there an easier way?
        $this->id = $title['id'];
        $this->onum = $title['onum'];
        $this->wallet = $title['wallet'];
        $this->doc_type = $title['doc_type'];
        $this->kind = $title['kind'];
        $this->specie = $title['specie'];
        $this->value = (float)$title['value'];
        $this->iof = (float)$title['iof'];
        $this->rebate = (float)$title['rebate'];
        $this->description = $title['description'];
        $this->due = $title['due'];
        $this->stamp = $title['stamp'];
        
        // fine and discount
        $default = ['type' => 3, 'date' => '', 'value' => 0];
        if ($title['fine_type'] == 3) {
            $this->fine = $default;
        } else {
            $this->fine = [
                'type' => $title['fine_type'],
                'date' => $title['fine_date'],
                'value' => (float)$title['fine_value']
            ];
        }
        if ($title['discount_type'] == 3) {
            $this->discount = $default;
        } else {
            $this->discount = [
                'type' => $title['discount_type'],
                'date' => $title['discount_date'],
                'value' => (float)$title['discount_value']
            ];
        }
        
        $this->payer = self::newPayer($db_cnab240, $db_address, $title['payer'], $cache);
        $this->guarantor = ($title['guarantor'] !== null)
            ? self::newPayer($db_cnab240, $db_address, $title['guarantor'], $cache)
            : null;
    }
    
    /**
     * Creates a new Payer object or use from cache
     *
     * @param
     *
     * @return Payer
     */
    protected static function newPayer(
        Database $db_cnab240,
        Database $db_address,
        $id,
        &$cache = null
    ) {
        if (is_array($cache) && array_key_exists($id, $cache)) {
            $payer = $cache[$id];
        } else {
            $payer = new namespace\Payer(
                $db_cnab240,
                $db_address,
                $id
            );
            if (is_array($cache)) {
                $cache[$id] = $payer;
            }
        }
        return $payer;
    }
}
