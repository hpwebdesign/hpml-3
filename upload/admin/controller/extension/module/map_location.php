<?php

class ControllerExtensionModuleMapLocation extends Controller
{

	private $error = array();

	public function index() {
		$this->load->language('extension/module/map_location');

		$this->install();
		
		$this->document->setTitle($this->language->get('heading_title2'));
		$data['heading_title'] = $this->language->get('heading_title2');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_map_location', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/map_location', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/map_location', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_map_location_status'])) {
			$data['module_map_location_status'] = $this->request->post['module_map_location_status'];
		} else {
			$data['module_map_location_status'] = $this->config->get('module_map_location_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/map_location', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/map_location')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install()
	{
		$sqls[] = "ALTER TABLE " . DB_PREFIX . "address ADD COLUMN IF NOT EXISTS `map_location_lat` VARCHAR(50)";
		$sqls[] = "ALTER TABLE " . DB_PREFIX . "address ADD COLUMN IF NOT EXISTS `map_location_lng` VARCHAR(50)";
		$sqls[] = "ALTER TABLE " . DB_PREFIX . "order ADD COLUMN IF NOT EXISTS `map_location_lat` VARCHAR(50)";
		$sqls[] = "ALTER TABLE " . DB_PREFIX . "order ADD COLUMN IF NOT EXISTS `map_location_lng` VARCHAR(50)";
		
		$error = 0;

		foreach ($sqls as $sql) {

			if (!$this->db->query($sql)) {
				$error++;
			}
			sleep(0.2);
		}
	}


}
