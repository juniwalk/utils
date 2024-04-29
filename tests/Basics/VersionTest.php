<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Enums\Strategy;
use JuniWalk\Utils\Version;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class VersionTest extends TestCase
{
	private Version $version;

	public function setUp() {
		$this->version = new Version('v1.0.14');
	}

	public function tearDown() {
		unset($this->version);
	}


	public function testParsing(): void
	{
		Assert::same(1, $this->version->getMajor());
		Assert::same(0, $this->version->getMinor());
		Assert::same(14, $this->version->getPatch());
		Assert::null($this->version->getBuild());
		Assert::false($this->version->isPreRelease());
	}


	public function testAdvancing(): void
	{
		$this->version->advance(Strategy::Minor, 'alpha');

		Assert::same(1, $this->version->getMajor());
		Assert::same(1, $this->version->getMinor());
		Assert::same(0, $this->version->getPatch());
		Assert::same(1, $this->version->getBuild());
		Assert::true($this->version->isPreRelease());
	}


	public function testComparing(): void
	{
		$version = new Version('v1.1.0-alpha.1');

		Assert::false($version->compare($this->version, '<'));
		Assert::false($version->compare($this->version, '='));
		Assert::true($version->compare($this->version, '>'));

		Assert::same(1, $version->compare($this->version));
	}


	public function testFormatting(): void
	{
		Assert::same('v1.0.x', $this->version->format(Version::Dev));
		Assert::same('v1.0.14', $this->version->format(Version::Tag));
		Assert::same('1.0.14', $this->version->format(Version::SemVer));
		Assert::same('v1.0', $this->version->format('v%M.%m'));
	}
}

(new VersionTest)->run();
