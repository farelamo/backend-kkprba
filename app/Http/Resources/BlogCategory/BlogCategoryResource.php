<?php

namespace App\Http\Resources\BlogCategory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data'    => $this->resource
        ];
    }
}
