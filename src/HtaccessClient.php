<?php declare(strict_types=1);

namespace Madewithlove;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

final class HtaccessClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var ServerRequestFactoryInterface
     */
    private $requestFactory;

    public function __construct(ClientInterface $httpClient, ServerRequestFactoryInterface $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @throws HtaccessException
     */
    public function test(
        string $url,
        string $htaccess,
        ?string $referrer = '',
        ?string $serverName = ''
    ): HtaccessResult {
        $responseData = $this->request(
            'POST',
            '',
            [
                'url' => $url,
                'htaccess' => $htaccess,
                'referrer' => $referrer ?? '',
                'serverName' => $serverName ?? '',
            ]
        );

        return new HtaccessResult(
            $responseData['output_url'],
            array_map(
                function (array $line) {
                    return new ResultLine(
                        $line['value'],
                        $line['message'],
                        $line['isMet'],
                        $line['isValid'],
                        $line['wasReached'],
                        $line['isSupported']
                    );
                },
                $responseData['lines']
            )
        );
    }

    /**
     * @throws HtaccessException
     */
    public function share(
        string $url,
        string $htaccess,
        ?string $referrer = '',
        ?string $serverName = ''
    ): ShareResult {
        $responseData = $this->request(
            'POST',
            '/share',
            [
                'url' => $url,
                'htaccess' => $htaccess,
                'referrer' => $referrer ?? '',
                'server_name' => $serverName ?? '',
            ]
        );

        return new ShareResult($responseData['url']);
    }

    /**
     * @throws HtaccessException
     */
    public function getShared(string $shareUuid): HtaccessResult
    {
        $responseData = $this->request(
            'GET',
            '/share?share=' . $shareUuid
        );

        return new HtaccessResult(
            $responseData['output_url'],
            array_map(
                function (array $line) {
                    return new ResultLine(
                        $line['value'],
                        $line['message'],
                        $line['isMet'],
                        $line['isValid'],
                        $line['wasReached'],
                        $line['isSupported']
                    );
                },
                $responseData['lines']
            )
        );
    }

    private function request(string $method, string $endpoint = '', array $requestData = []): array
    {
        $request = $this->requestFactory->createServerRequest(
            $method,
            'https://htaccess.madewithlove.be/api' . $endpoint
        );

        $body = $request->getBody();
        $body->write(json_encode($requestData));

        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $response = $this->httpClient->sendRequest($request);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData['errors'])) {
            throw HtaccessException::fromApiErrors($responseData['errors']);
        }

        return $responseData;
    }
}
