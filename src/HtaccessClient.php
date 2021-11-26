<?php declare(strict_types=1);

namespace Madewithlove;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

/**
 * @phpstan-type HtaccessResponseData array{
 *     'output_url': string,
 *     'lines': array<int,array{
*                  'value': string,
*                  'message': string,
*                  'isMet': bool,
*                  'isValid': bool,
*                  'wasReached': bool,
*                  'isSupported': bool,
*              }>,
 *     'output_status_code': int,
 *     'url':string,
 *     'errors'?: array<int,array{'field': string, 'message': string}>
 * }
 */
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
        /** @var HtaccessResponseData */
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
                function (array $line): ResultLine {
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
        /** @var HtaccessResponseData */
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
        /** @var HtaccessResponseData */
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

    /**
     * @param array<string,mixed> $requestData
     * @return array<string,mixed>
     */
    private function request(string $method, string $endpoint = '', array $requestData = []): array
    {
        $request = $this->requestFactory->createServerRequest(
            $method,
            'https://htaccess.madewithlove.com/api' . $endpoint
        );

        /** @var string $requestBody */
        $requestBody = json_encode($requestData);

        $body = $request->getBody();
        $body->write($requestBody);

        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $response = $this->httpClient->sendRequest($request);

        /** @var HtaccessResponseData */
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (isset($responseData['errors'])) {
            throw HtaccessException::fromApiErrors($responseData['errors']);
        }

        return $responseData;
    }
}
