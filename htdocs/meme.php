<?php

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

		if (strlen($text) <= 5) {

			return false;
		}

		$prefix = substr($text, 0, 5);

		if ($prefix == 'meme ') {

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
		//todo: check in api
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

		//todo: check in api
	}

	public function postImage($JSON_from_server) {

		$converted_JSON = json_decode($JSON_from_server);
		$images   = $converted_JSON->data->url;
		return $images;
	}

	public function postList($list) {

		//list in JSON
		//todo : check api, sambung ke utama
	}

	public function memeExist($JSON_from_server) {

		$converted_JSON = (array) json_decode($JSON_from_server);
		return $converted_JSON["success"];

	}

	public function mainMeme($text) {

		if ($this->isValidQuery($text)) {
			$command      = substr($text, 5);
			$command_type = $this->checkCommand($command);
			//if ($command_type == 'list') {

			//get list JSON
			//	$list = getList();
			//post list to user
			//	postList($list);
			//}

			if ($command_type = 'create_meme') {

				if ($this->isValidMemeQuery($command)) {

					$meme_and_caption = $this->getMemeAndCaption($command);
					$JSON_from_server = $this->getMeme($meme_and_caption);
					
					if ($this->memeExist($JSON_from_server)) {

						return $this->postImage($JSON_from_server);
					} 

					//else {

						//send error from JSON
					//}
				}
			}
		}
		return 'false';
	}
}