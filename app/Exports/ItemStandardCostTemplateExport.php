<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Site;
use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemStandardCostTemplateExport implements FromCollection, WithHeadings
{
    protected array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function headings(): array
    {
        return [
            'client id',
            'client name',
            'site id',
            'site name',
            'item id',
            'item name',
            'standard cost',
            'start date',
            'end date',
        ];
    }

    public function collection(): Collection
    {
        $collection = collect();

        $client = Client::where('id', $this->params['client_id'])->first();
        $site = Site::where('id', $this->params['site_id'])->first();
        $items = Item::where('active', '1')->get();

        foreach ($items as $item) {
            $collection->push([
                'client id' => $this->params['client_id'],
                'client name' => $client->name,
                'site id' => $this->params['site_id'],
                'site name' => $site->name,
                'item id' => $item->id,
                'item name' => $item->name,
                'standard cost' => $item->standard_cost,
                'start date' => '',
                'end date' => ''
            ]);
        }

        return $collection;
    }
}
