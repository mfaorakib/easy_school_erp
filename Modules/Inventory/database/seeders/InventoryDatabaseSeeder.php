<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemCategory;
use Modules\Inventory\Models\ItemStore;
use Modules\Inventory\Models\Supplier;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $stationery = ItemCategory::firstOrCreate(['name' => 'Stationery']);
        ItemCategory::firstOrCreate(['name' => 'Furniture']);

        ItemStore::firstOrCreate(['name' => 'Main Store'], ['code' => 'MAIN']);
        Supplier::firstOrCreate(['name' => 'ABC Traders'], ['phone' => '01700000000']);

        Item::firstOrCreate(['name' => 'A4 Paper (Ream)'], ['item_category_id' => $stationery->id, 'unit' => 'ream']);
        Item::firstOrCreate(['name' => 'Marker Pen'], ['item_category_id' => $stationery->id, 'unit' => 'pcs']);
    }
}
