<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Support\Collection;
use App\Constants\CurrencyConstants;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleXMLElement;
use Carbon\Carbon;
use App\Jobs\StoreVoucherJob;

class VoucherService
{
    public function getVouchers(int $page, int $paginate, ?string $serie, ?string $number, ?string $start , ?string $end): LengthAwarePaginator
    {
        return Voucher::with(['lines', 'user'])
        ->when($serie, function ($query, $serie) {
            return $query->where('document_series', $serie);
        })
        ->when($number, function ($query, $number) {
            return $query->where('document_number', $number);
        })
        ->when($start && $end, function ($query) use ($start, $end) {
            $startDate = Carbon::parse($start)->startOfDay(); 
            $endDate = Carbon::parse($end)->endOfDay(); 
            return $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->paginate(perPage: $paginate, page: $page);
    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): void
    {
        StoreVoucherJob::dispatch($xmlContents, $user);
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {

            $xml = new SimpleXMLElement($xmlContent);
            
            $documentSeries = (string) explode('-', $xml->xpath('//cbc:ID')[0])[0];
            $documentNumber = (string) explode('-', $xml->xpath('//cbc:ID')[0])[1];
            $documentVoucher_type = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
            $documentCurrency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];
            
            $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
            $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
            $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];
            
            $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
            $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
            $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

            $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

            $voucher = new Voucher([
                'document_series' => $documentSeries,
                'document_number' => $documentNumber,
                'document_voucher_type' => $documentVoucher_type,
                'document_currency' => $documentCurrency,
                'issuer_name' => $issuerName,
                'issuer_document_type' => $issuerDocumentType,
                'issuer_document_number' => $issuerDocumentNumber,
                'receiver_name' => $receiverName,
                'receiver_document_type' => $receiverDocumentType,
                'receiver_document_number' => $receiverDocumentNumber,
                'total_amount' => $totalAmount,
                'xml_content' => $xmlContent,
                'user_id' => $user->id,
            ]);
            $voucher->save();

            foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
                $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
                $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

                $voucherLine = new VoucherLine([
                    'name' => $name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'voucher_id' => $voucher->id,
                ]);

                $voucherLine->save();
            }

            return $voucher;

    }

    public function updateVouchersFromXmlContents(): array
    {
        $vouchers = [];

        $vouchersEmpty = Voucher::where('document_series', '')
            ->orWhere('document_number', '')
            ->orWhere('document_voucher_type', '')
            ->orWhere('document_currency', '')
            ->get();

        foreach ($vouchersEmpty as $voucherEmpty) {
            $vouchers[] = $this->updateVoucherFromXmlContent($voucherEmpty);
        }

        VouchersCreated::dispatch($vouchers, [], auth()->user());
        
        return $vouchers;
    }

    public function updateVoucherFromXmlContent(Voucher $voucher): Voucher
    {

        $xml = new SimpleXMLElement($voucher->xml_content);
        
        $documentSeries = (string) explode('-', $xml->xpath('//cbc:ID')[0])[0];
        $documentNumber = (string) explode('-', $xml->xpath('//cbc:ID')[0])[1];
        $documentVoucher_type = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $documentCurrency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $voucher->document_series = $documentSeries;
        $voucher->document_number = $documentNumber;
        $voucher->document_voucher_type = $documentVoucher_type;
        $voucher->document_currency = $documentCurrency;

        $voucher->save();

        return $voucher;
    }

    public function getAcumulativeTotal(): array
    {

        $vouchers = Voucher::all();

        $total_usd = $this->convertCurrency(CurrencyConstants::USD, $vouchers);
        $total_pen = $this->convertCurrency(CurrencyConstants::PEN, $vouchers);

        return [
            'total_usd' => $total_usd,
            'total_pen' => $total_pen,
        ];
    }

    public function convertCurrency(array $currency, Collection $vouchers): array
    {
        $total = 0;
        foreach ($vouchers as $voucher) {
            if ($voucher->document_currency === $currency['code']) {
                $total += $voucher->total_amount;
            } else {
                $total += $voucher->total_amount * CurrencyConstants::getCurrency($voucher->document_currency)['to_'.$currency['code']];
            }
        }

        return [
            'total' => round($total, 2),
        ];
    }

    public function deleteVoucher(string $id): void
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();
    }
}