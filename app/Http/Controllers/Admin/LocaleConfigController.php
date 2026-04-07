<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use MoonShine\Laravel\MoonShineUI;

class LocaleConfigController extends Controller
{
    public function regenerate(Locale $locale)
    {
        $locale->sync_token = \Illuminate\Support\Str::random(32);
        $locale->save();

        MoonShineUI::toast('Token de Sincronización regenerado exitosamente', \MoonShine\Support\Enums\ToastType::SUCCESS);
        return back();
    }

    public function download(Locale $locale)
    {
        $json = $locale->getConfigJson();
        $filename = 'pos_config_' . \Illuminate\Support\Str::slug($locale->nombre) . '.json';

        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
