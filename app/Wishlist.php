<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wishlist extends Model 
{
    /**
     * I prodotti di questa wishlist
     */
    public function products()
    {
        return $this->belongsToMany('App\Products');
    }

    public function getReport($filename) 
    {
        $sql = sprintf("SELECT u.email AS `user`, w.name AS `title wishlist`,
            (SELECT COUNT(pw.id) FROM products_wishlist pw 
            WHERE pw.wishlist_id = w.id) AS `number of items`
        INTO OUTFILE '%s'
            FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'
            LINES TERMINATED BY '\n'
        FROM users u
        JOIN wishlists w ON w.user_id = u.id", $filename);
        \var_dump(DB::statement($sql));

    }

}
