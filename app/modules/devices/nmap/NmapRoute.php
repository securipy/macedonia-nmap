<?php
use App\Lib\Auth,
    App\Lib\Response,
    App\Lib\GeneralFunction,
    App\Middleware\AuthMiddleware,
    App\Validation\NmapValidation,
    App\Middleware\AuthAppMiddleware,
    App\Middleware\AuditMiddleware;



$app->group('/device/nmap', function () {

  $this->get('/list/{id_device:[0-9]+}', function ($request, $response, $args) {
    $type_petition = $request->getAttribute('type_petition');

    $scans = clone($this->controller->nmap->getScanByIdDevice($args['id_device']));
    $ip = $this->controller->nmap->getIpByDevice($args['id_device']);
    

    if($type_petition == "html"){
      return $this->view->render($response, 'modules/devices/nmap/templates/nmaplist.twig',[
      'scans' => $scans,
      'locale' => $request->getAttribute('locale'),
      'id_device' => $args['id_device'],
      'ip' => $ip
        ]);

    }else{
      return $response->withHeader('Content-type', 'application/json')
      ->write(
       json_encode(array(
          'scans' => $scans,
          'locale' => $request->getAttribute('locale'),
          'id_device' => $args['id_device'],
          'ip' => $ip
        ))
       ); 
    }
    
  })->add(new AuditMiddleware($this))->add(new AuthMiddleware($this));



  $this->get('/{id_device:[0-9]+}', function ($request, $response, $args) {
    $token = $request->getAttribute('token');
    $type_petition = $request->getAttribute('type_petition');
    $audit = $request->getAttribute('audit');
    $id= Auth::GetData($token)->id;
    
    $device = $this->controller->nmap->getDataByIdDeviceUser($id,$audit,$args['id_device']);
    if($type_petition == "html"){
      return $this->view->render($response, 'modules/devices/templates/device.twig',[
        'device' => $device
        ]);
    }else{
     return $response->withHeader('Content-type', 'application/json')
     ->write(
       json_encode($device)
       );
   }
 })->add(new AuditMiddleware($this))->add(new AuthMiddleware($this));


  $this->post('/new', function ($request, $response, $args) {
    $token = $request->getAttribute('token');
    $audit = $request->getAttribute('audit');
    $id= Auth::GetData($token)->id;
    $expected_fields = array('day_scan','id','servers');
    $data = GeneralFunction::createNullData($request->getParsedBody(),$expected_fields);
    $r = NmapValidation::newScan($data);
    if(!$r->response){
            return $response->withHeader('Content-type', 'application/json')
                       ->withStatus(422)
                       ->write(json_encode($r));
    }
    $scan = $this->controller->nmap->setScanDevices($id,$audit,$data['day_scan'],$data['id'],$data['servers']);
  
    return $response->withHeader('Content-type', 'application/json')
            ->write(
            json_encode($scan)
    ); 
    
    
 })->add(new AuditMiddleware($this))->add(new AuthMiddleware($this));






$this->get('/distributed', function ($request, $response, $args) {
    $id_server = $request->getAttribute('id_server');

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