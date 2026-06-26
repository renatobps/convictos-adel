<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use App\Services\ComprovanteService;
use App\Services\QrCodeService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class IngressoController extends Controller
{
    public function show(Inscricao $inscricao)
    {
        $inscricao->loadMissing('igrejaRel.regional');

        return view('ingresso.show', [
            'inscricao' => $inscricao,
            'qrDataUri' => app(QrCodeService::class)->dataUri($inscricao->qrConteudo(), 320),
        ]);
    }

    public function qr(Inscricao $inscricao)
    {
        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $inscricao->qrConteudo(),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 320,
            margin: 8,
        );

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function pdf(Inscricao $inscricao, ComprovanteService $comprovante)
    {
        return response($comprovante->pdfBytes($inscricao), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$comprovante->nomeArquivo($inscricao).'"',
        ]);
    }
}
