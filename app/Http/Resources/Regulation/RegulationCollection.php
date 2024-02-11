<?php

namespace App\Http\Resources\Regulation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RegulationCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data' => $this->collection->transform(function($data){
                return [
                    'id' => $data->id,
                    'title' => $data->title,
                    'short_title' => $data->short_title,
                    'image' => env('APP_URL', 'localhost:8000') . $data->image,
                    'description' => $data->description,
                    'is_carousel' => $data->is_carousel,
                    'categories' => $data->categories->map(function($cat){
                        return [
                            'id' => $cat->id,
                            'name' => $cat->name
                        ];
                    }),
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                ];
            })
        ];
    }
}
