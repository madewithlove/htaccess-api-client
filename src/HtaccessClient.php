<?php declare(strict_types=1);

namespace Madewithlove;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

final class HtaccessClient
{
    public function __construct(
        private ClientInterface $httpClient,
        private ServerRequestFactoryInterface $requestFactory
    ) {
    }

    /**
     * @throws HtaccessException
     */
    public function test(
        string $url,
        string $htaccess,
        ?ServerVariables $serverVariables = null
    ): HtaccessResult {
        $serverVariables = $serverVariables ?? ServerVariables::default();
        $responseData = $this->request(
            'POST',
            '',
            [
                'url' => $url,
                'htaccess' => $htaccess,
                'serverVariables' => $serverVariables->toArray(),
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
            ),
            $responseData['output_status_code']
        );
    }

    /**
     * @throws HtaccessException
     */
    public function share(
        string $url,
        string $htaccess,
        ?ServerVariables $serverVariables = null
    ): ShareResult {
        $serverVariables = $serverVariables ?? ServerVariables::default();
        $responseData = $this->request(
            'POST',
            '/share',
            [
                'url' => $url,
                'htaccess' => $htaccess,
                'serverVariables' => $serverVariables->toArray()
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
            ),
            $responseData['output_status_code']
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
