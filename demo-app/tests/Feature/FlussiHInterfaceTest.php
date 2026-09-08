<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FlussiHInterfaceTest extends TestCase
{
    public function test_upload_page_is_available(): void
    {
        $this->get('/flussi-h')
            ->assertOk()
            ->assertSee('Validatore Flusso H')
            ->assertSee('Valida');
    }

    public function test_validation_downloads_an_excel_report(): void
    {
        $response = $this->post('/flussi-h/validate', [
            'h1' => UploadedFile::fake()->createWithContent('prova.h1', 'record non valido'),
            'h2' => UploadedFile::fake()->createWithContent('prova.h2', 'record non valido'),
        ]);

        $response->assertOk()
            ->assertDownload()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
