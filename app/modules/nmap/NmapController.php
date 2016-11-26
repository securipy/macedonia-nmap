<?php
namespace App\Controller;

use App\Lib\Response,
App\Lib\Auth;


class NmapController extends MasterController
{
	private $response;

	public function __construct($model,$connection)
	{
		$this->response = new Response();
		$this->model = $model;
		parent::__construct($connection,$this->response);
	}


	public function setPortScan($id_server,$data)
	{
		return $this->model->setPortScan($data);
	}

	public function getDeviceToScan($id_server)
	{
		$result_data_scan = $this->model->getDeviceToScan($id_server);
		if($result_data_scan->response == true && !empty($result_data_scan->result)){
			$result_tmp = $result_data_scan->result;
			$result_update = $this->model->updateStatusDeviceToScan($id_server,1,0);
			if($result_update->response == true){
				$this->response->result=$result_tmp;
				return $this->response->SetResponse(True,"Data to scan");
			}else{
				return $result_update;
			}
		}else{
			return $result_data_scan;
		}
	}

	public function uploadScan($id_server,$id_scan,$file)
	{
		$check_server_scan = $this->model->checkServerIdScan($id_server,$id_scan);
			if($check_server_scan->response=True && $check_server_scan->result != 0){
			$upload = $this->uploadFile($file,'nmap');
			if($upload->response = True){
				$result_set_file = $this->model->setFile($id_scan,$upload->result);
				if($result_set_file->response = true){
					$result_update_status = $this->model->updateStatusDeviceToScan($id_server,2,1);
					return $result_update_status;
				}else{
					return $result_set_file;
				}
			}else{
				return $upload;
			}
		}else{
			return $check_server_scan;
		}
	}



}