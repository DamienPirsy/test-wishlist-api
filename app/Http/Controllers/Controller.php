<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    /**
     * @OA\Info(title="Wishlist API", version="1.0.0")
     * @OAS\SecurityScheme(
     *  securityScheme="bearerAuth",
     *  type="http",
     *  scheme="bearer",
     *  bearerFormat: "JWT",
     *  in="header",
     *  name="bearer"
     * )
     * 
     */


    /**
     * Generico per risposte positive (200, ecc.)
     *
     * @param string $message
     * @param integer $status
     * @param array $options
     * @return Response
     */
    protected function genericSuccessResponse($message, $options = [], $status = 200)
    {
        $basic = ['message' => $message];
        return response()->json(array_merge($basic, $options), $status);
    }

    /**
     * Generico costruttore per la risposta d'errore (500-404-ecc.)
     *
     * @param string $message messaggio d'errore
     * @param integer $status
     * @param array $options
     * @return Response
     */
    protected function genericErrorResponse($message, $options = [], $status = 500)
    {
        $basic = ['error' => $message];
        return response()->json(array_merge($basic, $options),$status);
    }
    
    /**
     * 
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }
}
