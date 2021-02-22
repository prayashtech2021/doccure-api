<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MultiLanguage extends Model
{
    public $table = 'multi_languages';
    protected $fillable = [
        'page_master_id', 'language_id','keyword','value','created_by',
   ];
}
