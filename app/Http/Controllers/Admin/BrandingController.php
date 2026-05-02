<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use MoonShine\ColorManager\Palettes\PurplePalette;

class BrandingController extends Controller
{
    private function getActivePrimaryColor(): string
    {
        $paletteClass = get_global_setting('theme_palette', PurplePalette::class);

        if (! class_exists($paletteClass)) {
            $paletteClass = PurplePalette::class;
        }

        $palette = new $paletteClass;
        $colors = method_exists($palette, 'getColors') ? $palette->getColors() : [];

        // El formato guardado suele ser OKLCH, por ejemplo: "0.58 0.24 293.756"
        return isset($colors['primary']) ? $colors['primary'] : '0.58 0.24 293.756';
    }

    public function logo(): Response
    {
        return $this->generateLogo('#ffffff');
    }

    public function logoLight(): Response
    {
        return $this->generateLogo('#000000');
    }

    public function logoSmall(): Response
    {
        return $this->logo();
    }

    private function generateLogo(string $textColor): Response
    {
        $primaryOklch = $this->getActivePrimaryColor();

        $svg = <<<SVG
<svg width="500" height="150" viewBox="0 0 500 150" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Text Part (Laravel Best Practices: Fixed dimensions + centered anchor) -->
  <text x="250" y="105" text-anchor="middle" font-family="'Outfit', 'Inter', sans-serif" font-weight="900" font-size="80" letter-spacing="-3">
    <tspan fill="{$textColor}">Inventario-</tspan><tspan fill="oklch({$primaryOklch})">W</tspan>
  </text>
</svg>
SVG;

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}
