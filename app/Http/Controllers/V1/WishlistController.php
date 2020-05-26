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
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            }
            return $this->genericSuccessResponse('OK', ['items' => $lists, 'count' => count($lists)]);
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
            return $this->genericSuccessResponse('CREATED', ['id' => $list->id]);
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
            if (!$list) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            } else {
                if ($list->user_id != Auth::user()->id) {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }
                $list->name = $request->input('name');
                $list->alias = Str::of($request->input('name'))->slug('-'); // giusto per completezza
                $list->save();
                return $this->genericSuccessResponse('UPDATED', ['id' => $list->id]);
            }            
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
            if ($list = Wishlist::find($id)) {
                if ($list->user_id == Auth::user()->id) {
                    $list->delete();
                    return $this->genericSuccessResponse('DELETED', [], 204);
                } else {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }
            } else {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
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
            $list = Wishlist::find($wid);
            if (!$list) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);          
            } else {
                if ($list->user_id != Auth::user()->id) {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }
                $products = $list->products();
                return $this->genericSuccessResponse('OK', ['items' => $products, 'count' => count($products)]);
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }

    /**
     * Aggiunge il prodotto $pid alla lista $wid
     *
     * @param Request $request
     * @param int $wid - wishlist id
     * @return Response
     */
    public function addProducts(Request $request, $wid) 
    {
        $this->validate($request, [
            'pid' => 'required|numeric'
        ]);
        try {
            $list = Wishlist::with('products')->find($wid);
            $product = Products::find($request->input('pid'));

            if (!$list) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            } elseif (!$product) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
            } else {
                if ($list->user_id != Auth::user()->id) {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }
                if (!$list->products()->find($product->id)) {
                    $pw = $list->products()->attach($product->id);
                    $list->save();
                    return $this->genericSuccessResponse('CREATED');
                } else {
                    return $this->genericSuccessResponse('NOT ADDED',[], 301);
                }
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }

    /**
     * ELimina il prodotto $pid dalla lista $wid
     *
     * @param Request $request
     * @param [type] $wid
     * @return void
     */
    public function removeProducts(Request $request, $wid)
    {
        $this->validate($request, [
            'pid' => 'required|numeric'
        ]);

        try {
            $list = Wishlist::with('products')->find($wid);
            $product = Products::find($request->input('pid'));

            if (!$list) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            } elseif (!$product) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
            } else {
                if ($list->user_id != Auth::user()->id) {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }
                if ($list->products()->find($product->id)) {
                    $list->products()->detach($product->id);
                    $list->save();
                    return $this->genericSuccessResponse('DELETED', [], 204);
                } else {
                    return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
                }
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }
}