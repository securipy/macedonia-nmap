<?php
namespace App\Model;

use App\Lib\Response,
App\Lib\Auth;

class NmapModel extends MasterModel
{
    private $db;
    private $response;
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->response = new Response();
        parent::__construct($db,$this->response);
    }
    

    public function getScanByIdDevice($id_device)
    {
        $sql = "SELECT sw.id,sw.date_execute,sw.date_start,sw.date_finish,s.name,s.ip_domain FROM script_work as sw, servers as s, servers_scripts as ss WHERE sw.id_info_work=:id_device AND sw.id_script_server=ss.id_server_script AND ss.id_server=s.id";

       
        $st = $this->db->prepare($sql);

        $st->bindParam(':id_device',$id_device);
        $this->response->result = null;  
        if($st->execute()){
            $this->response->result = $st->fetchAll();
            return $this->response->SetResponse(true,"Get Scans");
        }else{
            return $this->response->SetResponse(false,"Error Get Scans");
        }
    }

    public function getIpByDevice($id_device)
    {
        $sql = "SELECT ip_domain FROM devices WHERE id = :id_device";
        $st = $this->db->prepare($sql);

        $st->bindParam(':id_device',$id_device);
        $this->response->result = null;  
        if($st->execute()){
            $this->response->result = $st->fetchAll();
            return $this->response->SetResponse(true,"Get Ip");
        }else{
            return $this->response->SetResponse(false,"Error Get Ip");
        }
    }

    public function getDeviceToScan($id_server)
    {
        $sql = "SELECT d.id,d.ip_domain FROM servers_scripts as ss,script_work as sw ,devices as d WHERE ss.id_server = :id_server AND ss.id_scripts=1 AND ss.id_server_script = sw.id_script_server AND sw.status=0 AND d.id = sw.id_info_work AND sw.date_execute<NOW()";
        $st = $this->db->prepare($sql);

        $st->bindParam(':id_server',$id_server);
        $this->response->result = null;  
        if($st->execute()){
            $this->response->result = $st->fetchAll();
            return $this->response->SetResponse(true,"Get Devices to Scan");
        }else{
            return $this->response->SetResponse(false,'Error Get Devices to scan');
        }
    }

    public function checkServerIdScan($id_server,$id_scan)
    {
        $sql = "SELECT COUNT(*) as permision FROM script_work WHERE id=:id_server AND id_info_work=:id_scan";

        $st = $this->db->prepare($sql);

        $st->bindParam(':id_server',$id_server);
        $st->bindParam(':id_scan',$id_scan);

        $this->response->result = null;  
        if($st->execute()){
            $this->response->result = $st->fetch();
            return $this->response->SetResponse(true,"Get Devices to Scan");
        }else{
            return $this->response->SetResponse(false,'Error Get Devices to scan');
        }
    }


    public function setFile($id_scan,$file_name)
    {
        $sql = "INSERT INTO devices_files_nmap (id_device,name) VALUES (:id_device,:file_name)";

        $st = $this->db->prepare($sql);


        $st->bindParam(':id_device',$id_scan);
        $st->bindParam(':file_name',$file_name);

        //$this->response->result = null;  
        if($st->execute()){
            $this->response->result = $this->db->lastInsertId();
            return $this->response->SetResponse(true,"Set xml nmap");
        }else{
            return $this->response->SetResponse(false,'Error Set xml nmap');
        }
    }


    public function setPortScan($data)
    {
        $sql = "INSERT INTO devices_ports (id_device,port,protocol,service,status,extra) VALUES (:id_device,:port,:protocol,:service,:status,:extra)";

        $st = $this->db->prepare($sql);


        $st->bindParam(':id_device',$data['id_scan']);
        $st->bindParam(':port',$data['port']);
        $st->bindParam(':protocol',$data['protocol']);
        $st->bindParam(':service',$data['service']);
        $st->bindParam(':status',$data['state']);
        $st->bindParam(':extra',$data['banner']);

        if($st->execute()){
            $this->response->result = $this->db->lastInsertId();
            return $this->response->SetResponse(true,"Set port");
        }else{
            return $this->response->SetResponse(false,'Error Set port');
        }    }



    public function updateStatusDeviceToScan($id_server,$status_new,$status_old)
    {

      $sql = "UPDATE servers_scripts as ss,script_work as sw SET sw.status = :status WHERE ss.id_server = :id_server AND ss.id_scripts=1 AND ss.id_server_script = sw.id_script_server AND status=:old_status AND sw.date_execute<NOW()";

      $st = $this->db->prepare($sql);

      $st->bindParam(':id_server',$id_server);
      $st->bindParam(':status',$status_new);
      $st->bindParam(':old_status',$status_old);

      $this->response->result = null;  
      if($st->execute()){
            //$this->response->result = $st->fetch();
        return $this->response->SetResponse(true,"Update Devices to Scan");
    }else{
      return $this->response->SetResponse(false,'Error Update Devices to scan');
  }
}



}


