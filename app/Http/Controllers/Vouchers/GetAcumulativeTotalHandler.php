<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Illuminate\Http\Response;
use Exception;

class GetAcumulativeTotalHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(): Response
    {
        try {
            $total = $this->voucherService->getAcumulativeTotal();

            return response([
                'data' => [
                    'total_usd' => $total['total_usd'],
                    'total_pen' => $total['total_pen'],
                ],
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
