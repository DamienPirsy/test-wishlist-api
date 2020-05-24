<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Products;
use Illuminate\Http\Request;
use App\Wishlist;
use Illuminate\Support\Str; // carico l'helper stringhe
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{

    /**
     * Restituisce una o tutte le wishlist dell'utente loggato
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function fetch(Request $request, $id = null) 
    {
        try {
            if ($id) {
                $lists = Wishlist::where(['id' => $id, 'user_id' => Auth::user()->id])->get();
            } else {
                $lists = Wishlist::where(['user_id' => Auth::user()->id])->get();
            }
            // ^_ ->get() restituisce una Collection

            if ($lists->isEmpty()) {
                return response()->json(['message' => 'Wishlist not found'], 404);
            }
            return response()->json(['wishlists' => $lists, 'message' => 'OK'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function add(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string'
        ]);

        try {
            $list = new Wishlist;
            $list->name = $request->input('name');
            $list->alias = Str::of($request->input('name'))->slug('-'); // giusto per completezza
            $list->user_id = Auth::user()->id;
            $list->save();
            return response()->json(['id' => $list->id, 'message' => 'OK'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Modifica la wishlist
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function edit(Request $request, $id) {

        $this->validate($request, [
            'name' => 'required|string'
        ]);
        try {
            $list = Wishlist::find($id);
            if (null === $list) {
                return response()->json(['message' => 'Wishlist not found'], 404);
            }

            $list->name = $request->input('name');
            $list->alias = Str::of($request->input('name'))->slug('-'); // giusto per completezza
            $list->save();
            return response()->json(['message' => 'Wishlist succesfully updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }        
    }

    /**
     * Deletes by index key
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function remove(Request $request, $id) {
        try {
            // Solo l'utente loggato può eliminare una sua wishlist
            if (Wishlist::where(['id' => $id, 'user_id' => Auth::user()->id])->delete()) {
                return response()->json(['message' => 'OK'], 200);
            } else {
                // Generico not found, anche se potrei distinguere un "Not authorized" se l'utente
                // non corrisponde
                return response()->json(['message' => 'Wishlist not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
     * Recupera tutti i prodotti relativi all wishlist indicata
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function fetchProducts(Request $request, $wid) 
    {
        try {
            $list = Wishlist::with('products')->where(['id' => $wid, 'user_id' => Auth::user()->id])->get();
            if ($list->isEmpty()) {
                return response()->json(['message' => 'Wishlist not found'], 404);                
            } else {
                return response()->json(['data' => $list], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Aggiunge il prodotto $pid alla lista $wid
     *
     * @param Request $request
     * @param int $wid - wishlist id
     * @param int $pid - product id
     * @return Response
     */
    public function addProducts(Request $request, $wid, $pid) 
    {
        try {
            $list = Wishlist::with('products')->where(['id' => $wid, 'user_id' => Auth::user()->id])->get();
            $product = Products::where(['id' => $pid])->get();
            if ($list->isEmpty()) {
                return response()->json(['message' => 'Wishlist not found'], 404);
            } elseif ($product->isEmpty()) {
                return response()->json(['message' => 'Product not found'], 404);
            } else {
                $w = $list->first();
                if (null == $w->products()->find($pid)) {
                    $w->products()->attach($pid);
                    $w->save();
                    return response()->json(['message' => 'Product added'], 200);
                } else {
                    return response()->json(['message' => 'Product already present'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * ELimina il prodotto $pid dalla lista $wid
     *
     * @param Request $request
     * @param [type] $wid
     * @param [type] $pid
     * @return void
     */
    public function removeProducts(Request $request, $wid, $pid)
    {
        try {
            $list = Wishlist::with('products')->where(['id' => $wid, 'user_id' => Auth::user()->id])->get();
            $product = Products::where(['id' => $pid])->get();
            if ($list->isEmpty()) {
                return response()->json(['message' => 'Wishlist not found'], 404);
            } elseif ($product->isEmpty()) {
                return response()->json(['message' => 'Product not found'], 404);
            } else {
                $w = $list->first();
                if (null !== $w->products()->find($pid)) {
                    $w->products()->detach($pid);
                    $w->save();
                    return response()->json(['message' => 'Product removed'], 200);
                } else {
                    return response()->json(['message' => 'Product not present'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}