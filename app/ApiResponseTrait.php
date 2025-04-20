<?php
namespace App;

trait ApiResponseTrait{
    public function sendResponse($result, $message){
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessage=[], $code='404'){
        $response = [
            'success' => false,
            'data' => $error,
        ];

        if(!empty($errorMessage)){
            $reponse['data'] = $errorMessage;
        }

        return response()->json($response, $code);
    }
}
?>