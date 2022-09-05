<?php
declare(strict_types=1);

namespace Netlogix\Nxsentry\Client;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;

class SentryHttpClient implements HttpClient, HttpAsyncClient
{

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    public function __construct()
    {
        $this->client = GuzzleClientFactory::getClient();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }

    public function sendAsyncRequest(RequestInterface $request)
    {
        return $this->client->sendAsync($request);
    }
}
