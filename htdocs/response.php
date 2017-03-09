<?php
require_once "database.php";

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

class Response {

	public $bot;
	public $request;

	function __construct() {

		$this->request = file_get_contents('php://input');

		/* Get Header Data */
		$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

		/* Logging to Console*/
		file_put_contents('php://stderr', 'Body: '.$this->request);

		/* Validation */
		if (empty($signature)) {
			return $response->withStatus(400, 'Signature not set');
		}

		if ($_ENV['PASS_SIGNATURE'] == false && !SignatureValidator::validateSignature($this->request, $_ENV['CHANNEL_SECRET'], $signature)) {
			return $response->withStatus(400, 'Invalid signature');
		}

		/* Initialize bot*/
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
		$this->bot  = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	}

	/* Bot Event Request Handler */

	public function botEventsRequestHandler() {

		$requestHandler = json_decode($this->request, true);
		return $requestHandler['events'];
	}

	/* Bot Usability | Every method can only be used trough foreach */

	/*==================================Mandatory==================================*/

	public function botDisplayName($userId = null) {

		$getProfile  = $this->bot->getProfile();
		$profile     = json_decode($getProfile, true);
		$displayName = $profile['displayName'];
		return $displayName;
	}

	/*General*/

	public function botEventReplyToken($event) {

		return $event['replyToken'];
	}

	public function botEventType($event) {

		return $event['type'];
	}

	public function botEventTimestamp($event) {

		return $event['timestamp'];
	}

	/*Source*/

	public function botEventSourceType($event) {

		return $event['source']['type'];
	}

	public function botEventSourceUserId($event) {

		return $event['source']['userId'];
	}

	public function botEventSourceRoomId($event) {

		return $event['source']['roomId'];
	}

	public function botEventSourceGroupId($event) {

		return $event['source']['groupId'];
	}

	public function botEventSourceIsUser($event) {

		if ($event['source']['type'] == "user") {
			return true;
		}
	}

	public function botEventSourceIsRoom($event) {

		if ($event['source']['type'] == "room") {

			return true;
		}
	}

	public function botEventSourceIsGroup($event) {

		if ($event['source']['type'] == "group") {
			return true;
		}
	}

	/*Message*/

	public function botEventMessageId($event) {

		// text, image, video, audio, location, sticker
		return $event['message']['id'];
	}

	public function botEventMessageType($event) {

		// text, image, video, audio, location, sticker
		return $event['message']['type'];
	}

	public function botEventMessageText($event) {

		// text
		return $event['message']['text'];
	}

	public function botEventMessageTitle($event) {

		// location
		return $event['message']['title'];
	}

	public function botEventMessageAddress($event) {

		// location
		return $event['message']['address'];
	}

	public function botEventMessageLatitude($event) {

		// location
		return $event['message']['latitude'];
	}

	public function botEventMessageLongitude($event) {

		// location
		return $event['message']['longitude'];
	}

	public function botEventMessagePackadeId($event) {

		// sticker
		return $event['message']['packageId'];
	}

	public function botEventMessageStickerId($event) {

		// sticker
		return $event['message']['stickerId'];
	}

	/*Postback*/

	public function botEventPostbackData($event) {

		return $event['postback']['data'];
	}

	/*Beacon*/

	public function botEventBeaconkHwid($event) {

		return $event['beacon']['hwid'];
	}

	public function botEventBeaconType($event) {

		return $event['beacon']['type'];
	}

	/*================================================================*/

	/* Bot Action */

	/*Leave*/
	public function botEventLeaveRoom($event) {

		return $this->bot->leaveRoom($this->botEventSourceRoomId($event));
	}

	public function botEventLeaveGroup($event) {

		return $this->bot->leaveRoom($this->botEventSourceGroupId($event));
	}

	/*Send Content*/
	public function botSendText($event, $text) {

		$input    = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}

	}

	public function botSendImage($event, $original, $preview) {

		$input    = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($original, $preview);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendVideo($event, $original, $preview) {

		$input    = new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($original, $preview);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendAudio($event, $content, $duration) {

		$input    = new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder($content, $duration);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendLocation($event, $title, $address, $latitude, $longitude) {

		$input    = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $latitude, $longitude);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendSticker($event, $packageId, $stickerId) {

		$input    = new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($packageId, $stickerId);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendImagemap($event, $baseUrl, $altText, $baseSizeBuilder, array $imagemapActionBuilders) {

		$input    = new ImagemapMessageBuilder($baseUrl, $altText, $baseSizeBuilder, $imagemapActionBuilders);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	public function botSendTemplate($event, $altText, $templateBuilder) {

		$input    = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($altText, $templateBuilder);
		$response = $bot->replyMessage($this->botEventReplyToken($event), $input);

		if ($response->isSucceeded()) {

			return true;
		}
	}

	/*Receive Content*/
	public function botReceiveText($event) {

		return $this->botEventMessageText($event);
	}

	public function botReceiveImage($event) {

		if ($this->botEventMessageType($event) == 'image') {

			$response = $this->bot->getMessageContent($this->botEventMessageId($event));

			if ($response->isSucceeded()) {

				$folder    = "image";
				$math      = mt_rand(1, 10000000000);
				$time      = time();
				$extension = ".jpg";
				$file      = $folder.'/'.$time.'-'.$math.$extension;
				$fp        = fopen($file, 'w');
				fwrite($fp, $response->getRawBody());
				fclose($fp);

				return "https://dl.abror.net/content/$file";
			}
		}
	}

	public function botReceiveAudio($event) {

		if ($this->botEventMessageType($event) == 'audio') {

			$response = $this->bot->getMessageContent($this->botEventMessageId($event));

			if ($response->isSucceeded()) {

				$folder    = "audio";
				$math      = mt_rand(1, 10000000000);
				$time      = time();
				$extension = ".jpg";
				$file      = $folder.'/'.$time.'-'.$math.$extension;
				$fp        = fopen($file, 'w');
				fwrite($fp, $response->getRawBody());
				fclose($fp);

				return "https://dl.abror.net/content/$file";
			}
		}
	}

	public function botReceiveVideo($event) {

		if ($this->botEventMessageType($event) == 'video') {

			$response = $this->bot->getMessageContent($this->botEventMessageId($event));

			if ($response->isSucceeded()) {

				$folder    = "video";
				$math      = mt_rand(1, 10000000000);
				$time      = time();
				$extension = ".mp4";
				$file      = $folder.'/'.$time.'-'.$math.$extension;
				$fp        = fopen($file, 'w');
				fwrite($fp, $response->getRawBody());
				fclose($fp);

				return "https://dl.abror.net/content/$file";
			}
		}
	}

	public function botReceiveSticker($event) {

		if ($this->botEventMessageType($event) == 'sticker') {

			$sticker   = array();
			$packageId = array(
				'packageId',
			);
			$stickerId = array(
				'stickerId',
			);

			array_push($packageId['packageId'], $this->botEventMessagePackadeId($event));
			array_push($stickerId['stickerId'], $this->botEventMessageStickerId($event));

			array_push($sticker, $packageId);
			array_push($sticker, $stickerId);

			return $sticker;
		}
	}

	public function botReceiveLocation($event) {

		if ($this->botEventMessageType($event) == 'location') {

			$location = array();
			$title    = array(
				'title',
			);
			$address = array(
				'address',
			);
			$latitude = array(
				'latitude',
			);
			$longitude = array(
				'longitude',
			);

			array_push($title['title'], $this->botEventMessageTitle($event));
			array_push($address['address'], $this->botEventMessageAddress($event));
			array_push($latitude['latitude'], $this->botEventMessageLatitude($event));
			array_push($longitude['longitude'], $this->botEventMessageLongitude($event));

			array_push($location, $title);
			array_push($location, $address);
			array_push($location, $latitude);
			array_push($location, $longitude);

			return $location;
		}
	}

	/*Is Receive Content*/
	public function botIsReceiveText($event) {

		if ($this->botEventMessageType($event) == 'text') {

			return true;
		}
	}

	public function botIsReceiveImage($event) {

		if ($this->botEventMessageType($event) == 'image') {

			return true;
		}
	}

	public function botIsReceiveAudio($event) {

		if ($this->botEventMessageType($event) == 'audio') {

			return true;
		}
	}

	public function botIsReceiveVideo($event) {

		if ($this->botEventMessageType($event) == 'video') {

			return true;
		}
	}

	public function botIsReceiveSticker($event) {

		if ($this->botEventMessageType($event) == 'sticker') {

			return true;
		}
	}

	public function botIsReceiveLocation($event) {

		if ($this->botEventMessageType($event) == 'location') {

			return true;
		}
	}

	/*Main*/
	public function mainBot() {

		foreach ($this->botEventsRequestHandler() as $event) {

			// $response = $this->bot->replyText($event['replyToken'], "1");

			if ($this->botEventSourceIsUser($event)) {

				$response = $this->bot->replyText($event['replyToken'], "2");

				if ($this->botIsReceiveText($event)) {

					$response = $this->bot->replyText($event['replyToken'], "3");

					if ($this->botReceiveText($event) == "halo") {

						$response = $this->bot->replyText($event['replyToken'], "4");

						$this->botSendText($event, "halo juga");

						return $response->getHTTPStatus().' '.$response->getRawBody();
					}
				}
			}

		}
	}
}
?>
