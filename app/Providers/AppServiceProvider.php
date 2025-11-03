<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use App\Filament\Resources\BuyResource;
use App\Models\Setting;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register Bokreah application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap Bokreah application services.
     */
    public function boot(): void
    {
        Pdf::default()
            ->footerView('PrnView.footer')
            ->withBrowsershot(function (Browsershot $shot) {
                $shot->noSandbox()
                    ->setChromePath(Setting::first()->exePath);
            })
            ->margins(10, 10, 20, 10, );
        Table::configureUsing(fn(Table $table) => $table->defaultNumberLocale('nl')
            ->pluralModelLabel('الصفحات')
            ->emptyStateHeading('لا توجد بيانات')
            ->defaultKeySort(false)
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn (): string => Blade::render('@livewire(\'top-bar\')'),
        );
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('admin') || $user->hasRole('supper') ) ? true : null;
        });

        Model::unguard();

        FilamentColor::register([
            'Fuchsia' =>  Color::Fuchsia,
            'green' =>  Color::Green,
            'blue' =>  Color::Blue,
            'gray' =>  Color::Gray,
        ]);

        FilamentAsset::register([
            Js::make('example-external-script', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js'),

        ]);
        FilamentView::registerRenderHook(
            'panels::page.end',
            fn (): View => view('analytics'),
            scopes: [
                BuyResource::class,


            ]
        );
    }
}
