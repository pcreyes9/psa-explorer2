<?php

use Illuminate\Support\Facades\Route;
use App\Models\Member;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/member-photo/{member}', function (Member $member) {

    if (blank($member->mem_pic)) {
        abort(404);
    }

    $image = $member->mem_pic;

    if (is_string($image) && str_starts_with($image, '0x')) {
        $image = hex2bin(substr($image, 2));
    }

    return response($image)
        ->header('Content-Type', 'image/jpeg');

})->name('member.photo');
