<?php

/*
 * 作者：wxsatellite<1453085314@qq.com>
 */

namespace Wxsatellite\Weather;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Weather::class, function () {
            return new Weather(config('services.weather.key'));
        });
        $this->app->alias(Weather::class, 'weather');
    }

    public function providers()
    {
        return [Weather::class, 'weather'];
    }
}
