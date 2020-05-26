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
     * @OA\Get(
     *     path="/api/v1/wishlists/{id}",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da recuperare",
     *         required=false,
     *         @OA\Schema(type="int")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Restituisce la wishlist richiesta o tutte le wishlist dell'utente",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="La wishlist non esiste, o l'id non corrisponde ad una wishlist valida",
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore",
     *     )
     * )
     *
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
                $lists = Wishlist::with('products')->where(['id' => $id, 'user_id' => Auth::user()->id])->get();
            } else {
                $lists = Wishlist::with('products')->where(['user_id' => Auth::user()->id])->get();
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
     * @OA\Post(
     *     path="/api/v1/wishlists/",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Nome da assegnare alla wishlist",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Wishlist creata con successo",
     *         @OA\JsonContent(
     *            @OA\Property(
     *              title="id",
     *              type="integer",
     *              description="Id wishlist creata"
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
            return $this->genericSuccessResponse('CREATED', ['id' => $list->id], 201);
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }

    /**
     * Modifica la wishlist
     * 
     * @OA\Put(
     *     path="/api/v1/wishlists/{id}",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da modificare",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Nome da assegnare alla wishlist",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Wishlist modificata con successo",
     *         @OA\JsonContent(
     *            @OA\Property(
     *              title="id",
     *              type="integer",
     *              description="Id prodotto modificato"
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Wishlist non trovata"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/wishlists/{id}",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da eliminare",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Wishlist cancellata con successo"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Wishlist non trovata"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/wishlists/{id}/products",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist-products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da cui estrarre i prodotti",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Elenco prodotti",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Wishlist non trovata"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
                $products = $list->products()->get();
                return $this->genericSuccessResponse('OK', ['items' => $products, 'count' => count($products)]);
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }

    /**
     * Aggiunge il prodotto $pid alla lista $wid
     *
     * @OA\Post(
     *     path="/api/v1/wishlists/{id}/products",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist-products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da cui estrarre i prodotti",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         description="Id prodotto da aggiungere",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Elenco prodotti",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="301",
     *         description="Prodotto già presente"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Wishlist o prodotto non trovati"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
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
                    return $this->genericSuccessResponse('CREATED', [], 201);
                } else {
                    return $this->genericSuccessResponse('NOT ADDED',[], 301);
                }
            }
        } catch (\Exception $e) {
            return $this->genericErrorResponse($e->getMessage());
        }
    }

    /**
     * ELimina il prodotto passato dalla lista $wid
     *
     * @OA\Delete(
     *     path="/api/v1/wishlists/{id}/products",
     *     security={
     *         {"bearer":{}}
     *     },
     *     tags={"wishlist-products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id wishlist da cui estrarre i prodotti",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         description="Id prodotto da rimuovere",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Wishlist o prodotto trovati"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Errore / Eccezione",
     *     )
     * )
     * 
     * @param Request $request
     * @param integer $wid
     * @return void
     */
    public function removeProducts(Request $request, $wid)
    {
        $pid = $request->input('pid');
        try {
            $list = Wishlist::with('products')->find($wid);
            if (!$list) {
                return $this->genericErrorResponse('NOT FOUND', ['message' => 'Wishlist not found'], 404);
            } else {
                if ($list->user_id != Auth::user()->id) {
                    return $this->genericErrorResponse('UNAUTHORIZED', [], 401);
                }

                // se non è specificato un id, li rimuovo tutti
                if (!$pid) {
                    $list->products()->detach();
                    $list->save();
                    return $this->genericSuccessResponse('DELETED_ALL', [], 204);                    
                }

                // altrimenti cerco ed elimino il prodotto
                $product = Products::find($pid);
                if (!$product) {
                    return $this->genericErrorResponse('NOT FOUND', ['message' => 'Product not found'], 404);
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