<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('lamii:about', function () {
    $this->info('Lamii — discover and connect with people around you.');
})->purpose('Show Lamii application information');
