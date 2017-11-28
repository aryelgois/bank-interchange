<?php
/**
 * This Software is part of aryelgois\BankInterchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\Views\Cnabs;

use aryelgois\Utils;
use aryelgois\BankInterchange as BankI;

/**
 * Generates CNAB240 Shipping Files to be sent to banks
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Cnab240 extends BankI\Views\Cnab
{
    /**
     * FEBRABAN's version of file layout
     *
     * @const string
     */
    const VERSION_FILE_LAYOUT = '101';

    /**
     * FEBRABAN's version of lot layout
     *
     * @const string
     */
    const VERSION_LOT_LAYOUT = '060';

    /**
     * Current Lot
     *
     * @var integer
     */
    protected $lot = 0;

    /**
     * Contais lot's data
     *
     * @var array[]
     */
    protected $lots;

    /**
     * Adds a File Header and a Lot
     */
    protected function open()
    {
        parent::open();
        $this->openLot();
    }

    /**
     * Starts a new Lot
     *
     * @throws \OverflowException If the file has too many Lots
     */
    protected function openLot()
    {
        if ($this->lot >= 9998) {
            throw new \OverflowException('The File got too many Lots');
        } elseif ($this->lot > 0) {
            $this->closeLot();
        }

        $this->lots[++$this->lot] = [
            'registries' => 1, // Already counting the
            'lines' => 1,      // following LotHeader
            'titles' => 0,
            'total' => 0.0,
            'closed' => false
        ];

        $this->addLotHeader();
        $this->increment(999997); // I think
    }

    /**
     * Adds a new Title entry
     *
     * @param Title   $title    What the entry is about
     *
     * @return boolean For success or failure
     *
     * @throws \OverflowException If there are too many lot registries
     */
    protected function addTitle(BankI\Models\Title $title)
    {
        // Check if the file or the current log is closed
        if ($this->lots[$this->lot]['closed']) {
            return false;
        }

        // Check if the Lot is full
        /**
         * @todo change those big numbers to be 999999 - the amount of lines
         *       to be added
         */
        $count = $this->lots[$this->lot]['registries'];
        if ($count > 999998) {
            throw new \OverflowException('The Lot got too many registries');
        } elseif ($count == 999998) {
            $this->openLot();
        }

        $this->addLotDetail($title);

        $this->lots[$this->lot]['registries']++;
        $this->lots[$this->lot]['lines'] += 2;
        $this->lots[$this->lot]['titles']++;
        $this->lots[$this->lot]['total'] += $title->value;
        $this->registry_count += 2; // the amount of segments (type 3)

        return true;
    }

    /**
     * Adds a Lot Trailer if the current lot is open
     */
    protected function closeLot()
    {
        if (!$this->lots[$this->lot]['closed']) {
            $this->lots[$this->lot]['registries']++;
            $this->lots[$this->lot]['lines']++;
            $this->addLotTrailer();
            $this->registry_count++;
            $this->lots[$this->lot]['closed'] = true;
        }
    }

    /**
     * Adds a File Trailer
     */
    protected function close()
    {
        $this->closeLot();
        $this->lot = 9999;
        $this->registry_count++;
        $this->addFileTrailer();
    }

    /*
     * Internals
     * =========================================================================
     */

    /**
     * Adds a File Header
     */
    protected function addFileHeader()
    {
        $assignor = $this->shipping_file->assignor;
        $assignor_person = $assignor->person;
        $bank = $assignor->bank;

        $format = '%03.3s%04.4s%01.1s%-9.9s%01.1s%014.14s%020.20s%05.5s%-1.1s'
                . '%012.12s%-1.1s%-1.1s%-30.30s%-30.30s%-10.10s%01.1s%08.8s'
                . '%06.6s%06.6s%03.3s%05.5s%-20.20s%-20.20s%-29.29s';

        $data = [
            $bank->code,
            $this->lot,
            '0',
            '',
            $assignor_person->documentValidate()['type'],
            $assignor_person->document,
            $assignor->covenant,
            $assignor->agency,
            '',
            $assignor->account,
            $assignor->account_cd,
            '',
            $assignor_person->name,
            $bank->name,
            '',
            '1',
            date('dmY'),
            date('His'),
            $this->shipping_file->counter,
            self::VERSION_FILE_LAYOUT,
            '00000',
            '',
            '',
            '',
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Lot Header
     */
    protected function addLotHeader()
    {
        $assignor = $this->shipping_file->assignor;
        $assignor_person = $assignor->person;
        $bank = $assignor->bank;

        $format = '%03.3s%04.4s%01.1s%-1.1s%02.2s%-2.2s%03.3s%-1.1s%01.1s'
                . '%015.15s%020.20s%05.5s%-1.1s%012.12s%-1.1s%-1.1s%-30.30s'
                . '%-40.40s%-40.40s%08.8s%08.8s%08.8s%-33.33s';

        $data = [
            $bank->code,
            $this->lot,
            '1',
            'R',
            '1',
            '',
            self::VERSION_LOT_LAYOUT,
            '',
            $assignor_person->documentValidate()['type'],
            $assignor_person->document,
            $assignor->covenant,
            $assignor->agency,
            '',
            $assignor->account,
            $assignor->account_cd,
            '',
            $assignor_person->name,
            '',  // message 1
            '',  // message 2
            '0', // number shipping/return
            '0', // recording date
            '0', // credit date
            '',
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Lot Detail
     *
     * @param Title   $title    Holds data about the title and the related payer
     * @param integer $movement ...
     */
    protected function addLotDetail(BankI\Models\Title $title, $movement = 1)
    {
        $assignor = $title->assignor;
        $assignor_person = $assignor->person;
        $bank = $assignor->bank;
        $payer = $title->payer;
        $payer_person = $payer->person;
        $payer_address = $payer->address;
        $guarantor_person = $title->guarantor->person ?? null;

        /*
         * 'P' Segment
         */

        $format = '%03.3s%04.4s%01.1s%05.5s%-1.1s%-1.1s%02.2s%05.5s%-1.1s'
                . '%012.12s%-1.1s%-1.1s%020.20s%01.1s%01.1s%-1.1s%01.1s%-1.1s'
                . '%-15.15s%08.8s%015.15s%05.5s%-1.1s%02.2s%-1.1s%08.8s%01.1s'
                . '%08.8s%015.15s%01.1s%08.8s%015.15s%015.15s%015.15s%-25.25s'
                . '%01.1s%02.2s%01.1s%03.3s%02.2s%010.10s%-1.1s';

        $data = [
            $bank->code,
            $this->lot,
            '3',
            $this->lots[$this->lot]['registries'],
            'P',
            '',
            $movement,
            $assignor->agency,
            '0',
            $assignor->account,
            $assignor->account_cd,
            '',
            $title->our_number,
            $assignor->wallet->febraban,
            '1', // Title's Registration
            $title->doc_type,
            '2', // Emission identifier
            '2', // Distribuition identifier
            $title->id,
            date('dmY', strtotime($title->due)),
            number_format($title->value, 2, '', ''),
            '0', // Collection agency
            '',  // Collection agency Check Digit
            $title->kind,
            'A', // Identifies title acceptance by payer
            date('dmY', strtotime($title->stamp)),
            $title->fine_type,
            ($title->fine_date != '' ? date('dmY', strtotime($title->fine_date)) : '0'),
            number_format($title->fine_value, 2, '', ''),
            $title->discount_type,
            ($title->discount_date != '' ? date('dmY', strtotime($title->discount_date)) : '0'),
            number_format($title->discount_value, 2, '', ''),
            number_format($title->iof, 2, '', ''),
            number_format($title->rebate, 2, '', ''),
            $title->description,
            '3', // Protest code
            '0', // Protest deadline
            '1', // low/return code
            '0', // low/return deadline
            $title->specie->febraban,
            '0', // Contract number
            '1', // Free use: it's defining partial payment isn't allowed
        ];

        $this->register($format, $data);

        /*
         * 'Q' Segment
         */

        $format = '%03.3s%04.4s%01.1s%05.5s%-1.1s%-1.1s%02.2s%01.1s%015.15s'
                . '%-40.40s%-40.40s%-15.15s%08.8s%-15.15s%-2.2s%01.1s%015.15s'
                . '%-40.40s%03.3s%020.20s%-8.8s';

        $data = [
            $bank->code,
            $this->lot,
            '0',
            $this->lots[$this->lot]['registries'],
            'Q',
            '',
            $movement,
            $payer_person->documentValidate()['type'],
            $payer_person->document,
            $payer_person->name,
            $payer_address->place,
            $payer_address->neighborhood,
            $payer_address->zipcode,
            $payer_address->county->name,
            $payer_address->county->state->code,
            ($guarantor_person !== null ? $guarantor_person->documentValidate()['type'] : ''),
            $guarantor_person->document ?? '',
            $guarantor_person->name ?? '',
            '0', // Corresponding bank
            '0', // "Our number" at corresponding bank
            '',
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a Lot Trailer
     */
    protected function addLotTrailer()
    {
        $format = '%03.3s%04.4s%01.1s%-9.9s%06.6s%06.6s%017.17s%06.6s%017.17s'
                . '%06.6s%017.17s%06.6s%017.17s%-8.8s%-117.117s';

        $data = [
            $this->shipping_file->assignor->bank->code,
            $this->lot,
            '5',
            '',
            $this->lots[$this->lot]['lines'],
            $this->lots[$this->lot]['titles'],
            number_format($this->lots[$this->lot]['total'], 2, '', ''),
            '0', // CV
            '0', // CV
            '0', // CC
            '0', // CC
            '0', // CD
            '0', // CD
            '',
            '',
        ];

        $this->register($format, $data);
    }

    /**
     * Adds a File Trailer
     */
    protected function addFileTrailer()
    {
        $format = '%03.3s%04.4s%01.1s%-9.9s%06.6s%06.6s%06.6s%-205.205s';

        $data = [
            $this->shipping_file->assignor->bank->code,
            $this->lot,
            '9',
            '',
            count($this->lots),
            $this->registry_count,
            '0',
            '',
        ];

        $this->register($format, $data);
    }
}
