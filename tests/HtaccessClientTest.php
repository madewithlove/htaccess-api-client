<?php declare(strict_types=1);

namespace Madewithlove;

use Http\Adapter\Guzzle7\Client;
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
            'RewriteRule .* /foo [R=301]'
        );

        $this->assertEquals(
            'http://localhost/foo',
            $response->getOutputUrl()
        );

        $this->assertEquals(
            301,
            $response->getOutputStatusCode()
        );

        $this->assertEquals(
            [
                new ResultLine(
                    'RewriteRule .* /foo [R=301]',
                    "The new url is http://localhost/foo\nTest are stopped, a redirect will be made with status code 301",
                    true,
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
            ServerVariables::empty()->with(
                ServerVariable::HTTP_REFERER,
                'http://example.com'
            )
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
            ServerVariables::empty()->with(
                ServerVariable::SERVER_NAME,
                'example.com'
            )
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
        $this->assertEquals('RewriteCond %{SERVER_NAME} example.com', $response->getLines()[0]->getLine());
        $this->assertStringContainsString('condition was met', $response->getLines()[0]->getMessage());
        $this->assertTrue($response->getLines()[0]->isMet());
        $this->assertTrue($response->getLines()[0]->isValid());
        $this->assertTrue($response->getLines()[0]->wasReached());
        $this->assertTrue($response->getLines()[0]->isSupported());
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

    /** @test */
    public function it can share a test run(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteRule .* /foo [R]'
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.be',
            $response->getShareUrl()
        );
        $this->assertMatchesRegularExpression(
            '#.*?share=[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}#',
            $response->getShareUrl()
        );

        $shareUuid = substr($response->getShareUrl(), -36);
        $sharedResult = $client->getShared($shareUuid);

        $this->assertEquals(
            'http://localhost/foo',
            $sharedResult->getOutputUrl()
        );

        $this->assertEquals(
            [
                new ResultLine(
                    'RewriteRule .* /foo [R]',
                    "The new url is http://localhost/foo\nTest are stopped, a redirect will be made with status code 302",
                    true,
                    true,
                    true,
                    true
                ),
            ],
            $sharedResult->getLines()
        );
    }

    /** @test */
    public function it can share a test run with a referer(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteCond %{HTTP_REFERER} http://example.com
             RewriteRule .* /example-page [L]',
            'http://example.com'
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.be',
            $response->getShareUrl()
        );
        $this->assertMatchesRegularExpression(
            '#.*?share=[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}#',
            $response->getShareUrl()
        );

        $shareUuid = substr($response->getShareUrl(), -36);
        $sharedResult = $client->getShared($shareUuid);

        $this->assertEquals(
            'http://localhost/example-page',
            $sharedResult->getOutputUrl()
        );
    }

    /** @test */
    public function it can share a test run with a server name(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new ServerRequestFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteCond %{SERVER_NAME} example.com
             RewriteRule .* /example-page [L]',
            null,
            'example.com'
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.be',
            $response->getShareUrl()
        );
        $this->assertMatchesRegularExpression(
            '#.*?share=[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}#',
            $response->getShareUrl()
        );

        $shareUuid = substr($response->getShareUrl(), -36);
        $sharedResult = $client->getShared($shareUuid);

        $this->assertEquals(
            'http://localhost/example-page',
            $sharedResult->getOutputUrl()
        );
    }
}
