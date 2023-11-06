<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Site;
use App\Models\Client;
use App\Models\ItemStandardCost;
use App\Models\ServiceType;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchaseRequestTemplateExport implements FromCollection, WithHeadings
{
    protected array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function headings(): array
    {
        return [
            'no',
            'need approval flag',
            'request date',
            'request delivery date',
            'request receive date',
            'client id',
            'client name',
            'site id',
            'site name',
            'service type id',
            'service type',
            'warehouse id',
            'warehouse code',
            'purchase type',
            'request type',
            'process type',
            'inspection status',
            'remarks',
            'item id',
            'item code',
            'request qty',
            'gl no',
            'description',
            'standard cost',
            'uom',
            'category id',
            'category name',
            'vendor id'
        ];
    }

    public function collection(): Collection
    {
        $collection = collect();

        $client = Client::where('id', $this->params['client_id'])->first();
        $site = Site::where('id', $this->params['site_id'])->first();
        $serviceType = ServiceType::where('id', $this->params['service_type_id'])->first();
        $warehouse = Warehouse::where('id', $this->params['warehouse_id'])->first();
        $category = Category::where('id', $this->params['category_id'])->first();
        $items = ItemStandardCost::with('item')
            ->whereRelation('item', 'category_1', $this->params['category_id'])
            ->where('site_id', $this->params['site_id'])
            ->whereNull('end_date')
            ->get();

        foreach ($items as $item) {
            $collection->push([
                'no' => '',
                'need approval flag' => $this->params['need_approval_flag'],
                'request date' => $this->params['request_date'],
                'request delivery date' => $this->params['request_delivery_date'],
                'request receive date' => $this->params['request_receive_date'],
                'client id' => $this->params['client_id'],
                'client name' => $client->code,
                'site id' => $this->params['site_id'],
                'site name' => $site->name,
                'service type id' => $this->params['service_type_id'],
                'service type' => $serviceType->name,
                'warehouse id' => $this->params['warehouse_id'],
                'warehouse code' => $warehouse->code,
                'purchase type' => 'item',
                'request type' => '0',
                'process type' => $this->params['process_type'],
                'inspection status' => '0',
                'remarks' => '',
                'item id' => $item->item_id,
                'item code' => $item->item->code,
                'request qty' => '',
                'gl no' => '',
                'description' => $item->item->name,
                'standard cost' => $item->standard_cost,
                'uom' => $item->item->uom->code,
                'category id' => $this->params['category_id'],
                'category name' => $category->name,
                'vendor id' => $this->params['vendor_id']
            ]);
        }

        return $collection;
    }
}
