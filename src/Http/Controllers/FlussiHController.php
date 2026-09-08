<?php

namespace FlussiH\Validator\Http\Controllers;

use FlussiH\Validator\FlussiHValidator;
use FlussiH\Validator\Reports\XlsxReportExporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class FlussiHController extends Controller
{
    public function index()
    {
        return view('flussi-h-validator::upload');
    }

    public function validateFiles(Request $request, FlussiHValidator $validator, XlsxReportExporter $exporter)
    {
        $max = (int) config('flussi-h.web.max_upload_kb', 51200);
        $validated = $request->validate([
            'h1' => ['required', 'file', 'max:'.$max],
            'h2' => ['required', 'file', 'max:'.$max],
        ], [
            'h1.required' => 'Seleziona il file H1 / ANA.',
            'h2.required' => 'Seleziona il file H2 / SAN.',
            'h1.file' => 'Il file H1 caricato non è valido.',
            'h2.file' => 'Il file H2 caricato non è valido.',
            'h1.max' => 'Il file H1 supera la dimensione massima consentita.',
            'h2.max' => 'Il file H2 supera la dimensione massima consentita.',
        ]);

        $h1 = $validated['h1'];
        $h2 = $validated['h2'];
        $result = $validator->validate($h1->getRealPath(), $h2->getRealPath());
        $report = $exporter->export($result, $h1->getClientOriginalName(), $h2->getClientOriginalName());
        $filename = 'report-errori-flusso-h-'.date('Ymd-His').'.xlsx';

        return response()->download(
            $report,
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        )->deleteFileAfterSend(true);
    }
}
