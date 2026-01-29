<?php

namespace App\Exports;

use App\Models\InventoryTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryTransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Model',
            'Warehouse',
            'Type',
            'Transaction Type',
            'Quantity',
            'User',
            'Invoice No',
            'Invoice Date',
            'Invoice File',
            'Remarks',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->model->model_name ?? 'N/A',
            $transaction->warehouse->name ?? 'N/A',
            ucfirst($transaction->type),
            $transaction->transaction_subtype ? ucwords(str_replace('_', ' ', $transaction->transaction_subtype)) : '-',
            $transaction->qty,
            $transaction->creator->name ?? 'N/A',
            $transaction->invoice_no ?? '-',
            $transaction->invoice_date ? \Carbon\Carbon::parse($transaction->invoice_date)->format('d/m/Y') : '-',
            $transaction->invoice_path ? asset('storage/' . $transaction->invoice_path) : '-',
            $transaction->remarks ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
