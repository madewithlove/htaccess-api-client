<?php

declare(strict_types=1);

namespace Madewithlove\HtaccessApiClient;

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
        $serverVariables ??= ServerVariables::default();
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
                fn (array $line): ResultLine => new ResultLine(
                    $line['value'],
                    $line['message'],
                    $line['isMet'],
                    $line['isValid'],
                    $line['wasReached'],
                    $line['isSupported']
                ),
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
        $serverVariables ??= ServerVariables::default();
        /** @var HtaccessResponseData */
        $responseData = $this->request(
            'POST',
            '/share',
            [
                'url' => $url,
                'htaccess' => $htaccess,
                'serverVariables' => $serverVariables->toArray(),
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
                fn (array $line): ResultLine => new ResultLine(
                    $line['value'],
                    $line['message'],
                    $line['isMet'],
                    $line['isValid'],
                    $line['wasReached'],
                    $line['isSupported']
                ),
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

        $requestBody = json_encode($requestData, JSON_THROW_ON_ERROR);

        $body = $request->getBody();
        $body->write($requestBody);

        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($body);

        $response = $this->httpClient->sendRequest($request);

        /** @var array{errors?: array<string, array<string>>}|HtaccessResponseData|null */
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!is_array($responseData)) {
            throw new HtaccessException('Unexpected response from API');
        }

        if (isset($responseData['errors'])) {
            /** @var array<string, array<string>> $errors */
            $errors = $responseData['errors'];
            throw HtaccessException::fromApiErrors($errors);
        }

        /** @var HtaccessResponseData $responseData */
        return $responseData;
    }
}
