<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/check-db', function () {
    $connection = config('database.default');
    $database = config("database.connections.$connection.database");
    
    return [
        'connection' => $connection,
        'database' => $database,
        'host' => config("database.connections.$connection.host"),
        'port' => config("database.connections.$connection.port"),
    ];
});