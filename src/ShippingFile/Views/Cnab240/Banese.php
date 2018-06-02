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
     * List of masks to apply in a Title registry based on its movement
     *
     * NOTE:
     * - I am not sure about all these segments being omitted. Maybe Q should
     *   have something
     * - Some movements are not implemented
     * - '13' should have the interest_type value set to 3
     * - '15' should have the fine_type value set to 0
     *
     * @var string[]
     */
    const MOVEMENT_MASK = [
        '02' => [
            'P' => '************** *******************************************                           ***************                                                                                                                                            ',
            'Q' => '',
            'R' => '',
        ],
        '04' => [
            'P' => '************** *******************************************                           ***************                                                                                ***************                                             ',
            'Q' => '',
            'R' => '',
        ],
        '05' => [
            'P' => '************** *******************************************                           ***************                                                                                000000000000000                                             ',
            'Q' => '',
            'R' => '',
        ],
        '06' => [
            'P' => '************** *******************************************                   ***********************                                                                                                                                            ',
            'Q' => '',
            'R' => '',
        ],
        '07' => [
            'P' => '************** *******************************************                           ***************                                         ************************                                                                           ',
            'Q' => '',
            'R' => '************** **************************************************                                                                                                                                                                               ',
        ],
        '08' => [
            'P' => '************** *******************************************                           ***************                                         ************************                                                                           ',
            'Q' => '',
            'R' => '************** **************************************************                                                                                                                                                                               ',
        ],
        '12' => [
            'P' => '************** *******************************************                           ***************                 ************************                                                                                                   ',
            'Q' => '',
            'R' => '',
        ],
        '13' => [
            'P' => '************** *******************************************                           ***************                 300000000000000000000000                                                                                                   ',
            'Q' => '',
            'R' => '',
        ],
        '14' => [
            'P' => '',
            'Q' => '',
            'R' => '************** **                                                ************************                                                                                                                                                       ',
        ],
        '15' => [
            'P' => '',
            'Q' => '',
            'R' => '************** **                                                000000000000000000000000                                                                                                                                                       ',
        ],
        '16' => [
            'P' => '************** *******************************************                           ***************                                          ***********************                                                                           ',
            'Q' => '',
            'R' => '************** ** *********************** ***********************                                                                                                                                                                               ',
        ],
        '18' => [
            'P' => '************** *******************************************                           ***************                                                                                ***************                                             ',
            'Q' => '',
            'R' => '',
        ],
        '21' => [
            'P' => '************** *******************************************    ***************        ***************                                                                                                                                            ',
            'Q' => '',
            'R' => '',
        ],
        '31' => [
            'P' => '************** *******************************************                   ***********************                                                                                                                                            ',
            'Q' => '',
            'R' => '',
        ],
        '42' => [
            'P' => '************** *******************************************                           ***************      **                                                                                                                                    ',
            'Q' => '',
            'R' => '',
        ],
    ];

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
        $movement = $title->movement->code;

        $movement_mask = static::MOVEMENT_MASK[$movement] ?? null;

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
            $movement,
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

        $result[] = static::mask(
            vsprintf($format, static::normalize($data)),
            $movement_mask['P']
        );

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
            $movement,
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

        $result[] = static::mask(
            vsprintf($format, static::normalize($data)),
            $movement_mask['Q']
        );

        /*
         * 'R' Segment
         */

        if ($title->discount2_type || $title->discount3_type
            || $title->fine_type
            || in_array($movement, ['07', '08', '14', '15', '16'])
        ) {
            $format = '%03.3s%04.4s%01.1s%05.5s%-1.1s%-1.1s%02.2s%01.1s%08.8s'
                . '%015.15s%01.1s%08.8s%015.15s%01.1s%08.8s%015.15s%-10.10s'
                . '%-40.40s%-40.40s%-20.20s%08.8s%03.3s%05.5s%-1.1s%012.12s'
                . '%-1.1s%-1.1s%01.1s%-9.9s';

            $data = [
                $bank->code,
                $this->lot_count,
                '3',
                $this->current_lot,
                'R',
                '',
                $movement,
                $title->discount2_type,
                static::date('dmY', $title->discount2_date),
                $currency->format($title->discount2_value, 'nomask'),
                $title->discount3_type,
                static::date('dmY', $title->discount3_date),
                $currency->format($title->discount3_value, 'nomask'),
                $title->fine_type,
                static::date('dmY', $title->fine_date),
                $currency->format($title->fine_value, 'nomask'),
                '', // information to client
                '', // message 3
                '', // message 4
                '',
                '0', // client occurence_code
                '0', // debit bank_code
                '0', // debit agency
                '0', // debit agency_cd
                '0', // debit account
                '',  // debit account_cd
                '',  // debit agency_account_cd
                '0', // debit emission_identification
                '',
            ];

            $result[] = static::mask(
                vsprintf($format, static::normalize($data)),
                $movement_mask['R']
            );
        }

        return array_filter($result, 'strlen');
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
