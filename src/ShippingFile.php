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
 * @version 0.2.1
 */
class ShippingFile extends namespace\Cnab240File
{
    /**
     * Total registries in the file
     *
     * integer
     */
    protected $registries = 0;
    
    /**
     * Current Lot
     *
     * @var integer
     */
    protected $lot = 0;
    
    /**
     * Contais lot's data
     *
     * array[]
     */
    protected $lots;
    
    /**
     * Creates a new Shipping File object
     *
     * @param Bank $bank ..
     * @param Assignor $assignor ..
     * @param integer $file_id Sequential file number, max 6 digits
     *
     * @param integer  $header_sequence    Max 6 digits. Increase for each File Header
     * @param integer  $shippping_sequence Max 8 digits. Increase for each Shipping File
     *
     * @param integer  $sequence Max 8 digits. Increase for each Shipping File
     * @param Bank     $bank     Contains Bank's information
     * @param Assignor $assignor Contains Assignor's information
     */
    public function __construct(
        namespace\objects\Bank $bank,
        namespace\objects\Assignor $assignor,
        $file_id
    ) {
        $this->bank = $bank;
        $this->assignor = $assignor;
        $this->file_id = $file_id;
        
        $this->open();
    }
    
    /**
     * Adds a File Header
     */
    protected function open()
    {
        $this->registerFileHeader();
        $this->addLot();
    }
    
    /**
     * Starts a new Lot
     *
     * @param mixed[] $data Data to be added
     *
     * @throws new OverflowException If the file has too many Lots
     */
    public function addLot()
    {
        if ($this->lot >= 9998) {
            throw new \OverflowException('The File got too many Lots');
        } elseif ($this->lot > 0) {
            $this->closeLot();
        }
        $this->lots[++$this->lot] = [
            'registries' => 0,
            'titles' => 0,
            'total' => 0.0,
            'closed' => false
        ];
        $this->registerLotHeader();
    }
    
    public function addEntry(
        namespace\objects\Payer $payer,
        namespace\objects\Service $service
    ) {
        
        $this->registerLotDetail('P', $data);
        $this->registerLotDetail('Q', $data);
    }
    
    /**
     * Adds a Lot Trailer if the current lot is open
     */
    protected function closeLot()
    {
        if (!$this->closed || !$this->lots[$this->lot]['closed']) {
            $this->registerLotTrailer();
            $this->lots[$this->lot]['closed'] = true;
        }
    }
    
    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        if (!$this->closed) {
            $this->closeLot();
            $this->lot = 9999;
            $this->registerFileTrailer();
            $this->closed = true;
        }
    }
    
    /**
     * Outputs the contents in a multiline string
     *
     * NOTES:
     * - Closes the current Lot and the File
     *
     * @return string A long, long string. Each line with 240 bytes.
     */
    final public function output()
    {
        $this->close();
        return implode("\n", $this->file);
    }
    
    
    /*
     * Formatting
     * =========================================================================
     */
    
    
    /**
     * [desc]
     *
     * @param integer $len Document length to be padded
     *
     * @return string
     */
    public function assignorDocument($len)
    {
        $a = $this->assignor;
        return $a->document['type'] . self::padNumber($a->document['number'], $len);
    }
    
    /**
     * [desc]
     *
     * @return string
     */
    public function assignorAgencyAccount()
    {
        $a = $this->assignor;
        $result = self::padNumber($a->agency['number'], 5) . $a->agency['cd']
                . self::padNumber($a->account['number'], 12) . $a->account['cd'];
        $result .= self::assignorAgencyAccountCheck($result);
        return $result;
    }
    
    /**
     * [desc]
     *
     * @param string $agency_account ..
     *
     * @return string
     */
    protected static function assignorAgencyAccountCheck($agency_account)
    {
        $cd = utils\Validation::mod10($agency_account);
        
        return $cd;
    }
    
    
    /*
     * Internals
     * =========================================================================
     */
    
    
    /**
     * Adds a File Header
     */
    protected function registerFileHeader()
    {
        $this->file[] = self::fieldControl(0)
                      . str_repeat(' ', 9)
                      . $this->assignorDocument(14)
                      . self::padNumber($this->assignor->covenant, 20)
                      . $this->assignorAgencyAccount()
                      . self::padAlfa($this->assignor->name, 30)
                      . self::padAlfa($this->bank->name, 30)
                      . str_repeat(' ', 10)
                      . '1' . date('dmYHis') . self::padNumber($this->file_id, 6) . self::VERSION_FILE_LAYOUT . '00000'
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 20)
                      . str_repeat(' ', 29);
        $this->registries++;
    }
    
    /**
     * Adds a Lot Header
     */
    protected function registerLotHeader()
    {
        $this->file[] = self::fieldControl(1)
                      . 'R' . '01' . '  ' . self::VERSION_LOT_LAYOUT
                      . ' '
                      . $this->assignorDocument(15)
                      . self::padNumber($this->assignor->covenant, 20)
                      . $this->assignorAgencyAccount()
                      . self::padAlfa($this->assignor->name, 30)
                      . str_repeat(' ', 40) // message 1
                      . str_repeat(' ', 40) // message 2
                      . '00000000'          // number shipping/return
                      . '00000000'          // recording date
                      . '00000000'          // credit date
                      . str_repeat(' ', 33);
        $this->incrementLotRegistry();
        $this->registries++;
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
                              . $data['bill']['value'] // 15 digits and 2 floating point
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
        
        $this->incrementLotRegistry();
        $this->registries++;
        return true;
    }
    
    /**
     * Adds a Lot Trailer
     */
    protected function registerLotTrailer()
    {
        $this->file[] = self::fieldControl(5)
                      . '         '
                      . self::padNumber($this->incrementLotRegistry(), 6)
                      . self::padNumber($this->lots[$this->lot]['titles'], 6)
                      . self::padNumber(number_format($this->lots[$this->lot]['total'], 2, '', ''), 17)
                      . '000000' . '00000000000000000'
                      . '000000' . '00000000000000000'
                      . '000000' . '00000000000000000'
                      . '        '
                      . str_repeat(' ', 117);
        $this->registries++;
    }
    
    /**
     * Adds a File Trailer
     */
    protected function registerFileTrailer()
    {
        $this->file[] = self::fieldControl(9)
                      . '         '
                      . self::padNumber(count($this->lots), 6)
                      . self::padNumber(++$this->registries, 6)
                      . '000000'
                      . str_repeat(' ', 205);
    }
    
    /**
     * Adds a File Trailer
     *
     * @throws OverflowException If there are too many lot registries
     */
    protected function incrementLotRegistry()
    {
        $count = ++$this->lots[$this->lot]['registries'];
        if ($count > 999999) {
            throw new \OverflowException('Too many lot registries');
        }
        return $count;
    }
}
