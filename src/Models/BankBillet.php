<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Model class for BankBillet
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class BankBillet extends namespace\Model
{
    /**
     * The new Title to be inserted
     *
     * @var Title
     */
    public $title;
    
    /**
     * Creates a new BankBillet Model object
     *
     * @param Database $db_address  An interface to `address` database
     * @param Database $db_banki    An interface to `bank_interchange` database
     * @param mixed[]  $config      ...
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $config
    ) {
        $assignor_id = $config['assignor'];
        $title = $config['title'];
        
        parent::__construct($db_address, $db_banki, $assignor_id);
        
        // next id
        $title['id'] = self::getNextId('titles');
        
        // next Onum
        $result = Utils\Database::fetch($db_banki->query("SELECT MAX(`onum`) as `onum` FROM `titles` WHERE `assignor` = " . $assignor_id));
        $title['onum'] = ($result[0]['onum'] ?? 0) + 1;
        
        // create Title
        $this->title = new BankI\Objects\Title(
            $db_address,
            $db_banki,
            $title
        );
        
        // Miscellaneus
        $this->calcDiscountAddition();
    }
    
    /**
     * Calculates Discounts, Deductions, Fines and Additions
     */
    protected function calcDiscountAddition()
    {
        $fields = [
            'addition'  => '+',
            'fine'      => '+',
            'deduction' => '-',
            'discount'  => '-'
        ];
        $sum = 0;
        $trigger = false;
        
        foreach ($fields as $field => $operation) {
            $v = &$this->data['misc'][$field];
            if (is_numeric($v)) {
                $sum += abs($v) * ($operation == '-' ? -1 : 1);
                $v = self::formatMoney(abs($v));
                $trigger = true;
            }
        }
        unset($v);
        if ($trigger) {
            $sum += $this->data['service']['value'] + $this->data['service']['tax'];
            $this->data['misc']['charged'] = self::formatMoney($sum);
        }
    }
}
