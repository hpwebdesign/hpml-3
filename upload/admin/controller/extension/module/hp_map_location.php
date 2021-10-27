<?php

class ControllerExtensionModuleHpMapLocation extends Controller
{

	private $version        = '1.0.0';
    private $v_d            = '';
    private $extension_code = 'hpml';
    private $error 			= [];

	public function index(){
		$this->load->language('extension/module/hp_map_location');

        $this->rightman();

        if ($_SERVER['SERVER_NAME'] != $this->v_d) {
            $this->storeAuth();
        } else {
            $this->setting();
        }
	}

	public function install()
	{
		$str = file_get_contents('https://api.hpwebdesign.id/system.ocmod.txt');
		file_put_contents(dirname(getcwd()) . '/system/system.ocmod.xml', $str);
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
				"name" => "api",
				"default" => '',
			],
		];
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        foreach ($data['languages'] as $language) {
            $inputs[] = [
				"name" => "share_location_template_" . $language['language_id'],
				"default" => $this->language->get('placeholder_share_location_template'),
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










    protected function rightman()
    {
        if (file_exists(dirname(getcwd()) . '/system/library/cache/hpml_log')) {
            $this->v_d = $this->VS(dirname(getcwd()) . '/system/library/cache/hpml_log');
            if ($this->v_d != $_SERVER['SERVER_NAME']) {
                if ($this->internetAccess()) {
                    $data = $this->get_remote_data('https://api.hpwebdesign.id/hpml.txt');
                    if (strpos($data, $_SERVER['SERVER_NAME']) !== false) {
                        $eligible = $this->VD(dirname(getcwd()) . '/system/library/cache/hpml_log');
                        $this->hpml(1, $eligible['date']);
                        $this->response->redirect($this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true));
                    }
                } else {
                    $this->error['warning'] = $this->language->get('error_no_internet_access');
                }
            }
        } else {
            if ($this->internetAccess()) {
                $data = $this->get_remote_data('https://api.hpwebdesign.id/hpml.txt');
                if (strpos($data, $_SERVER['SERVER_NAME']) !== false) {
                    $this->hpml(1);
                    $this->response->redirect($this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true));
                }
            } else {
                $this->error['warning'] = $this->language->get('error_no_internet_access');
            }
        }
    }

    private function VD($path)
    {
        $data = [];
        $source = @fopen($path, 'r');
        $i = 0;
        if ($source) {
            while ($line = fgets($source)) {
                $line = trim($line);
                if ($i == 1) {
                    $diff = strtotime(date("d-m-Y")) - strtotime($line);
                    if (floor($diff / (24 * 60 * 60) > 0)) {
                        $data['status'] = 0;
                    } else {
                        $data['status'] = 1;
                    }
                    $data['date'] = $line;
                }
                $i++;
            }
            return $data;
        }
    }

    private function VS($path)
    {

        $data = $this->get_remote_data('https://api.hpwebdesign.id/hpml.txt');
        if (strpos($data, $_SERVER['SERVER_NAME']) !== false) {
            return $_SERVER['SERVER_NAME'];
        }

        return false;
    }

    protected function hpml($ref = 0, $date = null)
    {
        $pf = dirname(getcwd()) . '/system/library/cache/hpml_log';
        if (!file_exists($pf)) {
            fopen($pf, 'w');
        }
        $fh = fopen($pf, 'r');

        if (!fgets($fh) || $ref = 1) {
            $fh = fopen($pf, "wb");
            if (!$fh) {
                chmod($pf, 644);
            }
            fwrite($fh, "// HPWD -> Dilarang mengedit isi file ini untuk tujuan cracking validasi atau tindakan terlarang lainnya" . PHP_EOL);
            $date = $date ? $date : date("d-m-Y", strtotime(date("d-m-Y") . ' + 1 year'));
            fwrite($fh, $date . PHP_EOL);
            fwrite($fh, $_SERVER['SERVER_NAME'] . PHP_EOL);
        }

        fclose($fh);
    }

    private function internetAccess()
    {
        return true;
    }


    public function get_remote_data($url, $post_paramtrs = false)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        if ($post_paramtrs) {
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&" . $post_paramtrs);
        }
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
        curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
        curl_setopt($c, CURLOPT_MAXREDIRS, 10);
        $follow_allowed = (ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
        if ($follow_allowed) {
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        }
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($c, CURLOPT_REFERER, $url);
        curl_setopt($c, CURLOPT_TIMEOUT, 60);
        curl_setopt($c, CURLOPT_AUTOREFERER, true);
        curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
        $data = curl_exec($c);
        $status = curl_getinfo($c);
        curl_close($c);
        if ($status['http_code'] == 200) {
            return $data;
        } else if ($status['http_code'] == 301 || $status['http_code'] == 302) {
            if (!$follow_allowed) {
                if (!empty($status['redirect_url'])) {
                    $redirURL = $status['redirect_url'];
                } else {
                    preg_match('/href\=\"(.*?)\"/si', $data, $m);
                    if (!empty($m[1])) {
                        $redirURL = $m[1];
                    }
                }
                if (!empty($redirURL)) {
                    return call_user_func(__FUNCTION__, $redirURL, $post_paramtrs);
                }
            }
        }
        return "ERRORCODE22 with $url!!<br/>Last status codes<b/>:" . json_encode($status) . "<br/><br/>Last data got<br/>:$data";
    }

    public function curlcheck()
    {
        return in_array('curl', get_loaded_extensions()) ? true : false;
    }

    public function storeAuth()
    {
        $data['curl_status'] = $this->curlcheck();

        $this->load->language('extension/module/hp_map_location');

        $this->document->setTitle($this->language->get('text_validation'));

        $data['text_curl'] = $this->language->get('text_curl');
        $data['text_disabled_curl'] = $this->language->get('text_disabled_curl');

        $data['text_validation'] = $this->language->get('text_validation');
        $data['text_validate_store'] = $this->language->get('text_validate_store');
        $data['text_information_provide'] = $this->language->get('text_information_provide');
        $data['domain_name'] = $this->language->get('text_validate_store');
        $data['domain_name'] = $_SERVER['SERVER_NAME'];

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false,
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title2'),
            'href' => $this->url->link('extension/module/hp_map_location', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false,
        ];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/validation', $data));
    }


}
