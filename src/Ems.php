<?php
/*
 * Simple interface to make api calls to an EMS server.
 */
namespace bledileka\Ems;

class Ems
{
	public function __construct($configs)
	{

		if (isset($configs["api_key"]) && $configs["api_key"] != "") {
			$this->apiKEY = trim(strip_tags($configs["api_key"]));
		} else {
			/* no api key ?*/
			die('Please set an api key!');
		}

		if (isset($configs["api_url"]) && $configs["api_url"] != "") {
			$this->apiURL = trim(strip_tags($configs["api_url"]));
		} else {
			/* no api key ?*/
			die('Please set an EMS Url!');
		}

	}

	public function _call($arr)
	{
		if (isset($arr["path"]) && $arr["path"] != "") {

			switch ($arr["path"]) {
				case "contacts/add":
					$method = "post";
					break;
			}

			if (isset($method) && $method == "post") {

				$result = $this->_curlPost($path = $arr["path"], $options = $arr["data"], $body = 1);
				return $result;
			} else {
				if (isset($method) && $method == "get") {
					if (isset($arr["data"])) {
						$result = $this->_getPost($path = $arr["path"], $options = $arr["data"]);
					} else {
						$result = $this->_getPost($path = $arr["path"]);
					}
					return $result;
				} else {
					die("Oooops - some error occurred while determining this request!");
				}
			}

		} else {
			die("Please define a path for this call!");
		}
	}

	public function _curlGet($path, $options = NULL)
	{
		if (isset($options) && is_array($options)) {
			$str = "";
			foreach ($options as $name => $value) {
				$str .= '&' . $name . "=" . $this->urlencodeAPI($value);
			}
			$url = $this->apiURL . $path . "?api_key=" . $this->apiKEY . $str;
		} else {
			$url = $this->apiURL . $path . "?api_key=" . $this->apiKEY;
		}

		$ch = curl_init();
		if (isset($_SERVER["HTTP_USER_AGENT"])) {
			$ua = $_SERVER["HTTP_USER_AGENT"];
		} else {
			$ua = "EMSApi";
		}

		$headers = array('Content-Type: application/json',
			'X-Api-Key: ' . $this->apiKEY,
			"User-Agent: " . $ua,
		);

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0
		);

		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);

		if ($errno = curl_errno($ch)) {
			$error_message = curl_strerror($errno);
			echo "FATAL --> cURL error: ({$errno}): {$error_message}\n\n";
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode == 0) {
			echo "cURL call failed!";
		} else {
			return json_decode($result, true);
		}

	}

	public function _curlPost($path, $options = NULL, $body = NULL)
	{
		if (is_array($options)) {
			$str = "";
			$i = 0;
			if (isset($body)) {
				/* body calls do not need the url params for posted data */
				$url = $this->apiURL . $path . "?api_key=" . $this->apiKEY;
			} else {
				foreach ($options as $name => $value) {
					$str .= '&' . $name . "=" . $this->urlencodeAPI($value);
				}
				$url = $this->apiURL . $path . "?api_key=" . $this->apiKEY . $str;
			}

		} else {
			$url = $this->apiURL . $path . "?api_key=" . $this->apiKEY;
		}

		if (isset($_SERVER["HTTP_USER_AGENT"])) {
			$ua = $_SERVER["HTTP_USER_AGENT"];
		} else {
			$ua = "EMSApi";
		}

		$ch = curl_init($url);
		if (isset($body)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'X-Api-Key: ' . $this->apiKEY,
				"User-Agent: " . $ua
			));
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'X-Api-Key: ' . $this->apiKEY,
				"User-Agent: " . $ua
			));
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($ch);

		if ($errno = curl_errno($ch)) {
			$error_message = curl_strerror($errno);
			echo "FATAL --> cURL error: ({$errno}): {$error_message}\n\n";
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode == 0) {
			echo "cURL call failed!";
		} else {
			return json_decode($result, true);
		}
	}

	function urlencodeAPI($string)
	{
		if (!is_array($string)) {
			$str = urlencode($string);
			$str = str_replace("%28", "(", $str);
			$str = str_replace("%29", ")", $str);
			$str = str_replace("%3D", "=", $str);
			$str = str_replace("%2C", ",", $str);
			$str = str_replace("%21", "!", $str);
			$str = str_replace("+", "%20", $str);
			return $str;
		} else {
			$return = "";
			if (isset($string[0])) {
				foreach ($string[0] as $a => $str) {
					$str = urlencode($str);
					$str = str_replace("%28", "(", $str);
					$str = str_replace("%29", ")", $str);
					$str = str_replace("%3D", "=", $str);
					$str = str_replace("%2C", ",", $str);
					$str = str_replace("%21", "!", $str);
					$str = str_replace("+", "%20", $str);
					$return .= '&' . $a . "=" . $str;
				}
			}
			return $return;
		}
	}

}