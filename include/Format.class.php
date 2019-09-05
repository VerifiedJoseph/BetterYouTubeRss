<?php

abstract class Format {

	/** @var array $data Feed data */
	protected $data = array();

	/** @var string $feed Formatted feed data */
	protected $feed = '';

	/** @var string $urlRegex URL regex */
	protected $urlRegex = '/https?:\/\/(?:www\.)?(?:[a-zA-Z0-9-.]{2,256}\.[a-z]{2,20})(\:[0-9]{2,4})?(?:\/[a-zA-Z0-9@:%_\+.,~#"!?&\/\/=\-*]+|\/)?/';

	/** @var string $embedUrl YouTube URL */
	protected $embedUrl = 'https://www.youtube.com';

	/** @var string $embedUrlNoCookie YouTube no cookie URL */
	protected $embedUrlNoCookie = 'https://www.youtube-nocookie.com';

	/**
	 * Constructor
	 *
	 * @param array $data Cache/fetch data
	 * @param boolean $embedVideos Embed YouTube videos in feed
	 */
	public function __construct(array $data, bool $embedVideos = false) {
		$this->data = $data;
		$this->embedVideos = $embedVideos;
	}

	/**
	 * Build feed
	 */
	abstract public function build();

	/**
	 * Returns feed
	 *
	 * @return string
	 */
	public function get() {
		return $this->feed;
	}

	/**
	 * Build feed itmes
	 *
	 * @return string Items as XML
	 */
	abstract protected function buildItmes();

	/**
	 * Build item categories
	 *
	 * @param array $categories Item categories
	 */
	abstract protected function buildCategories(array $categories);

	/**
	 * Build item content (description)
	 *
	 * @param array $video Video data
	 */
	protected function buildContent(array $video) {
		
		$description = $this->formatDescription($video['description']);
		$published = Helper::convertUnixTime($video['published'], config::get('DATE_FORMAT'));

		$media = <<<EOD
<a target="_blank" title="Watch" href="https://youtube.com/watch?v={$video['id']}"><img src="{$video['thumbnail']}"/></a>
EOD;

		if ($this->embedVideos === true) {
			$url = $this->embedUrl;

			if (config::get('YOUTUBE_EMBED_PRIVACY')) {
				$url = $this->embedUrlNoCookie;
			}

		$media = <<<EOD
<iframe width="100%" height="410" src="{$url}/embed/{$video['id']}" frameborder="0" allow="encrypted-media;" allowfullscreen></iframe>
EOD;
		}

		return <<<EOD
{$media}<hr/>Published: {$published} - Duration: {$video['duration']}<hr/><p>{$description}</p>
EOD;
	}

	/**
	 * Format video description
	 * Converts URLs to HTMl links
	 *
	 * @param string $description
	 */
	protected function formatDescription(string $description) {

		if (empty($description)) {
			return ' ';
		}

		$formatted = '';
		$lines = explode("\n", $description);

		foreach ($lines as $index => $line) {
			if(preg_match($this->urlRegex, $line, $matches)) {
				$line = str_replace($matches[0], '<a target="_blank" href="' . $matches[0] . '">' . $matches[0] . '</a>', $line);
			}

			$formatted .= $line . '<br/>';
		}

		return $formatted;
	}

	/**
	 * Convert special characters to HTML entities
	 *
	 * @param string $text
	 * @return string String with encoded characters
	 */
	protected function xmlEncode($text) {
		return htmlspecialchars($text, ENT_XML1);
	}
}
