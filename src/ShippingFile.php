<?php
/**
 * This Software is part of aryelgois\cnab240 and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\cnab240;

use aryelgois\utils;
use aryelgois\objects;

/**
 * Generates Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/cnab240
 * @version 0.2
 */
class ShippingFile extends namespace\Cnab240File
{
    /**
     * Creates a new Shipping File object
     *
     * @param Bank $bank ..
     * @param string[] $assignor As follows.
     *     [
     *         'document'   => string (14) Brazilian CNPJ|CPF
     *         'covenant'   => string (20) Informed by Bank
     *         'agency'     => string (5)  Bank's agency
     *         'agency_cd'  => string (1)  Bank's agency check digit
     *         'account'    => string (12) Bank's account
     *         'account_cd' => string (1)  Bank's account check digit
     *         'name'       => string (30) Name
     *     ]
     * @param integer  $header_sequence    Max 6 digits. Increase for each File Header
     * @param integer  $shippping_sequence Max 8 digits. Increase for each Shipping File
     *
     * @param integer  $sequence Max 8 digits. Increase for each Shipping File
     * @param Bank     $bank     Contains Bank's information
     * @param Assignor $assignor Contains Assignor's information
     */
    public function __construct(
        //integer $sequence,
        namespace\objects\Bank $bank,
        namespace\objects\Assignor $assignor
    ) {
        $this->bank = $bank;
        $this->assignor = $assignor;
        //$this->addHeaders();
    }
    
    /**
     * Adds a File Header and a Lot Header
     *
     * @throws LogicException If file is not empty
     */
    public function addHeaders()
    {
        if (!empty($this->file)) {
            throw new \LogicException('File is not empty');
        }
        $this->registerFileHeader();
        $this->registerLotHeader();
    }
    
    /**
     * Adds Lot Details
     *
     * @param mixed[] $data Data to be added
     */
    public function addLot($data)
    {
        $this->registerLotDetail('P', $data);
        $this->registerLotDetail('Q', $data);
    }
    
    /**
     * Adds a Lot Trailer and a File Trailer
     *
     * @throws LogicException If file is empty
     */
    public function addTrailers()
    {
        if (empty($this->file)) {
            throw new \LogicException('File is empty');
        }
        $this->registerLotTrailer();
        $this->registerFileTrailer();
    }
    
    /**
     * Adds a File Header
     *
     * @param integer  $operation 1|2. Means Shipping|Return File
     * @param integer  $sequence  File sequence (Max 6 digits)
     */
    protected function registerFileHeader(integer $operation, integer $sequence)
    {
        $this->file[] = self::fieldControl(0)
                      . str_repeat(' ', 9)
                      . $this->assignor->getAll()
                      . $this->bank->name
                      . str_repeat(' ', 10)
                      . $operation . date('dmYHis') . self::padNumber($sequence, 6) . self::VERSION_FILE_LAYOUT . '00000'
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 29);
        $this->lot++;
    }
    
    /**
     * Adds a Lot Header
     *
     * @param string  $operation 'R'|'T'. Means Shipping|Return File
     * @param integer $id        ...
     */
    protected function registerLotHeader(string $operation/*, integer $id,*/)
    {
        $this->file[] = self::fieldControl(1)
                      . $operation . '01' . '  ' . self::VERSION_LOT_LAYOUT
                      . ' '
                      . $this->assignor->getAll()
                      . str_repeat(' ', 40) // message 1
                      . str_repeat(' ', 40) // message 2
                      . '00000000'          // $id => database File sequence
                      . '00000000'          // date('dmY')
                      . '00000000'          // Credit date
                      . str_repeat(' ', 33);
        $this->lot++;
    }
    
    /**
     * Adds a Lot Detail
     *
     * @param type $name ...
     *
     * @return true|false On failure
     *
     * @throws OverflowException        If the registry limit was overflowed
     * @throws UnexpectedValueException If segment is wrong
     */
    protected function registerLotDetail($segment, $data)
    {
        // Check if can register
        if ($this->closed) {
            return false;
        }
        $count = count($this->file);
        if ($count > 9999) {
            throw new \OverflowException('Registry overflow');
        } elseif ($count > 9997) {
            return false;
        }
        
        // general data
        $control = self::fieldControl(3);
        $service = $this->lot // this detail (multiple segments to the same detail)
                 . $segment
                 . ' '
                 . $data['movement_code'];
        
        // add by segment
        switch ($segment) {
            case 'P':
                $this->file[] = $control
                              
                              . $service
                              
                              . $this->assignor->getAgencyAccount()
                              
                              . $data['onum']
                              
                              . $data['bill']['wallet'] // 1
                              . 1 // Title's Registration
                              . $data['bill']['doc_type'] // 1|2|3
                              . 2 // emission identifier
                              . 2 // distribuition identifier
                              
                              . $data['bill']['doc_number']
                              . $data['bill']['date_due']
                              . $data['bill']['value'] // 13 digits, 2 are floating point
                              . '00000'
                              . ' '
                              . $data['bill']['charging_mode'] // 2 digits
                              . $data['bill']['accept']
                              . date('dmY') // title_emission_date
                              
                              . $data['bill']['fine_mode'] // 1|2|3
                              . $data['bill']['fine_date'] // 'dmY'
                              . $data['bill']['fine_value']
                              
                              . $data['bill']['discount_mode'] // 1|2|3
                              . $data['bill']['discount_date'] // 'dmY'
                              . $data['bill']['discount_value']
                              
                              . '0000000000000' // IOF
                              . $data['bill']['value_rebate']
                              . $data['bill']['identifier'] // 25 characters
                              . 3
                              . '00'
                              . 1
                              . $data['bill']['date_low_return'] // 3 digits
                              . '09' // specie
                              . '0000000000'
                              . 1;
                break;
            case 'Q':
                $this->file[] = $control
                              
                              . $service
                              
                              . $data['payer']['document_type'] . $data['payer']['document']
                              . $data['payer']['name'] // 40 characters
                              . $data['payer']['address'] // 40 characters
                              . $data['payer']['neighborhood'] // 15 characters
                              . $data['payer']['cep'] // 5 digits
                              . $data['payer']['cep_suffix'] // 3 digits
                              . $data['payer']['county'] // 15 characters
                              . $data['payer']['state'] // 2 characters
                              
                              . $data['guarantor']['document_type'] . $data['guarantor']['document']
                              . $data['guarantor']['name'] // 40 characters
                              
                              . '000'
                              . '00000000000000000000'
                              . '        ';
                break;
            case 'R':
                
                break;
            case 'Y':
                
                break;
            default:
                throw new \UnexpectedValueException('Wrong segment');
        }
        
        $this->lot++;
        return true;
    }
    
    /**
     * Adds a Lot Trailer
     */
    protected function registerLotTrailer()
    {
        $this->file[] = self::fieldControl(5)
                      . '         '
                      . self::padNumber($this->len, 6)
                      . $data['titles']['amount']    // 6 digits
                      . $data['titles']['value_sum'] // 15 digits, 2 are floating point
                      . '000000' . '000000000000000'
                      . '000000' . '000000000000000'
                      . '000000' . '000000000000000'
                      . '        '
                      . str_repeat(' ', 117);
        $this->lot++;
    }
    
    /**
     * Adds a File Trailer
     */
    protected function registerFileTrailer()
    {
        $this->file[] = self::fieldControl(9)
                      . '         '
                      . '000001' // count type 1 // 6 digits
                      . self::padNumber($this->len + 1, 6) // count file registries // 6 digits
                      . '000000'
                      . str_repeat(' ', 205);
        $this->closed = true;
    }
}
