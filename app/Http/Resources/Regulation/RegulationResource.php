<?php

namespace App\Http\Resources\Regulation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data' => [
                'id' => $this->id,
                'title' => $this->title,
                'short_title' => $this->short_title,
                'image' => env('APP_URL', 'localhost:8000') . $this->image,
                'description' => $this->description,
                'categories' => $this->categories->map(function($cat){
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name
                    ];
                }),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
