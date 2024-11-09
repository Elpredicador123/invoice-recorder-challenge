<?php

namespace App\Http\Controllers\Vouchers\Voucher;

use App\Services\VoucherService;
use Illuminate\Http\Response;
use Exception;

class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(string $id): Response
    {
        try {
            $this->voucherService->deleteVoucher($id);

            return response([
                'message' => 'Voucher deleted successfully.'
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
