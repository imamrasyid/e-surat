<?php

namespace App\Actions\Telegram;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class RenderTelegramQrAction
{
    /**
     * Render payload (deep link bot) menjadi markup SVG untuk dialog pairing.
     */
    public function handle(string $payload, int $size = 320): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(max(160, $size), 1),
            new SvgImageBackEnd
        );

        $svg = (new Writer($renderer))->writeString($payload);

        // Prolog XML tidak valid saat markup disisipkan langsung ke DOM.
        return trim((string) preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg));
    }
}
