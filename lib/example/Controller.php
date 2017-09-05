<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240\example;

use aryelgois\utils;
use aryelgois\objects;
use aryelgois\cnab240;

/**
 * A basic controller to create the shipping file
 *
 * NOTES:
 * - May be moved to src/
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.1.1
 */
class Controller
{
    protected $database_address;
    protected $database_cnab240;
    protected $cache;
    protected $assignor;
    protected $bank;
    
    public function __construct($db_path, $assignor)
    {
        $this->database_address = new Database($db_path, 'address');
        $this->database_cnab240 = new Database($db_path, 'cnab240');
        $this->cache = [
            'payers' => [],
            'services' => []
        ];
        
        $this->assignor = new cnab240\objects\Assignor($this->database_cnab240, $assignor);
        
        $this->bank = new cnab240\objects\Bank($this->database_cnab240, $this->assignor->bank);
    }
    
    public function execute()
    {
        $query_transactions = "SELECT * FROM `transactions` WHERE `status` = 0 ORDER BY `stamp` LIMIT 9997";
        while (!empty($rows = Database::fetch($this->database_cnab240->query($query_transactions)))) {
            // @todo Get id for new row in databse -> table `shipping_files`
            $file_id = 1;
            
            // generate shipping file and cache data
            $transactions = [];
            $shipping_file = new cnab240\ShippingFile($this->bank, $this->assignor, $file_id);
            foreach ($rows as $row) {
                $transactions[] = $row['id'];
                
                if (!array_key_exists($row['payer'], $this->cache['payers'])) {
                    $this->cache['payers'][$row['payer']] = new cnab240\objects\Payer($this->database_cnab240, $this->database_address, $row['payer']);
                }
                $payer = $this->cache['payers'][$row['payer']];
                
                $items = Database::fetch($this->database_cnab240->query("SELECT `service` FROM `transaction_items` WHERE `transaction` = " . $row['id']));
                foreach ($items as $item) {
                    if (!array_key_exists($item['service'], $this->cache['services'])) {
                        $this->cache['services'][$item['service']] = new cnab240\objects\Service($this->database_cnab240, $item['service']);
                    }
                    $service = $this->cache['services'][$item['service']];
                    
                    //$shipping_file->addDetail($payer, $service);
                }
            }
            //$shipping_file->close();
            
            // save file
            $filename = 'COB.240.'
                      . cnab240\Cnab240File::padNumber($this->assignor->edi7, 6) . '.'
                      . date('Ymd') . '.'
                      . cnab240\Cnab240File::padNumber($file_id, 5) . '.'
                      . cnab240\Cnab240File::padNumber($this->assignor->covenant, 5, true) // @todo verify if covenant is actually small and the Headers exagerates the covenant lenght
                      . '.REM';
            
            //$file = fopen($filename, 'w');
            //fwrite($file, $shipping_file->output());
            //fclose($file);
            
            // update status
            $id = 0;
            $err = [];
            $stmt = $this->database_cnab240->connect->prepare("UPDATE `transactions` SET `status` = 1 WHERE `id` = ?");
            $stmt->bind_param('i', $id);
            foreach ($transactions as $id) {
                //$stmt->execute();
                if ($stmt->error !== '') {
                    $err[] = $stmt->error;
                }
            }
            if (!empty($err)) {
                die(implode("<br />\n", $err));
            }
            
            // DONE
            echo '<h2>' . $filename . "</h2>\n";
            //echo '<pre>', var_dump(explode("\n", $shipping_file->output())), "</pre>\n\n\n";
            echo '<pre>' . $shipping_file->output() . "</pre>\n\n\n";
            //echo '<pre>' . file_get_contents($filename) . "</pre>\n\n\n";
            
            break;
        }
    }
}
