<?php

declare(strict_types=1);

namespace App\Services;

class Code128BarcodeGenerator
{
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    public function svg(string $text, int $barHeight = 54, int $moduleWidth = 2): string
    {
        $codes = $this->encodeCodeB($text);
        $width = array_sum(array_map(
            fn ($code) => array_sum(array_map('intval', str_split(self::PATTERNS[$code]))),
            $codes
        )) * $moduleWidth;
        $height = $barHeight + 22;

        $bars = '';
        $x = 0;

        foreach ($codes as $code) {
            $pattern = self::PATTERNS[$code];

            foreach (str_split($pattern) as $index => $widthMultiplier) {
                $segmentWidth = (int) $widthMultiplier * $moduleWidth;

                if ($index % 2 === 0) {
                    $bars .= sprintf(
                        '<rect x="%d" y="0" width="%d" height="%d" fill="#000"/>',
                        $x,
                        $segmentWidth,
                        $barHeight
                    );
                }

                $x += $segmentWidth;
            }
        }

        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $textY = $barHeight + 16;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="Barcode {$escapedText}">
<rect width="100%" height="100%" fill="#fff"/>
{$bars}
<text x="50%" y="{$textY}" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#000">{$escapedText}</text>
</svg>
SVG;
    }

    private function encodeCodeB(string $text): array
    {
        $codes = [104];

        foreach (str_split($text) as $char) {
            $ascii = ord($char);

            if ($ascii < 32 || $ascii > 126) {
                throw new \InvalidArgumentException('Code 128 B hanya mendukung karakter ASCII 32-126.');
            }

            $codes[] = $ascii - 32;
        }

        $checksum = $codes[0];

        for ($i = 1; $i < count($codes); $i++) {
            $checksum += $codes[$i] * $i;
        }

        $codes[] = $checksum % 103;
        $codes[] = 106;

        return $codes;
    }
}
