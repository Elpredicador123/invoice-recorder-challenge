<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Response;

class UpdateVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(): Response
    {
        try {

            $vouchers = $this->voucherService->updateVouchersFromXmlContents();

            return response([
                'data' => VoucherResource::collection($vouchers),
            ], 201);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
