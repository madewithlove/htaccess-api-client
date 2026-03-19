<?php

declare(strict_types=1);

namespace Madewithlove\HtaccessApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HtaccessClientTest extends TestCase
{
    #[Test]
    public function it_returns_the_result_from_the_api(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
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

    #[Test]
    public function it_allows_for_passing_a_referrer(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{HTTP_REFERER} http://example.com
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with(
                'HTTP_REFERER',
                'http://example.com'
            )
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
    }

    #[Test]
    public function it_allows_for_passing_a_server_name(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{SERVER_NAME} example.com
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with(
                'SERVER_NAME',
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

    #[Test]
    public function it_allows_for_passing_an_http_user_agent(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{HTTP_USER_AGENT} (iPhone|Android)
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with(
                'HTTP_USER_AGENT',
                'Android'
            )
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
        $this->assertEquals('RewriteCond %{HTTP_USER_AGENT} (iPhone|Android)', $response->getLines()[0]->getLine());
        $this->assertStringContainsString('condition was met', $response->getLines()[0]->getMessage());
        $this->assertTrue($response->getLines()[0]->isMet());
        $this->assertTrue($response->getLines()[0]->isValid());
        $this->assertTrue($response->getLines()[0]->wasReached());
        $this->assertTrue($response->getLines()[0]->isSupported());
    }

    #[Test]
    public function it_allows_for_passing_a_remote_addr(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{REMOTE_ADDR} 10.0.0.1
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with(
                'REMOTE_ADDR',
                '10.0.0.1'
            )
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
        $this->assertEquals('RewriteCond %{REMOTE_ADDR} 10.0.0.1', $response->getLines()[0]->getLine());
        $this->assertStringContainsString('condition was met', $response->getLines()[0]->getMessage());
        $this->assertTrue($response->getLines()[0]->isMet());
        $this->assertTrue($response->getLines()[0]->isValid());
        $this->assertTrue($response->getLines()[0]->wasReached());
        $this->assertTrue($response->getLines()[0]->isSupported());
    }

    #[Test]
    public function it_throws_an_exception_when_we_pass_an_invalid_url(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $this->expectExceptionMessage('url: This is not a valid url');
        $this->expectException(HtaccessException::class);
        $client->test(
            'http:localhost',
            'RewriteRule .* /example-page [L]'
        );
    }

    #[Test]
    public function it_throws_an_exception_when_we_pass_multiple_invalid_fields(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $this->expectExceptionMessage("url: This is not a valid url\nhtaccess: The htaccess content field is required.");
        $this->expectException(HtaccessException::class);
        $client->test(
            'http:localhost',
            ''
        );
    }

    #[Test]
    public function it_can_share_a_test_run(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteRule .* /foo [R]'
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.com',
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

    #[Test]
    public function it_can_share_a_test_run_with_a_referer(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteCond %{HTTP_REFERER} http://example.com
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with('HTTP_REFERER', 'http://example.com')
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.com',
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

    #[Test]
    public function it_can_share_a_test_run_with_a_server_name(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->share(
            'http://localhost',
            'RewriteCond %{SERVER_NAME} example.com
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with('SERVER_NAME', 'example.com')
        );

        $this->assertStringStartsWith(
            'https://htaccess.madewithlove.com',
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

    #[Test]
    public function it_accepts_custom_server_variables_as_well(): void
    {
        $client = new HtaccessClient(
            new Client(),
            new HttpFactory()
        );

        $response = $client->test(
            'http://localhost',
            'RewriteCond %{CUSTOM_VARIABLE} example.com
             RewriteRule .* /example-page [L]',
            ServerVariables::default()->with('CUSTOM_VARIABLE', 'example.com')
        );

        $this->assertEquals(
            'http://localhost/example-page',
            $response->getOutputUrl()
        );
    }
}
