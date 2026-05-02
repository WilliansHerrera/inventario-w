<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use Illuminate\Support\Str;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Support\Enums\ToastType;

class LocaleConfigController extends Controller
{
    public function regenerate(Locale $locale)
    {
        $locale->sync_token = Str::random(32);
        $locale->save();

        MoonShineUI::toast('Token de Sincronización regenerado exitosamente', ToastType::SUCCESS);

        return back();
    }

    public function download(Locale $locale)
    {
        $json = $locale->getConfigJson();
        $filename = 'pos_config_'.Str::slug($locale->nombre).'.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
