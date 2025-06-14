<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'value', 'file_path', 'module_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
