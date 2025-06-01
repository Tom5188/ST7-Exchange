<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Levertolegal extends Model
{
    protected $table = 'lever_tolegal';
    public $timestamps = false;

    public function getQuotesAttribute(){
        return unserialize($this->attributes['quotes']);
    }

}
