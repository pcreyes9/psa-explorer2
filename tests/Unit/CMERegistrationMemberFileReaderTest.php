<?php

namespace Tests\Unit;

use App\Support\CMERegistrationMemberFileReader;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CMERegistrationMemberFileReaderTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_reads_member_ids_and_payment_references_from_export_headers(): void
    {
        $path = $this->csvFile(
            "\xEF\xBB\xBFPSA ID,Payment Ref. No.\n12345,V260010001\n67890,V260010002\n"
        );

        $result = app(CMERegistrationMemberFileReader::class)->read($path);

        $this->assertSame(
            [
                'member_ids' => ['12345', '67890'],
                'payment_references' => [
                    '12345' => 'V260010001',
                    '67890' => 'V260010002',
                ],
            ],
            $result
        );
    }

    public function test_rejects_a_member_row_without_a_payment_reference(): void
    {
        $path = $this->csvFile(
            "PSA ID,Payment Reference Number\n12345,\n"
        );

        try {
            app(CMERegistrationMemberFileReader::class)->read($path);
            $this->fail('Expected a missing payment reference to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'Payment reference number is required on spreadsheet row 2.',
                $exception->errors()['member_file'][0]
            );
        }
    }

    private function csvFile(string $contents): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'cme-import-');
        $path = $temporaryPath . '.csv';
        rename($temporaryPath, $path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
