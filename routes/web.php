<?php

use Illuminate\Support\Facades\Route;
use App\Models\Member;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CashVoucherController;

Route::get(
    '/cash-vouchers/{cashVoucher}/pdf',
    [CashVoucherController::class, 'voucherPdf']
)->name('cash-voucher.pdf');

Route::get(
    '/cash-vouchers/{cashVoucher}/check',
    [CashVoucherController::class, 'checkPdf']
)->name('cash-voucher.check');

Route::get(
    '/certificate/good-standing/{member}',
    [CertificateController::class, 'cogs']
)->name('certificate.cogs');

Route::get('/debug-php', function () {
    return [
        'php_version' => PHP_VERSION,
        'openssl_loaded' => extension_loaded('openssl'),
        'openssl_function' => function_exists('openssl_cipher_iv_length'),
        'ini' => php_ini_loaded_file(),
    ];
});

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/admin');
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
