<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Strings;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class FormatTest extends TestCase
{
	private JsonSerializable $jsonSerialize;
	private Stringable $stringable;
	private stdClass $object;

	public function setUp() {
		$this->object = (object) ['type' => 'stdClass'];
		$this->jsonSerialize = new class implements JsonSerializable {
			public function jsonSerialize(): mixed { return ['type' => 'class']; }
		};

		$this->stringable = new class implements Stringable {
			public function __toString(): string { return 'class'; }
		};
	}

	public function tearDown() {
		unset($this->jsonSerialize);
		unset($this->stringable);
		unset($this->object);
	}


	public function testClassName(): void
	{
		$class = new UnexpectedValueException;

		Assert::same('unexpected-value', Format::className($class));
		Assert::same('format-test', Format::className($this));
		Assert::same('format', Format::className($this, suffix: 'Test'));
		Assert::same('format', Format::className($this, suffix: 'test'));
	}


	public function testCasing(): void
	{
		$value = Strings::webalize('variable case');

		Assert::same('variable_case', Format::snakeCase($value));
		Assert::same('variable-case', Format::kebabCase($value));
		Assert::same('variableCase', Format::camelCase($value));
		Assert::same('VariableCase', Format::pascalCase($value));
	}


	public function testSerializable(): void
	{
		$items = [
			'stdClass'		=> [
				'actual'	=> $this->object,
				'expect'	=> $this->object,
			],
			'datetime'		=> [
				'actual'	=> DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 00:00:00'),
				'expect'	=> '2024-01-01T00:00:00+00:00',
			],
			'jsonSerialize'	=> [
				'actual'	=> $this->jsonSerialize,
				'expect'	=> ['type' => 'class'],
			],
			'stringable'	=> [
				'actual'	=> $this->stringable,
				'expect'	=> 'class',
			],
			'enum'			=> [
				'actual'	=> Casing::Snake,
				'expect'	=> Casing::Snake->value,
			],
		];

		foreach ($items as $testCase => ['actual' => $actual, 'expect' => $expect]) {
			Assert::same($expect, Format::serializable($actual), $testCase);
		}
	}


	public function testStringify(): void
	{
		$items = [
			'stdClass'		=> [
				'actual'	=> $this->object,
				'expect'	=> '{"type":"stdClass"}',
			],
			'datetime'		=> [
				'actual'	=> DateTime::createFromFormat('Y-m-d H:i:s', '2024-01-01 00:00:00'),
				'expect'	=> '2024-01-01T00:00:00+00:00',
			],
			'jsonSerialize'	=> [
				'actual'	=> $this->jsonSerialize,
				'expect'	=> '{"type":"class"}',
			],
			'stringable'	=> [
				'actual'	=> $this->stringable,
				'expect'	=> 'class',
			],
			'enum'			=> [
				'actual'	=> Casing::Snake,
				'expect'	=> Casing::Snake->value,
			],
		];

		foreach ($items as $testCase => ['actual' => $actual, 'expect' => $expect]) {
			Assert::same($expect, Format::stringify($actual), $testCase);
		}
	}


	public function testNumeric(): void
	{
		Assert::type('int', Format::numeric(5.0));
		Assert::type('int', Format::numeric('5.0'));
		Assert::type('int', Format::numeric('5,0'));
		Assert::type('float', Format::numeric(5.5));
		Assert::type('float', Format::numeric('5.5'));
		Assert::type('float', Format::numeric('5,5'));
		Assert::type('float', Format::numeric('5 000,50'));
		Assert::type('null', Format::numeric('test'));
		Assert::type('string', Format::numeric('test', strict: false));
	}
}

(new FormatTest)->run();
