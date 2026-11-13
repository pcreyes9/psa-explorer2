<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CMERegistrationMemberFileReader
{

    public function read(string $filePath): array
    {
        try {

            if (! is_file($filePath)) {
                throw new \RuntimeException(
                    'The uploaded file could not be found.'
                );
            }

            $spreadsheet = IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray(
                null,
                true,
                true,
                false
            );

            if (empty($rows)) {
                throw new \RuntimeException(
                    'The uploaded file is empty.'
                );
            }

            $headers = array_map(
                fn ($value) => strtolower(
                    preg_replace(
                        '/^\xEF\xBB\xBF/',
                        '',
                        trim((string) $value)
                    ) ?? ''
                ),
                $rows[0]
            );

            $memberColumn = null;
            $paymentReferenceColumn = null;

            foreach ($headers as $index => $header) {

                if (in_array($header, [
                    'psa id',
                    'psa_id',
                    'member_id_no',
                ], true)) {
                    $memberColumn = $index;
                }

                if (in_array($header, [
                    'payment reference number',
                    'payment_ref_no',
                    'payment ref no',
                    'payment ref no.',
                    'payment ref. no.',
                ], true)) {
                    $paymentReferenceColumn = $index;
                }
            }

            if ($memberColumn === null) {
                throw new \RuntimeException(
                    'The uploaded file must contain a PSA ID column.'
                );
            }

            if ($paymentReferenceColumn === null) {
                throw new \RuntimeException(
                    'The uploaded file must contain a Payment Reference Number column.'
                );
            }

            $memberIds = [];
            $paymentReferences = [];

            foreach (array_slice($rows, 1) as $rowIndex => $row) {

                $memberId = trim(
                    (string) ($row[$memberColumn] ?? '')
                );

                if ($memberId === '') {
                    continue;
                }

                $paymentReference = trim(
                    (string) ($row[$paymentReferenceColumn] ?? '')
                );

                if ($paymentReference === '') {
                    throw new \RuntimeException(
                        'Payment reference number is required on spreadsheet row '
                        . ($rowIndex + 2)
                        . '.'
                    );
                }

                if (
                    isset($paymentReferences[$memberId])
                    && $paymentReferences[$memberId] !== $paymentReference
                ) {
                    throw new \RuntimeException(
                        "PSA ID {$memberId} has conflicting payment reference numbers in the uploaded file."
                    );
                }

                $memberIds[] = $memberId;
                $paymentReferences[$memberId] = $paymentReference;
            }

            if (empty($memberIds)) {
                throw new \RuntimeException(
                    'No PSA IDs were found in the uploaded file.'
                );
            }

            return [
                'member_ids' => $memberIds,
                'payment_references' => $paymentReferences,
            ];
        } catch (ValidationException $exception) {

            throw $exception;

        } catch (\Throwable $exception) {

            throw ValidationException::withMessages([
                'member_file' => $exception->getMessage(),
            ]);
        }
    }
}
