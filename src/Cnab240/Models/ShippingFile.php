<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Cnab240\Models;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Model class for ShippingFile
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/BankInterchange
 */
class ShippingFile extends BankI\Abstracts\Models\Model
{
    /**
     * All titles to be added
     *
     * @var Title[]
     */
    public $titles;
    
    /**
     * Creates a new ShippingFile Model object
     *
     * @param Database $db_address  An interface to `address` database
     * @param Database $db_banki    An interface to `bank_interchange` database
     * @param integer  $assignor_id Assignor's id from database
     * @param integer  $status      Status by which titles will be selected
     */
    public function __construct(
        Utils\Database $db_address,
        Utils\Database $db_banki,
        $assignor_id,
        $status = 0
    ) {
        parent::__construct($db_address, $db_banki, $assignor_id);
        
        // fetch titles
        $this->titles = $cache = [];
        $query = "SELECT `id` FROM `titles` WHERE `assignor` = ? AND `status` = ? ORDER BY `stamp`";
        $titles = array_column(Utils\Database::fetch($db_banki->prepare($query, 'ii', [$assignor_id, $status])), 'id');
        foreach ($titles as $id) {
            $this->titles[$id] = BankI\Objects\Title::fromDatabase($db_address, $db_banki, $id, $cache);
        }
    }
    
    /**
     * Locks a table and returns next auto increment index
     *
     * This method is an alias to parent's with a default table name and should
     * be called without arguments
     *
     * @param string $table The table's name to be locked
     *
     * @return integer
     */
    public function getNextId($table = 'shipping_files')
    {
        return parent::getNextId($table);
    }
    
    /**
     * Inserts the record of the Shipping File
     *
     * @param string $data Shipping File name
     *
     * @return true For success or string[] of errors for failure
     */
    public function insertEntry($data = null)
    {
        $query = "INSERT INTO `shipping_files` (`filename`) VALUES (?)";
        $stmt = $this->db_banki->connect->prepare($query);
        $stmt->bind_param('s', $data);
        $stmt->execute();
        if ($stmt->error !== '') {
            return $stmt->error;
        }
        $this->db_banki->query("UNLOCK TABLES `shipping_files` WRITE");
        return true;
    }
}