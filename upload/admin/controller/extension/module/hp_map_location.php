<?php

class ControllerExtensionModuleHpMapLocation extends Controller
{

	private $version = '1.0.0';

	private $error = array();

	public function index() {
		$this->load->language('extension/module/hp_map_location');

		$this->install();
		
		$this->document->setTitle($this->language->get('heading_title2'));
		$data['heading_title'] = $this->language->get('heading_title2');

		//Load additional CSS/JS
		$this->document->addScript('view/javascript/bootstrap-toggle/js/bootstrap-toggle.min.js');
		$this->document->addStyle('view/javascript/bootstrap-toggle/css/bootstrap-toggle.min.css');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$post = $this->request->post;
			foreach ($post as $key => $value) {
				$data['module_hp_map_location_' . $key] = $value;
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$this->model_setting_setting->editSetting('module_hp_map_location', $data);
			$this->response->redirect($this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['version'] = $this->version;

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
			'href' => $this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true)
		);

		$inputs = [
			[
				"name" => "status",
				"default" => 0,
			],
			[
				"name" => "api",
				"default" => '',
			],
		];

		foreach ($inputs as $input) {
			$key = "module_hp_map_location_" . $input['name'];

			if (isset($this->request->post[$key])) {
				$data[$input['name']] = $this->request->post[$key];
			} else if ($this->config->get($key)) {
				$data[$input['name']] = $this->config->get($key);
			} else {
				$data[$input['name']] = $input['default'];
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hp_map_location', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/hp_map_location')) {
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
