<?php

namespace Crystal\Api;

/**
 * The api handler.
 */
class FourPlayerChess implements Handler
{
    /** @var array $endpoint The api enpoints */
    private $endpoints = [
        'arrow' => '{url}/bot?token={token}&arrows=',
        'chat' => '{url}/bot?token={token}&chat=',
        'clear' => '{url}/bot?token={token}&arrows=clear',
        'play' => '{url}/bot?token={token}&play=',
        'resign' => '{url}/bot?token={token}&play=R',
        'stream' => '{url}/bot?token={token}&stream=1',
    ];

    /** @var string $userAgent The user agent to send. */
    private $userAgent = 'CrystalChessApiConsole/v01.0.0 (www.chess.com/member/omatamix)';

    /**
     * Construct a new api controller.
     *
     * @param string $token     The api token.
     * @param string $userAgent The user agent to set.
     *
     * @return void Returns nothing.
     */
    public function __construct(string $token, string $userAgent = '', $accessType = 'beta')
    {
        $accessUrl = [
            'beta' => 'https://4player-beta.chess.com',
            'main' => 'https://4player.chess.com',
        ];
        $token = trim($token);
        $res = [];
        foreach ($this->endpoints as $type => $endpoint) {
            $endpoint = str_replace('{token}', $token, $endpoint);
            $endpoint = str_replace('{url}', $accessUrl[$accessType], $endpoint);
            $res[$type] = $endpoint;
        }
        $this->endpoints = $res;
        if ($userAgent !== '') {
            $this->userAgent = $userAgent;
        }
    }

    /**
     * Get the 4pc data stream.
     *
     * @return mixed Returns the stream response.
     */
    public function getStream()
    {
        $fp = fopen($this->endpoints['stream'], 'rb');
        while (($line = fgets($fp)) !== \false)
            yield rtrim($line, "\r\n");
        fclose($fp);
    }

    /**
     * Make an arrow.
     *
     * @param string $squareOne The square to start the arrow from.
     * @param string $squareTwo The square to end the arrow to.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function arrow(string $squareOne, string $squareTwo): bool
    {
        $url = $this->endpoints['arrow'] . $squareOne . $squareTwo;
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Send something to the chat.
     *
     * @param string $message The message to send to the chat.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function chat(string $message): bool
    {
        $url = $this->endpoints['chat'] . urlencode($message);
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Make a circle.
     *
     * @param string $square The square to put the circle.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function circle(string $square): bool
    {
        $url = $this->endpoints['arrow'] . $square . $square;
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Clear all the circles and arrows.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function clear(): bool
    {
        $url = $this->endpoints['clear'];
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Play a move.
     *
     * @param string $squareOne     The from square.
     * @param string $squareTwo     The to square.
     * @param string $promotionCode The piece to convert the pawn to.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function play(string $squareOne, string $squareTwo, string $promotionCode = 'Q'): bool
    {
        $url = $this->endpoints['play'] . $squareOne . $squareTwo . $promotionCode;
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Resigns a game.
     *
     * @return bool Returns true if the request was sent and false if not.
     */
    public function resign(): bool
    {
        $url = $this->endpoints['resign'];
        $resp = $this->sendRequest($url);
        if ($resp) {
            return true;
        }
        return false;
    }

    /**
     * Send a request.
     *
     * @param $url The url to send the request to.
     *
     * @return mixed Returns the requests response.
     */
    private function sendRequest(string $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->userAgent
        ]);
        if (!($resp = curl_exec($curl))) {
            $resp = false;
        }
        curl_close($curl);
        return $resp;
    }
}
