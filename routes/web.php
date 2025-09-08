<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LogbookTemplateController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});
