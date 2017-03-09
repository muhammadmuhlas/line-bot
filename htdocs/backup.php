<?php
foreach ($this->requestHandler('events') as $event){

			$this->botEvents($event);

			if(strpos($event['message']['text'], 'bye dwabot') !== false){

				switch ($event['source']['type']) {

					case 'room':

						$response = $this->botLeaveRoom($event['source']['roomId']);
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();

						break;

					case 'group':

						$response = $this->botLeaveRoom($event['source']['groupId']);
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();

						break;
					
					default:

						$response = $this->bot->replyText($event['replyToken'], "Error Found : Undefined Event Type");
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();

						break;
				}
			}

			if (strpos($event['message']['text'], 'about dwabot') !== false){

				$text = "
				Dirty Word Alert Bot will scan any dirty word and its possible combination. Put me in your group or multichat and I will do the job.


				DWABot did not alert some 'word' or didn't work well? Please click 'Suggest Word & Bug Report'
				Have an idea for future DWABot feature? please click 'Suggest Feature'
				*via personal

				K*L*M
				Kurniawan Eka Nugraha
				Lantang Satriatama
				Muhammad Muhlas Abror
				";

				$response = $this->bot->replyText($event['replyToken'], $text);
            	return $response->getHTTPStatus() . ' ' . $response->getRawBody(); 
			}

			switch ($event['type']) {

				case 'message':

					if($event['message']['type'] == 'text'){

						$dataTable_dirtyWords = $this->databaseGetter('words');
						$data_dirtyWords = array();

						foreach ($dataTable_dirtyWords as $key => $value){

							array_push($data_dirtyWords, $value['word']);
						}

						$f_separator = "/\b";
						$m_separator = "+(|[^a-z])*";
						$e_separator = "+\b/i";
						$dirtyWords = array();

						foreach ($data_dirtyWords as $key => $value){

							$word = "";
							$word = $word . $f_separator;

							for ($i = 0; $i != strlen($value); $i++){

								$word = $word . $value[$i];
								if ($i+1 != strlen($value)){

									$word = $word . $m_separator;
								}
							}

							$word = $word . $e_separator;

							array_push($dirtyWords, $word);
						}

						foreach ($dirtyWords as $dirtyWord) {

							if (preg_match($dirtyWord, $event['message']['text'])) {

								$textResponse = $this->preferredResponseGetter();

								$response = $this->botSendText($event['replyToken'], $textResponse);
							}
						}
						return $response->getHTTPStatus() . ' ' . $response->getRawBody();
					}

					break;

				case 'join':

					$response = $this->bot->replyText($event['replyToken'], "Thanks for inviting me, i will alert your dirty friend");
					return $response->getHTTPStatus() . ' ' . $response->getRawBody();

					break;
				
				default:

					$response = $this->bot->replyText($event['replyToken'], "Error Found : Undefined Event Type");
					return $response->getHTTPStatus() . ' ' . $response->getRawBody();

					break;
			}
		}
	}
?>