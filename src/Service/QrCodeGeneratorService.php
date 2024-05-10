<?php
namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGeneratorService
{
    public function generate($data): string
    {
        $qrCode = new QrCode($data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $tmpFile = tempnam(sys_get_temp_dir(), 'qr_code');
        $result->saveToFile($tmpFile);

        return $tmpFile;
    }
}