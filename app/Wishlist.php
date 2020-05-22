<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model 
{
    /**
     * I prodotti di questa wishlist
     */
    public function products()
    {
        return $this->belongsToMany('App\Products');
    }

}
