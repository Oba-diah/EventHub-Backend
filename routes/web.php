<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

});

use Illuminate\Support\Facades\Mail;


Route::get('/test-mail', function () {
    Mail::raw('Test email', function ($message) {
        $message->to('kipobash@gmail.com') // change this
                ->subject('Test Email');
    });

    return 'Email sent';
});

