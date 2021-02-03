<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    

    public function getData(){
        return [
            'id' => $this->id,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,
            'ifsc_code' => $this->ifsc_code,
        ];
    }
}
