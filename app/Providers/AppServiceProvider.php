<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

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
        Table::$defaultNumberLocale = 'nl';
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar','en']) // also accepts a closure
                ->displayLocale('ar');
        });
        Model::unguard();

        FilamentColor::register([
            'Fuchsia' =>  Color::Fuchsia,
            'green' =>  Color::Green,
            'blue' =>  Color::Blue,
            'gray' =>  Color::Gray,
        ]);

        FilamentAsset::register([
            \Filament\Support\Assets\Js::make('example-external-script', 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js'),

        ]);
        FilamentView::registerRenderHook(
            'panels::page.end',
            fn (): View => view('analytics'),
            scopes: [
                \App\Filament\Resources\BuyResource::class,


            ]
        );
    }
}
