<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\VoucherService;
use App\Events\Vouchers\VouchersCreated;
use Exception;

class StoreVoucherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    private array $xmlContents;
    private User $user;



    public function __construct(array $xmlContents, User $user)
    {
        $this->xmlContents = $xmlContents;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $voucherService = new VoucherService();

        $vouchersRegistered = [];
        $vouchersFailed = [];

        foreach ($this->xmlContents as $xmlContent) {
            try {
                $vouchersRegistered[] = $voucherService->storeVoucherFromXmlContent($xmlContent, $this->user);
            } catch (Exception $e) {
                $vouchersFailed[] = ['error' => $e->getMessage()];
            }
        }

        VouchersCreated::dispatch($vouchersRegistered, $vouchersFailed, $this->user);
    }
}
