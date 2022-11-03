<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Latte;

use JuniWalk\Utils\Enums\Currency;
use JuniWalk\Utils\Html;
use Latte\Extension;

class LatteExtension extends Extension
{
	/**
	 * @return array<string, callable>
	 */
	public function getFilters(): array
	{
		return [
			'price' => $this->filterPrice(...),
		];
	}


	protected function filterPrice(
		?float $amount,
		mixed $currency,
		bool $isColored = true,
		string ...$classes
	): Html {
		return Html::price((float) $amount, Currency::make($currency), isColoredBySign: $isColored)->addClass($classes);
	}
}
