<?php

namespace App\Services;

use App\Models\Inscricao;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprovanteService
{
    public function __construct(
        protected QrCodeService $qrCode,
    ) {}

    /**
     * Gera o PDF do comprovante de inscrição e retorna o conteúdo binário.
     */
    public function pdfBytes(Inscricao $inscricao): string
    {
        $inscricao->loadMissing('igrejaRel.regional');

        $pdf = Pdf::loadView('pdf.comprovante', [
            'inscricao' => $inscricao,
            'qrDataUri' => $this->qrCode->dataUri($inscricao->qrConteudo(), 320),
            'logoBase64' => $this->logoBase64(),
        ])->setPaper('a4');

        return $pdf->output();
    }

    public function nomeArquivo(Inscricao $inscricao): string
    {
        return 'comprovante-'.($inscricao->codigo ?: 'inscricao').'.pdf';
    }

    private function logoBase64(): ?string
    {
        $caminho = public_path('assets/logos/um-escudo-azul.png');

        if (! is_file($caminho)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($caminho));
    }
}
