<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PDFToSQLite extends Model
{
    use HasFactory;

    protected $table = 'p_d_f_to_s_q_lites';

    protected $guarded = [];
}
