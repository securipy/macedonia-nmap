<?php
namespace App\Validation;

use App\Lib\Response;

class NmapValidation {

    public static function Validate($data) {
        $response = new Response();
        
        
        $key = 'id_scan';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }


        $response->setResponse(count($response->errors) === 0);


        return $response;
    }


    public static function newScan($data){
        $response = new Response();
        
      
        $key = 'day_scan';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }
        $data[$key] = $data[$key].":00";
        $result = masterValidation::validateDate($data[$key]);
        if(!$result){
          $response->errors[$key][] = 'Formato de la fecha no valida';
        }
        $key = 'id';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }
        $key = 'servers';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }
        
        $response->setResponse(count($response->errors) === 0);
        return $response;
    }




    public static function ValidatePort($data)
    {
    	$response = new Response();
        
        
        $key = 'id_scan';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }

 		$key = 'port';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }

 		$key = 'state';
        if(empty($data[$key])) {
            $response->errors[$key][] = 'Este campo es obligatorio';
        }


        $response->setResponse(count($response->errors) === 0);


        return $response;
    }



}