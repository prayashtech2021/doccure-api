<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Storage;
use URL;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','button_name','link','image', 'created_by',
    ];
    public function getData(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'button_name' => $this->button_name,
            'link' => $this->link,
            'image' => $this->getImage(),
            'deleted_at' => $this->deleted_at
        ];
    }

    function getImage(){
        $image = $this->image;
        if (!empty($image) && Storage::exists('images/cms-images/' . $image)) {
            $path = (config('filesystems.default') == 's3') ? Storage::temporaryUrl('images/cms-images/' . $image, now()->addMinutes(5)) : url('storage/images/cms-images/' . $image);
        } elseif(!empty($image)){
            $path = url('img/cms-images/' . $image);
        }else{
            $path = url('img/logo.png');
        }
        return $path;
    }
}
