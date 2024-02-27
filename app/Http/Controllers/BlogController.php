<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Http\Requests\BlogRequest;
use App\Http\Requests\BlogUpdateRequest;
use App\Http\Resources\Blog\BlogCollection;
use App\Http\Resources\Blog\BlogResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except(['index', 'show']);
    }
    
    public function carousel() {
        $blogs = Blog::select('id', 'title', 'short_title', 'image', 'description', 'is_carousel', 'created_at', 'updated_at')
                        ->where('is_carousel', true)
                        ->orderBy('id', 'desc')
                        ->paginate(3);

        return new BlogCollection($blogs);
    }

    public function index(Request $request) {
        $blogs = Blog::select('id', 'title', 'short_title', 'image', 'description', 'is_carousel', 'created_at', 'updated_at')
                        ->when($request->search, function($q) use ($request) {
                            $q->where('title', 'like', '%'.$request->search.'%');
                            $q->orWhere('short_title', 'like', '%'.$request->search.'%');
                        })
                        ->when($request->categories, function($q) use ($request){
                            $q->whereHas('categories', function($q) use ($request){
                                $request->categories = explode(',', $request->categories);
                                $q->whereIn('blog_categories.id', $request->categories);
                            });
                        })
                        ->orderBy('id', 'desc')
                        ->paginate(10);

        return new BlogCollection($blogs);
    }

    public function show($id){
        $blog = Blog::select('id', 'title', 'short_title', 'image', 'description', 'is_carousel', 'created_at', 'updated_at')
                        ->where('id', $id)->first();
        if(!$blog) return $this->returnCondition(false, 404, 'data not found');

        return new BlogResource($blog);
    }

    public function store(BlogRequest $request) {
        
        $rules = [
            'image' => 'required|mimes:jpg,jpeg,png|max:5048',
        ];

        Validator::make($request->all(), $rules, $messages = 
        [
            'image.required' => 'Gambar harus diisi',
            'image.mimes'    => 'Gambar harus berupa jpg, png atau jpeg',
            'image.max'      => 'Maximum gambar adalah 5 MB',
        ])->validate();
        
        try {

            $imageFile = $request->file('image');
            $image     = time() . '-' . $imageFile->getClientOriginalName();

            $ids = explode(',', $request->categories);
            $categories = BlogCategory::select('id')->whereIn('id', $ids)->get()->toArray();
            if (count($categories) <= 0) return $this->returnCondition(false, 404, 'category not found');

            $categoryIds = [];
            foreach ($categories as $item) {
                array_push($categoryIds, $item['id']);
            }

            if ($request->is_carousel == 1){
                $carousel = Blog::where('is_carousel', true)->get();
                if (count($carousel) >= 3) return $this->returnCondition(false, 404, 'maximal blog carousel adalah 3');
            }
            
            $create = [
                'title' => $request->title,
                'short_title' => $request->short_title,
                'image' => $image,
                'description' => $request->description,
                'is_carousel' => $request->is_carousel,
            ];

            $blogId = Blog::create($create)->id;

            $blog = Blog::select('id')->where('id', $blogId)->first();
            $blog->categories()->attach($categoryIds);

            Storage::putFileAs('public/images/blog', $imageFile, $image);

            return $this->returnCondition(true, 200, 'Successfully created data');
        }catch(Exception $e){
            if(Storage::disk('local')->exists('public/images/blog' . $image)){
                Storage::delete('public/images/blog' . $image);
            }
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function update(BlogUpdateRequest $request, $id) {

            $updateData = [
                'title' => $request->title,
                'short_title' => $request->short_title,
                'description' => $request->description,
                'is_carousel' => $request->is_carousel,
            ];

            if($request->hasFile('photo')){

                $rules = [
                    'image' => 'mimes:jpg,png,jpeg|max:5048',
                ];

                Validator::make($request->all(), $rules, $messages = 
                [
                    'image.mimes' => 'gambar harus berupa jpg, png atau jpeg',
                    'image.max'   => 'maximum gambar adalah 5 MB',
                ])->validate();

                $imageFile      = $request->file('image');
                $image          = time() . '-' . $imageFile->getClientOriginalName();
                Storage::putFileAs('public/images/blog', $imageFile, $image);

                $updateData['image'] = $image;
            }
        
            $blog = Blog::select('id', 'title')->where('id', $id)->first();
            if(!$blog) return $this->returnCondition(false, 404, 'data not found');

            if ($request->is_carousel == 1){
                $carousel = Blog::where('is_carousel', true)->get();
                if (count($carousel) >= 3) return $this->returnCondition(false, 404, 'maximal blog carousel adalah 3');
            }

            $ids = explode(',', $request->categories);
            $categories = BlogCategory::select('id')->whereIn('id', $ids)->get()->toArray();
            if (count($categories) <= 0) return $this->returnCondition(false, 404, 'category not found');

            $categoryIds = [];
            foreach ($categories as $item) {
                array_push($categoryIds, $item['id']);
            }

            $oriImage = $blog->image;

        try {

            $blog->update($updateData);
            $blog->categories()->detach();
            $blog->categories()->attach($categoryIds);

            if($request->hasFile('image')){
                if($oriImage){
                    if(Storage::disk('local')->exists('public/images/blog' . $oriImage)){
                        Storage::delete('public/images/blog' . $oriImage);
                    }
                }
            }

            return $this->returnCondition(true, 200, 'Successfully updated data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            if($request->hasFile('image')){
                if(Storage::disk('local')->exists('public/images/blog' . $image)){
                    Storage::delete('public/images/blog' . $image);
                }
            }
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function destroy($id) {
        try {
            $blog = Blog::select('id', 'title')->where('id', $id)->first();
            if(!$blog) return $this->returnCondition(false, 404, 'data not found');

            $blogImage = $blog->image;

            $blog->categories()->detach();
            $blog->delete();

            if($blogImage){
                if(Storage::disk('local')->exists('public/images/blog' . $blogImage)){
                    Storage::delete('public/images/blog' . $blogImage);
                }
            }

            return $this->returnCondition(true, 200, 'Successfully deleted data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }
}
