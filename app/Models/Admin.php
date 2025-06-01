<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Admin extends User
{
    use HasApiTokens, HasFactory,Notifiable;
    protected $table = 'admin';
    public $timestamps = false;
    protected $appends = ['role_name'];

    public function getRoleNameAttribute(){
        return $this->hasOne(AdminRole::class,'id','role_id')->value('name');
    }


}
