<?php declare(strict_types = 1);

namespace Tests\OriNette\HttpClient\Unit;

use OriNette\HttpClient\ConfigurableRequestFactory;
use PHPUnit\Framework\TestCase;

final class ConfigurableRequestFactoryTest extends TestCase
{

	public function test(): void
	{
		$factory = new ConfigurableRequestFactory([]);
		$request = $factory->createRequest('GET', 'https://example.com');
		self::assertSame(
			[
				'Host' => ['example.com'],
			],
			$request->getHeaders(),
		);

		$factory = new ConfigurableRequestFactory([
			'a' => 'b',
			'c' => ['d', 'e'],
		]);
		$request = $factory->createRequest('GET', 'https://example.com');
		self::assertSame(
			[
				'Host' => ['example.com'],
				'a' => ['b'],
				'c' => ['d', 'e'],
			],
			$request->getHeaders(),
		);
	}

}
