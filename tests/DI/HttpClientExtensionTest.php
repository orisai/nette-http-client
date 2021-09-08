<?php declare(strict_types = 1);

namespace Tests\OriNette\HttpClient\DI;

use Nyholm\Psr7\Factory\Psr17Factory;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function dirname;

final class HttpClientExtensionTest extends TestCase
{

	public function testClientWiring(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 2));
		$configurator->setDebugMode(true);

		$configurator->addConfig(__DIR__ . '/wiring.neon');

		$container = $configurator->createContainer();

		self::assertFalse($container->isCreated('httpClient.nyholm.psr17Factory'));
		self::assertFalse($container->isCreated('httpClient.symfony.client'));

		$client = $container->getByType(ClientInterface::class);
		self::assertInstanceOf(Psr18Client::class, $client);
		self::assertSame($client, $container->getService('httpClient.client'));

		self::assertTrue($container->isCreated('httpClient.nyholm.psr17Factory'));
		self::assertTrue($container->isCreated('httpClient.symfony.client'));
		self::assertInstanceOf(HttpClientInterface::class, $container->getService('httpClient.symfony.client'));
	}

	public function testFactoryWiring(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 2));
		$configurator->setDebugMode(true);

		$configurator->addConfig(__DIR__ . '/wiring.neon');

		$container = $configurator->createContainer();

		$factory = $container->getService('httpClient.nyholm.psr17Factory');
		self::assertInstanceOf(Psr17Factory::class, $factory);

		self::assertSame($factory, $container->getService('httpClient.requestFactory'));
		self::assertSame($factory, $container->getByType(RequestFactoryInterface::class));

		self::assertSame($factory, $container->getService('httpClient.responseFactory'));
		self::assertSame($factory, $container->getByType(ResponseFactoryInterface::class));

		self::assertSame($factory, $container->getService('httpClient.serverRequestFactory'));
		self::assertSame($factory, $container->getByType(ServerRequestFactoryInterface::class));

		self::assertSame($factory, $container->getService('httpClient.streamFactory'));
		self::assertSame($factory, $container->getByType(StreamFactoryInterface::class));

		self::assertSame($factory, $container->getService('httpClient.uploadedFileFactory'));
		self::assertSame($factory, $container->getByType(UploadedFileFactoryInterface::class));

		self::assertSame($factory, $container->getService('httpClient.uriFactory'));
		self::assertSame($factory, $container->getByType(UriFactoryInterface::class));
	}

}
