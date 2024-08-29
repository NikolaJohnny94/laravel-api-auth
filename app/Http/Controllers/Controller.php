<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Task API",
 *     version="1.0.0"
 * )
 * 
 *  @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 */
abstract class Controller
{
    //
}
