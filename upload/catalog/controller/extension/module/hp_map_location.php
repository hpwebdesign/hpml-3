<?php

class ControllerExtensionModuleHpMapLocation extends Controller {

	public function editMapLocation() {
		$this->load->model('account/address');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if($this->customer->isLogged() && !empty($this->request->post['address_id'])){
				$this->model_account_address->editMapLocation($this->request->post['address_id'], $this->request->post);
			}

            $this->session->data['shipping_address']['map_location_lat'] = $this->request->post['map_location_lat'];
            $this->session->data['shipping_address']['map_location_lng'] = $this->request->post['map_location_lng'];

            $this->session->data['shipping_address_hpwd']['map_location_lat'] = $this->request->post['map_location_lat'];
            $this->session->data['shipping_address_hpwd']['map_location_lng'] = $this->request->post['map_location_lng'];
			

			if($this->config->get('module_marketplace_status')) {
				$this->session->data['lat'] = $this->request->post['map_location_lat'];
				$this->session->data['lng'] = $this->request->post['map_location_lng'];
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([
				'status' => true,
				'redirect' => $this->request->post['redirect'] ?? ''
			]));
		}
	}

	public function modalEditMapLocation() {

		if(!$this->config->get('module_hp_map_location_status')){
			return '';
		}

		$data = array();

		$route = $this->request->get['route'] ?? '';

		

		if ($route == 'checkout/cart' || $route == 'checkout/checkout') {

			if($route == 'checkout/cart' && !$this->cart->hasProducts()){
				return '';
			}

			$this->load->language('extension/module/hp_map_location');

			$map_location_lng = 0;

			$map_location_lat = 0;

			
			$map_location_lat = $this->session->data['shipping_address_hpwd']['map_location_lat'] ?? 0;

			$map_location_lng = $this->session->data['shipping_address_hpwd']['map_location_lng'] ?? 0;

			$checkout_page = $route == 'checkout/checkout';

			$cart_page = $route == 'checkout/cart';

			if($checkout_page || $cart_page){
				$data['have_coordinate'] = $map_location_lat && $map_location_lng;

				if(!$data['have_coordinate']){
					$data['module_id'] = rand(1, 100000);
	
					$data['cart_page'] =  $route == 'checkout/cart';
	
					$data['module_hp_map_location_api'] = $this->config->get('module_hp_map_location_api');
	
					$data['address_id'] = $address_id ?? 0;
	
					return $this->load->view('extension/module/modal_edit_map_location', $data);
				}
			}

		

		}
	}
}
