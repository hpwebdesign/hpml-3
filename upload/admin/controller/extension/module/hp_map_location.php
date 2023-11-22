<?php

class ControllerExtensionModuleHpMapLocation extends Controller
{

	private $version        = '1.0.0.4';
	private $v_d            = '';
	private $extension_code = 'hpml';
	private $error 			= [];
	private $domain 		= '';

	public function index(){
		$this->domain	 = str_replace("www.","",$_SERVER['SERVER_NAME']);

		$this->houseKeeping();

		$this->load->language('extension/module/hp_map_location');

		$this->rightman();

		if ($this->domain != $this->v_d) {
			$this->storeAuth();
		} else {
			$this->setting();
		}
	}

	private function setting() {
		$data['version'] = $this->version;
		$data['extension_code'] = $this->extension_code;

		$this->installTable();

		$this->document->setTitle($this->language->get('heading_title2'));
		$data['heading_title'] = $this->language->get('heading_title2');

		//Load additional CSS/JS
		$this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-checkbox/1.5.0/js/bootstrap-checkbox.min.js');

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
				"name" => "compulsory",
				"default" => 0,
			],
			[
				"name" => "api",
				"default" => '',
			],
			[
				"name" => "force_map",
				"default" => 0,
			],
		];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		foreach ($data['languages'] as $language) {
			$inputs[] = [
				"name" => "share_location_template_" . $language['language_id'],
				"default" => $this->language->get('placeholder_share_location_template'),
			];
			$inputs[] = [
				"name" => "error_message_" . $language['language_id'],
				"default" => $this->language->get('placeholder_error_message'),
			];
		}

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

		$data['user_token'] = $this->session->data['user_token'];

		$this->response->setOutput($this->load->view('extension/module/hp_map_location', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/hp_map_location')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function installTable()
	{
		$query1 = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'map_location_lat'");
		$query2 = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "address` LIKE 'map_location_lat'");
		$query3 = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "address` LIKE 'map_location_lng'");
		$query4 = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE 'map_location_lng'");
		$sqls = [];

		if(!$query1->num_rows){
			$sqls[] = "ALTER TABLE " . DB_PREFIX . "order ADD map_location_lat VARCHAR(50)";
		}

		if(!$query2->num_rows){
			$sqls[] = "ALTER TABLE " . DB_PREFIX . "address ADD map_location_lat VARCHAR(50)";
		}

		if(!$query3->num_rows){
			$sqls[] = "ALTER TABLE " . DB_PREFIX . "address ADD map_location_lng VARCHAR(50)";
		}

		if(!$query4->num_rows){
			$sqls[] = "ALTER TABLE " . DB_PREFIX . "order ADD map_location_lng VARCHAR(50)";
		}

		$error = 0;

		foreach ($sqls as $sql) {

			if (!$this->db->query($sql)) {
				$error++;
			}
			sleep(0.2);
		}
	}











	private function internetAccess()
	{
		return true;
	}

	public function curlcheck()
	{
		return in_array('curl', get_loaded_extensions()) ? true : false;
	}



	public function storeAuth() {
		$data['curl_status'] = $this->curlcheck();
		$data['extension_code'] = $this->extension_code;
		$data['user_token'] = $this->session->data['user_token'];
		$this->flushdata();

		$this->document->setTitle($this->language->get('text_validation'));

		$data['text_curl']                  = $this->language->get('text_curl');
		$data['text_disabled_curl']         = $this->language->get('text_disabled_curl');

		$data['text_validation']            = $this->language->get('text_validation');
		$data['text_validate_store']        = $this->language->get('text_validate_store');
		$data['text_information_provide']   = $this->language->get('text_information_provide');
		$data['domain_name'] = str_replace("www.","",$_SERVER['SERVER_NAME']);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title2'),
			'href'      => $this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/validation', $data));
	}

	private function rightman() {
		if($this->internetAccess()) {
			$this->load->model('extension/module/system_startup');

			$license = $this->model_extension_module_system_startup->checkLicenseKey($this->extension_code);

			if ($license) {
					if (isset($this->model_extension_module_system_startup->licensewalker)) {
						$url = $this->model_extension_module_system_startup->licensewalker($license['license_key'],$this->extension_code,$this->domain);
						$data = $url;
						$domain = isset($data['domain']) ? $data['domain'] : '';

						if($domain == $this->domain) {
							$this->v_d = $domain;
						} else {
							$this->flushdata();
						}
					 }
				}

		} else {
			$this->error['warning'] = $this->language->get('error_no_internet_access');
		}
	}

	private function houseKeeping() {
		$file = 'https://api.hpwebdesign.io/validate.zip';
		$newfile = DIR_APPLICATION.'validate.zip';

		if (!file_exists(DIR_APPLICATION.'controller/common/hp_validate.php') || !file_exists(DIR_APPLICATION.'model/extension/module/system_startup.php') || !file_exists(DIR_APPLICATION.'view/template/extension/module/validation.twig')) {
			$file = $this->curl_get_file_contents($file);
			if (file_put_contents($file, $newfile)) {
			$zip = new ZipArchive();
			$res = $zip->open($newfile);
				if ($res === TRUE) {
				  $zip->extractTo(DIR_APPLICATION);
				  $zip->close();
				  unlink($newfile);
				}
			}
		}

		$this->load->model('extension/module/system_startup');
		if (!isset($this->model_extension_module_system_startup->checkLicenseKey) || !isset($this->model_extension_module_system_startup->licensewalker)) {
			$file = $this->curl_get_file_contents($file);
			if (file_put_contents($file, $newfile)) {
			$zip = new ZipArchive();
			$res = $zip->open($newfile);
				if ($res === TRUE) {
				  $zip->extractTo(DIR_APPLICATION);
				  $zip->close();
				  unlink($newfile);
				}
			}
		}

		if(!file_exists(DIR_SYSTEM.'system.ocmod.xml')) {
			$str = $this->curl_get_file_contents('https://api.hpwebdesign.io/system.ocmod.txt');

			file_put_contents(dirname(getcwd()) . '/system/system.ocmod.xml', $str);
		}
		$sql = "CREATE TABLE IF NOT EXISTS `hpwd_license`(
						`hpwd_license_id` INT(11) NOT NULL AUTO_INCREMENT,
						`license_key` VARCHAR(64) NOT NULL,
						`code` VARCHAR(32) NOT NULL,
						`support_expiry` date DEFAULT NULL,
						 PRIMARY KEY(`hpwd_license_id`)
					) ENGINE = InnoDB;";
		 $this->db->query($sql);
	}

	public function install() {
		$this->houseKeeping();
	}

	public function flushdata() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` LIKE '%module_hp_map_location_status%'");
	}

	private function curl_get_file_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) return $contents;
		else return FALSE;
	}
}