<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\Currency;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use Nette\Localization\Translator;
use Nette\Utils\Html as NetteHtml;

final class Html extends NetteHtml
{
	private static Translator $translator;


	public static function setTranslator(Translator $translator): void
	{
		static::$translator = $translator;
	}


	public function addText($text, mixed ... $args): static
	{
		if (is_string($text) && sizeof($args) > 0) {
			$text = sprintf($text, ... $args);
		}

		return parent::addText($text);
	}


	public static function subtext(string $content, bool $tryTranslate = true): self
	{
		$content = static::translate($content, $tryTranslate);
		return static::el('i class="text-muted"', $content);
	}


	public static function badge(
		string $content,
		Color $color = Color::Secondary,
		string $icon = null,
		bool $tryTranslate = true,
		bool $isPill = false
	): self {
		$content = static::translate($content, $tryTranslate);
		$badge = static::el('span class="badge"')
			->addClass($color->for('badge'));

		if ($isPill === true) {
			$badge->addClass('badge-pill');
		}

		if (!empty($icon)) {
			$icon = static::icon($icon, true);
			$badge->addHtml($icon)->addText(' ');
		}

		return $badge->addHtml($content);
	}


	public static function badgeEnum(
		LabeledEnum $enum,
		bool $tryTranslate = true,
		bool $isPill = false
	): self {
		if ($icon = $enum->icon()) {
			$icon = static::icon($icon)->getClass();
			$icon = implode(' ', array_keys($icon));
		}

		return static::badge(
			$enum->label(),
			$enum->color(),
			$icon,
			$tryTranslate,
			$isPill
		);
	}


	/**
	 * @deprecated
	 */
	public static function enumBadge(LabeledEnum $enum): self
	{
		trigger_error('Method '.__METHOD__.' is deprecated use badgeEnum instead', E_USER_DEPRECATED);
		return static::badgeEnum($enum);
	}


	public static function price(
		float $amount,
		Currency $unit,
		int $decimals = 2,
		bool $isColoredBySign = false
	): self {
		$value = Format::currency($amount, $unit, $decimals);
		$color = $unit->color();

		if ($isColoredBySign && $amount > 0) {
			$color = Color::Success;
		}

		if ($isColoredBySign && $amount < 0) {
			$color = Color::Danger;
		}

		return self::badge($value, $color, isPill: true, tryTranslate: false);
	}


	public static function icon(string $icon, bool $fixedWidth = false): self
	{
		$html = static::el('i');

		if (!Strings::match($icon, '/fas|fab|far/i')) {
			$html->addClass('fas');
		}

		if ($fixedWidth && !Strings::match($icon, '/fa-fw/i')) {
			$html->addClass('fa-fw');
		}

		return $html->addClass($icon);
	}


	public static function option(
		string $label,
		mixed $value,
		string $content = null,
		string $icon = null,
		Color $color = null,
		bool $tryTranslate = true,
	): self {
		$content = static::translate($content, $tryTranslate);
		$label = static::translate($label, $tryTranslate);

		return Html::el('option', $label)->value($value)
			->data('color', $color?->for('text'))
			->data('content', $content)
			->data('icon', $icon);
	}


	public static function optionEnum(LabeledEnum $enum, bool $tryTranslate = true): self {
		return Html::option(
			value: $enum->value,
			label: $enum->label(),
			icon: $enum->icon(),
			color: $enum->color(),
			tryTranslate: $tryTranslate,
		);
	}


	public static function status(
		mixed $status,
		bool $hasIcons = true,
		bool $hasContent = true,
		bool $isInverse = false,
	): self {
		$content = $status ? 'web.general.yes' : 'web.general.no';
		$color = $status && !$isInverse ? Color::Success : Color::Danger;
		$icon = null;

		if ($hasIcons == true) {
			$icon = $status && !$isInverse ? 'fa-check' : 'fa-times';
		}

		if ($hasContent == false) {
			$content = '';
		}

		return static::badge($content, $color, $icon);
	}


	private static function translate(?string $content, bool $tryTranslate = true): ?string
	{
		if (!$content || !$tryTranslate || !isset(static::$translator)) {
			return $content;
		}

		return static::$translator->translate($content);
	}
}
