<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    /**
     * Gera o QR Code como data URI PNG (base64), pronto para uso em <img src="...">.
     */
    public function dataUri(string $conteudo, int $size = 300, int $margin = 8): string
    {
        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $conteudo,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: $margin,
        );

        return $result->getDataUri();
    }

    /**
     * Gera o QR Code como bytes PNG (conteúdo binário).
     */
    public function pngBytes(string $conteudo, int $size = 300, int $margin = 8): string
    {
        $result = (new Builder())->build(
            writer: new PngWriter(),
            data: $conteudo,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: $margin,
        );

        return $result->getString();
    }

    /**
     * Gera o QR Code como markup SVG inline.
     */
    public function svg(string $conteudo, int $size = 300, int $margin = 8): string
    {
        $result = (new Builder())->build(
            writer: new SvgWriter(),
            data: $conteudo,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: $margin,
        );

        return $result->getString();
    }
}
