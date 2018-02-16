<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile\Views\Cnab240;

use aryelgois\BankInterchange;
use aryelgois\BankInterchange\Models;

/**
 * Generates CNAB240 shipping files for Banese
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Banese extends BankInterchange\ShippingFile\Views\Cnab240
{
    /**
     * Generates FileHeader registry
     *
     * @return string
     */
    protected function generateFileHeader()
    {
        $assignment = $this->shipping_file->assignment;
        $assignor_person = $assignment->assignor->person;
        $bank = $assignment->bank;

        $format = '%03.3s%04.4s%01.1s%-9.9s%01.1s%014.14s%020.20s%05.5s%-1.1s'
            . '%012.12s%-1.1s%-1.1s%-30.30s%-30.30s%-10.10s%01.1s%08.8s%06.6s'
            . '%06.6s%03.3s%05.5s%-20.20s%-20.20s%-29.29s';

        $data = [
            $bank->code,
            '0',
            '0',
            '',
            $assignor_person->documentValidate()['type'],
            $assignor_person->document,
            $assignment->covenant,
            '0', // $assignment->agency,
            '',
            '0', // $assignment->account,
            '',
            '',
            $assignor_person->name,
            $bank->name,
            '',
            '1',
            date('dmY', strtotime($this->shipping_file->stamp)),
            date('His', strtotime($this->shipping_file->stamp)),
            $this->shipping_file->counter,
            static::VERSION_FILE_LAYOUT,
            '0',
            '',
            '',
            '',
        ];

        return vsprintf($format, static::normalize($data));
    }

    /**
     * Generates LotHeader registry
     *
     * @return string
     */
    protected function generateLotHeader()
    {
        $assignment = $this->shipping_file->assignment;
        $assignor_person = $assignment->assignor->person;
        $bank = $assignment->bank;

        $format = '%03.3s%04.4s%01.1s%-1.1s%02.2s%-2.2s%03.3s%-1.1s%01.1s'
            . '%015.15s%020.20s%05.5s%-1.1s%012.12s%-1.1s%-1.1s%-30.30s%-40.40s'
            . '%-40.40s%08.8s%08.8s%08.8s%-33.33s';

        $data = [
            $bank->code,
            $this->lot_count,
            '1',
            'R',
            '1',
            '',
            static::VERSION_LOT_LAYOUT,
            '',
            $assignor_person->documentValidate()['type'],
            $assignor_person->document,
            $assignment->covenant,
            '0', // $assignment->agency,
            '',
            '0', // $assignment->account,
            '',
            '',
            $assignor_person->name,
            '',  // message 1
            '',  // message 2
            '0', // number shipping/return
            '0', // recording date
            '0', // credit date
            '',
        ];

        return vsprintf($format, static::normalize($data));
    }

    /**
     * Generates LotDetail registry
     *
     * @param Models\ShippingFileTitle $sft Contains data for the registry
     *
     * @return string[]
     */
    protected function generateLotDetail(Models\ShippingFileTitle $sft)
    {
        $result = [];

        $title = $sft->title;
        $assignment = $title->assignment;
        $assignor_person = $assignment->assignor->person;
        $bank = $assignment->bank;
        $client = $title->client;
        $client_address = $client->address;
        $client_person = $client->person;
        $currency_code = Models\CurrencyCode::getInstance([
            'currency' => $title->currency->id,
            'bank' => $bank->id
        ]);
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
            $this->lot_count,
            '3',
            $this->current_lot['registry_count'],
            'P',
            '',
            $sft->movement->code,
            '0', // $assignment->agency,
            '',
            '0', // $assignment->account,
            '',
            '',
            $title->our_number,
            $assignment->wallet->code,
            '1', // Title's Registration
            '1', // Document type
            '2', // Emission identifier
            '2', // Distribuition identifier
            $title->id,
            date('dmY', strtotime($title->due)),
            number_format($title->value, 2, '', ''),
            '0', // Charging agency
            '',  // Charging agency Check Digit
            $title->kind->code,
            $title->accept,
            date('dmY', strtotime($title->stamp)),
            $title->interest_type,
            ($title->interest_date != '' ? date('dmY', strtotime($title->interest_date)) : '0'),
            number_format($title->interest_value, 2, '', ''),
            $title->discount1_type,
            ($title->discount1_date != '' ? date('dmY', strtotime($title->discount1_date)) : '0'),
            number_format($title->discount1_value, 2, '', ''),
            '0', // number_format($title->ioc_iof, 2, '', ''),
            number_format($title->rebate, 2, '', ''),
            $title->description,
            '3', // Protest code
            '0', // Protest due
            '1', // down/return code
            '0', // down/return due
            $currency_code->cnab240,
            '0', // Contract number
            '1', // Free use: it's defining partial payment isn't allowed
        ];

        $result[] = vsprintf($format, static::normalize($data));

        /*
         * 'Q' Segment
         */

        $format = '%03.3s%04.4s%01.1s%05.5s%-1.1s%-1.1s%02.2s%01.1s%015.15s'
            . '%-40.40s%-40.40s%-15.15s%08.8s%-15.15s%-2.2s%01.1s%015.15s'
            . '%-40.40s%03.3s%020.20s%-8.8s';

        $data = [
            $bank->code,
            $this->lot_count,
            '3',
            $this->current_lot['registry_count'],
            'Q',
            '',
            $sft->movement->code,
            $client_person->documentValidate()['type'],
            $client_person->document,
            $client_person->name,
            static::filter($client_address->place),
            $client_address->neighborhood,
            $client_address->zipcode,
            $client_address->county->name,
            $client_address->county->state->code,
            ($guarantor_person !== null ? $guarantor_person->documentValidate()['type'] : ''),
            $guarantor_person->document ?? '',
            $guarantor_person->name ?? '',
            '0', // Corresponding bank
            '0', // "Our number" at corresponding bank
            '',
        ];

        $result[] = vsprintf($format, static::normalize($data));

        return $result;
    }

    /**
     * Generates LotTrailer registry
     *
     * @return string
     */
    protected function generateLotTrailer()
    {
        $format = '%03.3s%04.4s%01.1s%-9.9s%06.6s%06.6s%017.17s%06.6s%017.17s'
            . '%06.6s%017.17s%06.6s%017.17s%-8.8s%-117.117s';

        $data = [
            $this->shipping_file->assignment->bank->code,
            $this->lot_count,
            '5',
            '',
            $this->current_lot['registry_count'],
            $this->current_lot['title_count'],
            number_format($this->current_lot['title_total'], 2, '', ''),
            '0', // CV
            '0', // CV
            '0', // CC
            '0', // CC
            '0', // CD
            '0', // CD
            '',
            '',
        ];

        return vsprintf($format, static::normalize($data));
    }

    /**
     * Generates FileTrailer registry
     *
     * @return string
     */
    protected function generateFileTrailer()
    {
        $format = '%03.3s%04.4s%01.1s%-9.9s%06.6s%06.6s%06.6s%-205.205s';

        $data = [
            $this->shipping_file->assignment->bank->code,
            '9999',
            '9',
            '',
            $this->lot_count,
            $this->registry_count,
            '0',
            '',
        ];

        return vsprintf($format, static::normalize($data));
    }
}
