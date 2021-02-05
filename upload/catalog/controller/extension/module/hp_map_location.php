<?php

class ControllerExtensionModuleHpMapLocation extends Controller
{
	
	public function editMapLocation(){
		$this->load->model('account/address');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			$this->model_account_address->editMapLocation($this->request->post['address_id'], $this->request->post);
            $this->response->addHeader('Content-Type: application/json');
		    $this->response->setOutput(json_encode(['success'=>true]));
		}

	}

}
