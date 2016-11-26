<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\GeneralFunction,
    App\Middleware\AuthMiddleware,
    App\Validation\NmapValidation,
    App\Middleware\AuthAppMiddleware;


$app->group('/nmap', function () {

$this->get('/distributed', function ($request, $response, $args) {
    $id_server = $request->getAttribute('id_server');
    //$id_server = 37;

    $get_scan_device = $this->controller->nmap->getDeviceToScan($id_server);


    return $response->withHeader('Content-type', 'application/json')
                    ->write(
                    json_encode($get_scan_device)
    );

    
})->add(new AuthAppMiddleware($this));

$this->post('/scan', function ($request, $response, $args) {

    $files = $request->getUploadedFiles();

    $id_server = $request->getAttribute('id_server');
    $expected_fields = array('id_scan');

    //error_log(var_dump($request->getParsedBody()));
    $data = GeneralFunction::createNullData($request->getParsedBody(),$expected_fields);
 
    $r = NmapValidation::Validate($data);

    if(!$r->response){
      return $response->withHeader('Content-type', 'application/json')
      ->withStatus(422)
      ->write(json_encode($r));
    }

    return $response->withHeader('Content-type', 'application/json')
    ->write(
     json_encode($this->controller->nmap->uploadScan($id_server,$data['id_scan'],$files))
     ); 

    
})->add(new AuthAppMiddleware($this));


$this->post('/port', function ($request, $response, $args) {


    $id_server = $request->getAttribute('id_server');
    $expected_fields = array('id_scan','port','protocol','state','service','banner');


    $data = GeneralFunction::createNullData($request->getParsedBody(),$expected_fields);
 
    
    $r = NmapValidation::ValidatePort($data);

    if(!$r->response){
      return $response->withHeader('Content-type', 'application/json')
      ->withStatus(422)
      ->write(json_encode($r));
    }

    return $response->withHeader('Content-type', 'application/json')
    ->write(
     json_encode($this->controller->nmap->setPortScan($id_server,$data))
     ); 

    
})->add(new AuthAppMiddleware($this));




});