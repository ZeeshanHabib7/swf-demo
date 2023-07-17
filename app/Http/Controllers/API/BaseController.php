<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    private $result;

    public function successResponse($result = [], $message, $paginate = FALSE, $code = 200)
    {

        $this->result = $result;

        if ($paginate == TRUE) {
            $this->paginate($result);
        }

        $response = [
            'success' => true,
            'status_code'    => $code,
            'message' => [$message],
            // 'message' =>$message,
            'data'   => $this->result
        ];
        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function errorResponse($error, $code = 400, $errorMessages = [])
    {

        $statusCode = $code == 0 ? 400 : $code;
        $response = [
            'success' => false,
            'status_code' => $statusCode,
            'message' => is_array($error) == TRUE ? $error : [$error],
            'data'    => []
        ];

        return response()->json($response, $statusCode);
    }
}
