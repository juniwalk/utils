<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Latte;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Currency;
use JuniWalk\Utils\Enums\LabeledEnum;
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
			'badge' => $this->filterBadge(...),
			'price' => $this->filterPrice(...),
		];
	}


	protected function filterBadge(
		string|LabeledEnum $content,
		string $color = Color::Secondary,
		string $icon = null,
	): Html {
		if ($content instanceof LabeledEnum) {
			return Html::enumBadge($content);
		}

		return Html::badge($content, Color::make($color), $icon);
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
