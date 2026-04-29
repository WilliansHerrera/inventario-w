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
        
        if (!class_exists($paletteClass)) {
            $paletteClass = PurplePalette::class;
        }

        $palette = new $paletteClass();
        $colors = method_exists($palette, 'getColors') ? $palette->getColors() : [];
        
        // El formato guardado suele ser OKLCH, por ejemplo: "0.58 0.24 293.756"
        return isset($colors['primary']) ? $colors['primary'] : '0.58 0.24 293.756';
    }

    public function logo(): Response
    {
        $primaryOklch = $this->getActivePrimaryColor();
        
        $svg = <<<SVG
<svg width="650" height="150" viewBox="0 0 650 150" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bgGradDefault" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="oklch({$primaryOklch})" />
      <stop offset="100%" stop-color="oklch(0.77 0.16 293.756)" />
    </linearGradient>
  </defs>

  <g transform="translate(10, -25) scale(0.4)">
    <circle cx="250" cy="250" r="180" fill="url(#bgGradDefault)" />
    <path d="M140 180 L200 340 L250 230 L300 340 L360 180" stroke="#ffffff" stroke-width="32" stroke-linecap="round" stroke-linejoin="round" />
  </g>

  <text x="230" y="95" font-family="'Outfit', 'Inter', sans-serif" font-weight="900" font-size="64" fill="#ffffff" letter-spacing="-2">Inventario</text>
  <text x="555" y="95" font-family="'Outfit', 'Inter', sans-serif" font-weight="900" font-size="64" fill="oklch({$primaryOklch})" letter-spacing="-2">W</text>
</svg>
SVG;

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function logoSmall(): Response
    {
        $primaryOklch = $this->getActivePrimaryColor();

        $svg = <<<SVG
<svg width="500" height="500" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bgGradDefault" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="oklch({$primaryOklch})" />
      <stop offset="100%" stop-color="oklch(0.77 0.16 293.756)" />
    </linearGradient>
  </defs>

  <circle cx="250" cy="250" r="250" fill="url(#bgGradDefault)" />
  <path d="M100 150 L180 370 L250 220 L320 370 L400 150" stroke="#ffffff" stroke-width="52" stroke-linecap="round" stroke-linejoin="round" />
</svg>
SVG;

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}
