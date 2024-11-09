<?php

use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\GetAcumulativeTotalHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use App\Http\Controllers\Vouchers\UpdateVouchersHandler;
use App\Http\Controllers\Vouchers\Voucher\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\Voucher\GetVoucherHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::post('/', StoreVouchersHandler::class);
        Route::put('/update-vouchers-empty', UpdateVouchersHandler::class);
        Route::get('/acumulative-total', GetAcumulativeTotalHandler::class);
        Route::delete('/{id}', DeleteVoucherHandler::class);
    }
);
