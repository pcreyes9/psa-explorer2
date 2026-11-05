<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CMERegistrationMemberFileReader
{
    /**
     * Read PSA IDs from an Excel or CSV file.
     *
     * Accepted column headings:
     * - PSA ID
     * - PSA_ID
     * - member_id_no
     */
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

            /*
            |--------------------------------------------------------------------------
            | Identify PSA ID Column
            |--------------------------------------------------------------------------
            */

            $headers = array_map(
                fn ($value) => strtolower(
                    trim((string) $value)
                ),
                $rows[0]
            );

            $acceptedHeaders = [
                'psa id',
                'psa_id',
                'member_id_no',
            ];

            $columnIndex = null;

            foreach ($headers as $index => $header) {

                if (in_array($header, $acceptedHeaders, true)) {
                    $columnIndex = $index;
                    break;
                }
            }

            if ($columnIndex === null) {
                throw new \RuntimeException(
                    'The uploaded file must contain a PSA ID column.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Extract PSA IDs
            |--------------------------------------------------------------------------
            */

            $memberIds = [];

            foreach (array_slice($rows, 1) as $row) {

                $value = $row[$columnIndex] ?? null;

                if ($value === null) {
                    continue;
                }

                $memberId = trim((string) $value);

                if ($memberId === '') {
                    continue;
                }

                $memberIds[] = $memberId;
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Duplicate PSA IDs
            |--------------------------------------------------------------------------
            */

            $memberIds = array_values(
                array_unique($memberIds)
            );

            if (empty($memberIds)) {
                throw new \RuntimeException(
                    'No PSA IDs were found in the uploaded file.'
                );
            }

            return $memberIds;

        } catch (ValidationException $exception) {

            throw $exception;

        } catch (\Throwable $exception) {

            throw ValidationException::withMessages([
                'member_file' => $exception->getMessage(),
            ]);
        }
    }
}
