<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Response;
use Exception;

class GetVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(GetVouchersRequest $request): Response
    {
        try {
            $vouchers = $this->voucherService->getVouchers(
                $request->query('page',1),
                $request->query('paginate',10),
                $request->query('serie',null),
                $request->query('number',null),
                $request->query('start',null),
                $request->query('end',null)
            );

            return response([
                'data' => VoucherResource::collection($vouchers),
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
