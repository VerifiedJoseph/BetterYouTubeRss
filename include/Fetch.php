<?php

use \Curl\Curl;
use Configuration as Config;
use Helper\Url;

class Fetch {

	/** @var string $feedId YouTube channel or playlist ID */
	private string $feedId = '';

	/** @var string $feedType Feed type (channel or playlist) */
	private string $feedType = 'channel';

	/**
	 * Fetch YouTube RSS feed
	 *
	 * @param string $feedId Feed id (channel or playlist ID)
	 * @param string $feedType Feed type (channel or playlist)
	 *	 
	 * @return object|array Response from Curl
	 *
	 * @throws Exception If a curl error has occurred.
	 */
	public function feed(string $feedId, string $feedType) {
		$url = Url::getRssFeed($feedType, $feedId);

		$curl = new Curl();
		$curl->get($url);
		$this->response = $curl->response;

		if ($curl->getCurlErrorCode() !== 0) {
			throw new Exception('Error: ' . $curl->getCurlErrorCode() . ': ' . $curl->errorMessage);
		}

		if ($curl->getHttpStatusCode() !== 200) {
			throw new Exception('Failed to fetch: ' . $url);
		}

		return $curl->getResponse();
	}
}
