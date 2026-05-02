<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\Crud\Traits\WithComponentsPusher;
use MoonShine\Laravel\Layouts\BaseLayout;
use MoonShine\UI\Components\Components;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Body;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Html;
use MoonShine\UI\Components\Layout\Layout;
use MoonShine\UI\Components\Layout\Logo;

class CustomLoginLayout extends BaseLayout
{
    use WithComponentsPusher;

    protected function getLogoComponent(): Logo
    {
        $logo = $this->getLogo();
        $small = $this->getLogo(small: true);

        // Path to light versions created by script
        $lightLogo = str_replace('.svg', '-light.svg', $logo);
        $lightSmall = str_replace('.svg', '-light.svg', $small);

        return Logo::make(
            href: $this->getCore()->getConfig()->getHomeRoute(),
            logo: $lightLogo, // Light mode logo (default in MoonShine)
            logoSmall: $lightSmall,
        )->darkMode(
            logo: $logo,      // Dark mode logo
            small: $small
        );
    }

    protected ?string $title = null;

    protected ?string $description = null;

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title ?? __(
            'moonshine::ui.login.title',
            ['moonshine_title' => $this->getCore()->getConfig()->getTitle()],
        );
    }

    public function getDescription(): string
    {
        return $this->description ?? __('moonshine::ui.login.description');
    }

    protected function getLogo(bool $small = false): string
    {
        $paletteClass = get_global_setting('theme_palette', PurplePalette::class);
        $colorName = strtolower(str_replace(['MoonShine\ColorManager\Palettes\\', 'Palette'], '', $paletteClass));

        $suffix = $small ? '-small' : '';
        $logo = "logo-{$colorName}{$suffix}.svg";

        if (! file_exists(public_path("vendor/moonshine/$logo"))) {
            $logo = $small ? 'logo-small.svg' : 'logo.svg';
        }

        return asset("vendor/moonshine/$logo") . '?v=' . time();
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                FlexibleRender::make('<style>
                    .authentication {
                        min-height: 100vh;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        padding: 0.5rem !important;
                        background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.12), transparent),
                                    radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.08), transparent);
                        overflow: hidden;
                    }
                    .authentication-logo {
                        margin-bottom: 0.75rem !important;
                        margin-top: 0 !important;
                        display: flex;
                        justify-content: center;
                        color: white !important;
                    }
                    .authentication-logo img {
                        width: 280px !important;
                        height: auto !important;
                        max-height: 160px !important;
                        transition: transform 0.3s ease;
                    }
                    .authentication-content {
                        width: 100%;
                        max-width: 380px;
                        animation: slideUp 0.5s ease-out;
                        margin-top: 0 !important;
                    }
                    .authentication-header {
                        text-align: center;
                        margin-bottom: 1rem !important;
                        margin-top: 0 !important;
                    }
                    .authentication-header h1 {
                        color: var(--primary, #7c3aed) !important;
                        font-weight: 800 !important;
                        letter-spacing: -0.025em !important;
                        font-size: 1.5rem !important;
                        margin-bottom: 0 !important;
                        margin-top: 0 !important;
                    }
                    .authentication-header .description {
                        color: var(--body-color-60, #9ca3af);
                        font-size: 0.75rem;
                        margin-top: 0 !important;
                    }
                    /* Formulario ultra compacto */
                    .authentication-content form {
                        margin-top: 0 !important;
                    }
                    .authentication-content .form-group {
                        margin-bottom: 0.75rem !important;
                    }
                    .authentication-content .form-group label {
                        margin-bottom: 0.25rem !important;
                        font-size: 0.75rem !important;
                    }
                    .authentication-content .btn {
                        padding: 0.5rem 1rem !important;
                    }
                    @keyframes slideUp {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    html, body {
                        height: 100%;
                        overflow: hidden;
                    }
                    @media (max-height: 600px) {
                        .authentication-content { transform: scale(0.9); transform-origin: center top; }
                        .authentication { justify-content: flex-start; padding-top: 1rem !important; }
                    }
                    @media (max-height: 500px) {
                        html, body { overflow-y: auto; }
                        .authentication { height: auto; min-height: 100vh; }
                    }
                </style>'),
                Body::make([
                    Div::make([
                        Div::make([
                            $this->getLogoComponent(),
                        ])->class('authentication-logo'),

                        Div::make([
                            Components::make($this->getPage()->getComponents()),
                        ])->class('authentication-content'),

                        ...$this->getPushedComponents(),
                    ])->class('authentication'),
                ]),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->when(
                    $this->hasThemes() || $this->isAlwaysDark(),
                    fn (Html $html): Html => $html->withThemes($this->isAlwaysDark())
                ),
        ]);
    }
}
