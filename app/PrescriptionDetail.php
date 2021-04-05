<?php

namespace App;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionDetail extends Model
{
    use SoftDeletes;
    use EncryptedAttribute;

    protected $fillable = [
        'prescription_id', 'drug_name', 'quantity', 'type', 'days', 'time', 'created_by',
   ];
    protected $encryptable = [
    'drug_name',
    ];
}
