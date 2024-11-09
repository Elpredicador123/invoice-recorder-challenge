<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VouchersCreatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public array $vouchersRegistered;
    public array $vouchersFailed;
    public User $user;

    public function __construct(array $vouchersRegistered, array $vouchersFailed, User $user)
    {
        $this->vouchersRegistered = $vouchersRegistered;
        $this->vouchersFailed = $vouchersFailed;
        $this->user = $user;
    }

    public function build(): self
    {
        return $this->view('emails.comprobante')
            ->with(['comprobantesRegistrados' => $this->vouchersRegistered,'comprobantesFallidos' => $this->vouchersFailed,'user' => $this->user]);
    }
}
