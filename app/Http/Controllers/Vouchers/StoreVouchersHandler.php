<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $xmlFiles = $request->file('files');
            
            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                if ($xmlFile->getClientOriginalExtension() === 'xml') {
                    $xmlContents[] = file_get_contents($xmlFile->getRealPath());
                }
            }
            
            $user = auth()->user();
            $this->voucherService->storeVouchersFromXmlContents($xmlContents, $user);

            return response([
                'message' => 'Vouchers created successfully',
            ], 201);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
