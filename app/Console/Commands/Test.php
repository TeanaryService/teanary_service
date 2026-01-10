<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\ProductAttributeValue;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payload = array('attribute_id' => 268266341361434624, 'product_id' => 268271843491364864, 'attribute_value_id' => 268266341369823232,);
        $modelInstance = new ProductAttributeValue();
        $fillableFields = $modelInstance->getFillable();

        // 只保留 fillable 字段，且值不为 null
        $cleanPayload = [];
        foreach ($fillableFields as $field) {
            if (!empty($field) && is_string($field) && isset($payload[$field]) && $payload[$field] !== null) {
                $cleanPayload[$field] = $payload[$field];
            }
        }
        // dd($cleanPayload);
        $query = ProductAttributeValue::query();
        foreach ($cleanPayload as $field => $value) {
            if (!empty($field) && is_string($field)) {
                $query->where($field, $value);
            }
        }
        dd($query->get());
        //
        dd(Media::first()->toArray());
    }
}
