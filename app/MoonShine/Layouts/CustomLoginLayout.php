<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\BaseLayout;
use MoonShine\Crud\Traits\WithComponentsPusher;
use MoonShine\UI\Components\Components;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\{Body, Div, Html, Layout};

class CustomLoginLayout extends BaseLayout
{
    use WithComponentsPusher;

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
        $paletteClass = get_global_setting('theme_palette', \MoonShine\ColorManager\Palettes\PurplePalette::class);
        $colorName = strtolower(str_replace(['MoonShine\ColorManager\Palettes\\', 'Palette'], '', $paletteClass));

        $logo = "logo-{$colorName}.svg";

        if (!file_exists(public_path("vendor/moonshine/$logo"))) {
            $logo = 'logo.svg';
        }

        return $this->getAssetManager()->getAsset("/vendor/moonshine/$logo");
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                FlexibleRender::make('<style>
                    .authentication-header h1 {
                        color: var(--primary, #7c3aed) !important;
                    }
                    .authentication-logo {
                        margin-bottom: 2rem !important;
                    }
                    .authentication-logo img {
                        width: 110px !important;
                        height: 110px !important;
                        max-height: 110px !important;
                        filter: drop-shadow(0 15px 20px rgba(0, 0, 0, 0.3));
                        border-radius: 9999px;
                    }
                </style>'),
                Body::make([
                    Div::make([
                        Div::make([
                            $this->getLogoComponent(),
                        ])->class('authentication-logo'),

                        Div::make([
                            Div::make([
                                Heading::make(
                                    $this->getTitle(),
                                )->h(1, false),
                                Div::make([
                                    FlexibleRender::make(
                                        $this->getDescription(),
                                    ),
                                ])->class('description'),
                            ])->class('authentication-header'),

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
