<?php

class ControllerCommonHPValidate extends Controller
{
	private $v_d;
	private $version = '1.4.0.0';

	public function index()
	{
	}

	public function storeauth()
	{
		$this->load->model('extension/module/system_startup');

		$json = [];

		$this->language->load('common/hp_validate');

		$this->document->setTitle($this->language->get('text_validation'));

		$data['text_curl'] = $this->language->get('text_curl');
		$data['text_disabled_curl'] = $this->language->get('text_disabled_curl');

		$data['text_validation'] = $this->language->get('text_validation');
		$data['text_validate_store'] = $this->language->get('text_validate_store');
		$data['text_information_provide'] = $this->language->get('text_information_provide');
		$data['text_validate_store'] = $this->language->get('text_validate_store');
		$data['domain_name'] = str_replace("www.","",$_SERVER['SERVER_NAME']);

		if (isset($this->session->data['hp_ext']) && $this->session->data['hp_ext']) {
			foreach ($this->session->data['hp_ext'] as $extension) {
				if (isset($extension['db_key'])) {
					$domain = $this->rightman($extension['code']);
					$json['data'][] = $this->v_d;

					if ($this->config->get($extension['group'] . '_apitype') == "hpwdapi") {
						$this->model_extension_module_system_startup->apiusage($extension['group'], $extension['db_key'], $this->v_d['status']);

						if (!$this->v_d['status']) {
							$json['error']['domain'][] = sprintf($this->language->get('error_expired_api_usage'), $extension['name']);
							$json['link'][] = $this->url->link($extension['link'], 'user_token=' . $this->session->data['user_token'], true);
							$json['button_validate_store'] = $this->language->get('button_see_detail');
						}
					}
				} else {
					$domain = $this->rightman($extension['code']);
				}

				if (str_replace("www.","",$_SERVER['SERVER_NAME']) != $domain) {
					$this->flushdata($extension['group']);
					$json['error']['domain'][] = sprintf($this->language->get('error_store_domain'), $extension['name']);
					$json['link'][] = $this->url->link($extension['link'], 'user_token=' . $this->session->data['user_token'], true);

					$json['button_validate_store'] = $this->language->get('button_validate_store');
				}
			}
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function rightman($code)
	{
		if (file_exists(dirname(getcwd()) . '/system/library/cache/' . $code . '_log')) {
			$this->v_d = $this->VD(dirname(getcwd()) . '/system/library/cache/' . $code . '_log');

			$data = $this->get_remote_data('https://api.hpwebdesign.id/' . $code . '.txt');
			if (strpos($data, $this->v_d['store']) !== false && $this->v_d['store'] == str_replace("www.","",$_SERVER['SERVER_NAME'])) {
				return $this->v_d['store'];
			}
		}
		return false;
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
				if ($i == 2) {
					$data['store'] = $line;
				}
				$i++;
			}

			return $data;
		}
	}

	public function flushdata($code)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` LIKE '%" . $code . "%'");
	}

	public function license()
	{
		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = '';
		}

		if (isset($this->request->get['version'])) {
			$data['version'] = $this->request->get['version'];
		} else {
			$data['version'] = '';
		}

		$this->load->language('common/hp_validate');
		$eligible = $this->VD(dirname(getcwd()) . '/system/library/cache/' . $code . '_log');
		$data['domain'] = ($eligible) ? $eligible['store'] : '';
		$data['status'] = ($eligible) ? $eligible['status'] : '';
		$data['date'] = ($eligible) ? date('d F Y H:i:s', strtotime($eligible['date'])) : '';
		$this->response->setOutput($this->load->view('common/license', $data));
	}

	public function support()
	{
		$this->load->language('common/hp_validate');

		$data['thumb_get_support'] = HTTPS_SERVER . 'view/image/support/get-support.png';
		$data['thumb_hire_us'] = HTTPS_SERVER . 'view/image/support/hire-us.jpeg';
		$data['thumb_idea'] = HTTPS_SERVER . 'view/image/support/idea.jpeg';
		$data['thumb_maintenance'] = HTTPS_SERVER . 'view/image/support/maintenance.png';

		$data['get_support'] = 'https://hpwebdesign.id/support';
		$data['hire_us'] = 'https://hpwebdesign.id/hire-us';
		$data['idea'] = 'https://hpwebdesign.id/idea';
		$data['maintenance'] = 'https://hpwebdesign.id/maintenance';

		$this->response->setOutput($this->load->view('common/support', $data));
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
}
