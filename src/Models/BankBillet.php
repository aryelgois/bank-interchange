<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\BankBillet\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Model class for BankBillet
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Model extends BankI\Abstracts\Models\Model
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
        //$this->calcDiscountAddition();
    }

    /**
     * Calculates Discounts, Deductions, Fines and Additions
     *
     * NOT USED
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

    /**
     * Inserts the record of the Shipping File
     *
     * NOTES:
     * - Error is handled by Database
     *
     * @param mixed $data It is ignored
     *
     * @return true For success or string[] of errors for failure
     */
    public function insertEntry($data = null)
    {
        $t = $this->title;
        $query = "INSERT INTO `titles` "
               . "(`id`, `assignor`, `payer`, `guarantor`, `wallet`, `specie`, `onum`, `cnab`, `doc_type`, `kind`, `value`, `iof`, `rebate`, `fine_type`, `fine_date`, `fine_value`, `discount_type`, `discount_date`, `discount_value`, `description`, `due`, `stamp`) "
               . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db_banki->prepare(
            $query,
            'iiiiiiissidddisdisdsss',
            [
                $t->id,
                $this->assignor->id,
                $t->payer->id,
                $this->guarantor->id ?? null,
                $t->wallet['id'],
                $t->specie['id'],
                $t->onum,
                $t->cnab,
                $t->doc_type,
                $t->kind,
                $t->value,
                $t->iof,
                $t->rebate,
                $t->fine['type'],
                $t->fine['date'],
                $t->fine['value'],
                $t->discount['type'],
                $t->discount['date'],
                $t->discount['value'],
                substr($t->description, 0, 25),
                $t->due,
                $t->stamp
            ]
        );
        //$this->db_banki->query("UNLOCK TABLES `shipping_files` WRITE");
        return true;
    }
}
