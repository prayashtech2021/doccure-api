<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Storage;
use URL;

class Feature extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'image', 'created_by',
    ];
    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->getFeatureImage(),
            'deleted_at' => $this->deleted_at
        ];
    }

    function getFeatureImage(){
        $image = $this->image;
        if (!empty($image) && Storage::exists('images/features/' . $image)) {
            $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('images/features/' . $image, now()->addMinutes(5)) : url('storage/images/features/' . $image);
        }else{
            $path = url('img/logo.png');
        }
        return $path;
    }
}
