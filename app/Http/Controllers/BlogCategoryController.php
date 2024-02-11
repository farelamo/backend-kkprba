<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Http\Requests\BlogCategoryRequest;
use App\Http\Resources\BlogCategory\BlogCategoryCollection;
use App\Http\Resources\BlogCategory\BlogCategoryResource;

class BlogCategoryController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api')->except(['index', 'show']);
    }

    public function index(Request $request) {
        $categories = BlogCategory::select('id', 'name')
                                    ->when($request->search, function($q){
                                        $q->where('name', 'like', '%'.$request->search.'%');
                                    })
                                    ->orderBy('id', 'desc')
                                    ->paginate(10);

        return new BlogCategoryCollection($categories);
    }

    public function show($id){
        $category = BlogCategory::select('id', 'name')->where('id', $id)->first();
        if(!$category) return $this->returnCondition(false, 404, 'data not found');

        return new BlogCategoryResource($category);
    }

    public function store(BlogCategoryRequest $request) {
        try {
            BlogCategory::create([
                'name' => $request->name,
            ]);

            return $this->returnCondition(true, 200, 'Successfully created data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function update($id, BlogCategoryRequest $request) {
        try {
            $category = BlogCategory::select('id', 'name')->where('id', $id)->first();
            if(!$category) return $this->returnCondition(false, 404, 'data not found');

            $category->update([
                'name' => $request->name,
            ]);

            return $this->returnCondition(true, 200, 'Successfully updated data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function destroy($id) {
        try {
            $category = BlogCategory::select('id', 'name')->where('id', $id)->first();
            if(!$category) return $this->returnCondition(false, 404, 'data not found');

            $category->blogs()->detach();
            $category->delete();

            return $this->returnCondition(true, 200, 'Successfully deleted data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }
}
