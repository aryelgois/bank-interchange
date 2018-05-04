<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ShippingFile\Views\Cnab240;

use aryelgois\BankInterchange;
use aryelgois\BankInterchange\Utils;
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
        $shipping_file = $this->shipping_file;
        $assignment = $shipping_file->assignment;
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
            $assignor_person->getDocumentType(),
            $assignor_person->document,
            $assignment->covenant,
            '0', // $assignment->agency,
            '',
            '0', // $assignment->account,
            '',
            '',
            Utils::cleanSpaces($assignor_person->name),
            $bank->name,
            '',
            '1',
            static::date('dmY', $shipping_file->stamp),
            static::date('His', $shipping_file->stamp),
            $shipping_file->counter,
            static::VERSION_FILE_LAYOUT,
            '0',
            '',
            $shipping_file->notes,
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
            $assignor_person->getDocumentType(),
            $assignor_person->document,
            $assignment->covenant,
            '0', // $assignment->agency,
            '',
            '0', // $assignment->account,
            '',
            '',
            Utils::cleanSpaces($assignor_person->name),
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
     * @param Models\Title $title Contains data for the registry
     *
     * @return string[]
     */
    protected function generateLotDetail(Models\Title $title)
    {
        $result = [];

        $assignment = $title->assignment;
        $assignor_person = $assignment->assignor->person;
        $bank = $assignment->bank;
        $client = $title->client;
        $client_address = $client->address;
        $client_person = $client->person;
        $currency = $title->currency;
        $currency_code = $title->getCurrencyCode();
        $guarantor_person = $title->guarantor->person ?? null;

        /*
         * 'P' Segment
         */

        $format = '%03.3s%04.4s%01.1s%05.5s%-1.1s%-1.1s%02.2s%05.5s%-1.1s'
            . '%012.12s%-1.1s%-1.1s%020.20s%01.1s%01.1s%-1.1s%01.1s%-1.1s'
            . '%-15.15s%08.8s%015.15s%05.5s%-1.1s%02.2s%-1.1s%08.8s%01.1s'
            . '%08.8s%015.15s%01.1s%08.8s%015.15s%015.15s%015.15s%-25.25s'
            . '%01.1s%02.2s%01.1s%03.3s%02.2s%010.10s%-1.1s';

        $protest_code = $title->protest_code ?? 3;
        $protest_days = ($protest_code != 3 ? $title->protest_days ?? 0 : 0);

        $data = [
            $bank->code,
            $this->lot_count,
            '3',
            $this->current_lot,
            'P',
            '',
            $title->movement->code,
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
            $title->doc_number,
            static::date('dmY', $title->due),
            $currency->format($title->getActualValue(), 'nomask'),
            '0', // Charging agency
            '',  // Charging agency Check Digit
            $title->kind->code,
            $title->accept,
            static::date('dmY', $title->emission),
            $title->interest_type,
            static::date('dmY', $title->interest_date),
            $currency->format($title->interest_value, 'nomask'),
            $title->discount1_type,
            static::date('dmY', $title->discount1_date),
            $currency->format($title->discount1_value, 'nomask'),
            $currency->format($title->ioc_iof, 'nomask'),
            $currency->format($title->rebate, 'nomask'),
            $title->description,
            $protest_code,
            min($protest_days, 99),
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

        $client_address_piece = implode(' ', [
            static::filter($client_address->place),
            $client_address->number,
            static::filter($client_address->detail)
        ]);

        $guarantor_document_type = ($guarantor_person !== null)
            ? $guarantor_person->getDocumentType()
            : '';

        $data = [
            $bank->code,
            $this->lot_count,
            '3',
            $this->current_lot,
            'Q',
            '',
            $title->movement->code,
            $client_person->getDocumentType(),
            $client_person->document,
            Utils::cleanSpaces($client_person->name),
            Utils::cleanSpaces($client_address_piece),
            static::filter($client_address->neighborhood),
            $client_address->zipcode,
            $client_address->county->name,
            $client_address->county->state->code,
            $guarantor_document_type,
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
            $this->current_lot,
            '0', // CS
            '0', // CS
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
