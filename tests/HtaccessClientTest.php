<?php declare(strict_types=1);

namespace Madewithlove;

use Http\Adapter\Guzzle6\Client;
use Http\Factory\Guzzle\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

final class HtaccessClientTest extends TestCase
{
    /** @test */
    public function it returns the result from the api(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteRule .* /foo [R]'
        );

        $this->assertEquals(
            'http://localhost/foo',
            $response->getOutputUrl()
        );

        $this->assertEquals(
            [
                new ResultLine(
                    'RewriteRule .* /foo [R]',
                    "The new url is http://localhost/foo\nTest are stopped, a redirect will be made with status code 302",
                    true,
                    true,
                    true
                ),
            ],
            $response->getLines()
        );
    }

    /** @test */
    public function it allows for passing a referrer(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{HTTP_REFERER} http://example.com
             RewriteRule .* /example-page [L]',
            'http://example.com'
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
    }

    /** @test */
    public function it allows for passing a server name(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{SERVER_NAME} example.com
             RewriteRule .* /example-page [L]',
            null,
            'example.com'
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
    }

    /** @test */
    public function it throws an exception when we pass an invalid url(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $this->expectExceptionMessage('url: This is not a valid url');
        $this->expectException(HtaccessException::class);
        $client->test(
            'http:localhost',
            'RewriteRule .* /example-page [L]'
        );
    }

    /** @test */
    public function it throws an exception when we pass multiple invalid fields(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $this->expectExceptionMessage("url: This is not a valid url\nhtaccess: htaccess must not be empty");
        $this->expectException(HtaccessException::class);
        $client->test(
            'http:localhost',
            ''
        );
    }
}
