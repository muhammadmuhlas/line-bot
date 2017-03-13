<?php
/*
	Meme Generator
	Input (String):
		List : '@meme list'
		Image: '@meme <id>;<caption1>;<caption2>'
	Output (Object):
		->type (String, only contains 'list','image', or 'error')
		->content (array of object for 'list', string for 'image', and string for 'error')
	WILL RETURN BOOLEAN FALSE IF NOT AN '@meme ' QUERY 
*/
class Meme {

	private $separator;
	private $username;
	private $password;

	public function __construct() {

		$this->separator = ';';
		$this->username  = 'imgflip_hubot';
		$this->password  = 'imgflip_hubot';

	}

	public function isValidQuery($text) {

		if (strlen($text) <= 6) {

			return false;
		}

		$prefix = substr($text, 0, 6);

		if ($prefix == '@meme ') {

			return true;
		}
		return false;
	}

	public function checkCommand($command) {

		if ($command == 'list') {

			return 'list';
		}
		return 'create_meme';
	}

	public function isValidMemeQuery($command) {

		//check if there are exacly 2 separator
		$total_separator = substr_count($command, $this->separator);

		if ($total_separator == 2) {
			return true;
		}

		return false;
	}

	public function getMemeAndCaption($command) {

		//output:
		//meme_and_caption[0] = meme_id
		//meme_and_caption[1] = caption1
		//meme_and_caption[2] = caption2
		$meme_and_caption = explode($this->separator, $command);
		return $meme_and_caption;

	}

	public function getMeme($meme_and_caption) {

		//output: JSON
		$url  = 'https://api.imgflip.com/caption_image';
		$data = array(
			'template_id' => $meme_and_caption[0],
			'username'    => $this->username,
			'password'    => $this->password,
			'text0'       => $meme_and_caption[1],
			'text1'       => $meme_and_caption[2],
		);

		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			),
		);

		$context = stream_context_create($options);
		$result  = file_get_contents($url, false, $context);
		return $result;
	}

	public function getList() {

		$url  = 'https://api.imgflip.com/get_memes';
		$result  = file_get_contents($url);
		//output: JSON
		return $result;
	}

	public function postImage($url) {

		$result = new stdClass();
		$result->type = 'image';
		$result->content = $url;
		return $result;
	}

	public function postError($error_message) {

		$result = new stdClass();
		$result->type = 'error';
		$result->content = $error_message;
		return $result;
	}

	public function postRequestedImage($JSON_from_server) {

		$converted_JSON = json_decode($JSON_from_server);
		$image_url   = $converted_JSON->data->url;
		return $this->postImage($image_url);
	}

	public function postList($list_JSON) {

		//input: JSON
		$converted_JSON = json_decode($list_JSON);
		
		if ($converted_JSON->success == false) {
			return $this->postError('Failed to get meme list.');
		}

		//output : array of object
		//each object's element: id,name,url,width,height
		$result = new stdClass();
		$result->type = 'list';
		$result->content = $converted_JSON->data->memes;
		return $result;
	}

	public function memeExist($JSON_from_server) {

		$converted_JSON = json_decode($JSON_from_server);
		return $converted_JSON->success;

	}

	public function mainMeme($text) {

		if ($this->isValidQuery($text)) {
			
			$command      = substr($text, 6);
			$command_type = $this->checkCommand($command);
			
			if ($command_type == 'list') {

				$list = $this->getList();
				return $this->postList($list);
			}

			if ($command_type = 'create_meme') {

				if ($this->isValidMemeQuery($command)) {

					$meme_and_caption = $this->getMemeAndCaption($command);
					$JSON_from_server = $this->getMeme($meme_and_caption);
					
					if ($this->memeExist($JSON_from_server)) {

						return $this->postRequestedImage($JSON_from_server);
					} 

					$error_message = json_decode($JSON_from_server);
					return $this->postError($error_message);
				}

				return $this->postError('Invalid query. Use "@meme list" if you want to check the meme list, or "@meme id;caption1;caption2" if you want to create meme.');
			}

		}
		return false;
	}
}

//test
//$meme = new Meme;
//echo var_dump($meme->mainMeme('@meme lista'));