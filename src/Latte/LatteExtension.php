<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Latte;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Currency;
use JuniWalk\Utils\Enums\LabeledEnum;
use JuniWalk\Utils\Format;
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
			'phone' => $this->filterPhone(...),
			'badge' => $this->filterBadge(...),
			'price' => $this->filterPrice(...),
			'icon' => $this->filterIcon(...),
		];
	}


	protected function filterPhone(
		?string $phone,
	): ?string {
		return Format::phoneNumber($phone);
	}


	protected function filterIcon(
		?string $icon,
		bool $fixedWidth = true,
		string ...$classes,
	): ?Html {
		if (!isset($icon)) {
			return null;
		}

		return Html::icon($icon, $fixedWidth)->addClass($classes);
	}


	protected function filterBadge(
		string|LabeledEnum $content,
		string|Color $color = Color::Secondary,
		string $icon = null,
	): Html {
		if ($content instanceof LabeledEnum) {
			return Html::badgeEnum($content);
		}

		return Html::badge($content, Color::make($color), $icon);
	}


	protected function filterPrice(
		?float $amount,
		mixed $currency,
		bool $isColored = true,
		string ...$classes,
	): Html {
		return Html::price((float) $amount, Currency::make($currency), isColoredBySign: $isColored)->addClass($classes);
	}
}
