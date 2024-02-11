<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Illuminate\Http\Request;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Http\Requests\RegulationRequest;
use App\Http\Resources\Regulation\RegulationCollection;
use App\Http\Resources\Regulation\RegulationResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RegulationController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api')->except(['index', 'show']);
    }
    
    public function index(Request $request) {
        $regulations = Regulation::select('id', 'title', 'short_title', 'image', 'description', 'created_at', 'updated_at')
                        ->when($request->search, function($q){
                            $q->where('title', 'like', '%'.$request->search.'%');
                            $q->orWhere('short_title', 'like', '%'.$request->search.'%');
                        })
                        ->orderBy('id', 'desc')
                        ->paginate(10);

        return new RegulationCollection($regulations);
    }

    public function show($id){
        $Regulation = Regulation::select('id', 'title', 'short_title', 'image', 'description', 'created_at', 'updated_at')
                        ->where('id', $id)->first();
        if(!$Regulation) return $this->returnCondition(false, 404, 'data not found');

        return new RegulationResource($Regulation);
    }

    public function store(RegulationRequest $request) {
        
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

            $imageFile      = $request->file('image');
            $image          = time() . '-' . $imageFile->getClientOriginalName();

            $ids = explode(',', $request->categories);
            $categories = RegulationCategory::select('id')->whereIn('id',$ids)->get()->toArray();
            if (count($categories) <= 0) return $this->returnCondition(false, 404, 'category not found');

            $categoryIds = [];
            foreach ($categories as $item) {
                array_push($categoryIds, $item['id']);
            }
            
            $create = [
                'title' => $request->title,
                'short_title' => $request->short_title,
                'image' => $image,
                'description' => $request->description,
            ];

            $RegulationId = Regulation::create($create)->id;

            $Regulation = Regulation::select('id')->where('id', $RegulationId)->first();
            $Regulation->categories()->attach($categoryIds);

            Storage::putFileAs('public/images/regulation', $imageFile, $image);

            return $this->returnCondition(true, 200, 'Successfully created data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            if(Storage::disk('local')->exists('public/images/Regulation' . $image)){
                Storage::delete('public/images/regulation' . $image);
            }
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function update($id, RegulationRequest $request) {

            $updateData = [
                'title' => $request->title,
                'short_title' => $request->short_title,
                'description' => $request->description,
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
                Storage::putFileAs('public/images/regulation', $imageFile, $image);

                $updateData['image'] = $image;
            }

            $Regulation = Regulation::select('id', 'title')->where('id', $id)->first();
            if(!$Regulation) return $this->returnCondition(false, 404, 'data not found');

            $ids = explode(',', $request->categories);
            $categories = RegulationCategory::select('id')->whereIn('id',$ids)->get()->toArray();
            if (count($categories) <= 0) return $this->returnCondition(false, 404, 'category not found');

            $categoryIds = [];
            foreach ($categories as $item) {
                array_push($categoryIds, $item['id']);
            }

            $oriImage = $Regulation->image;

        try {

            $Regulation->update($updateData);
            $Regulation->categories()->detach();
            $Regulation->categories()->attach($categoryIds);

            if($request->hasFile('image')){
                if($oriImage){
                    if(Storage::disk('local')->exists('public/images/regulation' . $oriImage)){
                        Storage::delete('public/images/regulation' . $oriImage);
                    }
                }
            }

            return $this->returnCondition(true, 200, 'Successfully updated data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            if($request->hasFile('image')){
                if(Storage::disk('local')->exists('public/images/regulation' . $image)){
                    Storage::delete('public/images/regulation' . $image);
                }
            }
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }

    public function destroy($id) {
        try {
            $Regulation = Regulation::select('id', 'title')->where('id', $id)->first();
            if(!$Regulation) return $this->returnCondition(false, 404, 'data not found');

            $RegulationImage = $Regulation->image;

            $Regulation->categories()->detach();
            $Regulation->delete();

            if($RegulationImage){
                if(Storage::disk('local')->exists('public/images/regulation' . $RegulationImage)){
                    Storage::delete('public/images/regulation' . $RegulationImage);
                }
            }

            return $this->returnCondition(true, 200, 'Successfully deleted data');
        }catch(Exception $e){
            Log::error($e->getMessage());
            return $this->returnCondition(false,500, 'Internal server error');
        }
    }
}
