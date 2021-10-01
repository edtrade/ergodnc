<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\Office\ImageStoreRequest;
use App\Models\Office;
use App\Models\Image;
use App\Http\Resources\ImageResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class OfficeImageController extends Controller
{
    //

    public function store(ImageStoreRequest $request, Office $office): JsonResource
    {
        $path = $request->file('image')->storePublicly('/');

        $image = $office->images()->create([
            'path'=>$path
        ]);

        return ImageResource::make($image);
    }

    //
    public function delete(Office $office, Image $image)
    {
        abort_unless(auth()->user()->tokenCan('office.delete'),
            Response::HTTP_FORBIDDEN
        );

        $this->authorize('delete', $office);

        throw_if($image->resource_type != 'office' ,//|| $image->resource_id != $office->id
            ValidationException::withMessages(["image" => "Cannot delete this image"]));

        throw_if($office->images()->count()==1,
            ValidationException::withMessages(["image" => "Cannot delete the only image"]));

        Storage::delete($image->path);

        $image->delete();        
    }
}
