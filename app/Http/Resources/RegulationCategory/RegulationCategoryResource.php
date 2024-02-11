<?php

namespace App\Http\Resources\RegulationCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data'    => $this->resource
        ];
    }
}
