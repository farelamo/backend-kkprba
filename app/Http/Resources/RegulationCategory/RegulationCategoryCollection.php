<?php

namespace App\Http\Resources\RegulationCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RegulationCategoryCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data' => $this->collection->transform(function($data){
                return [
                    'id' => $data->id,
                    'name' => $data->name
                ];
            })
        ];
    }
}
