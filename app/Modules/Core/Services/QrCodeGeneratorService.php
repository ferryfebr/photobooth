<?php

namespace App\Modules\Core\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeGeneratorService
{
    public function svg(string $url, int $size = 600): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);
    }
}
