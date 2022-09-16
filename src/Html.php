<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Currency;
use JuniWalk\Utils\Enums\LabeledEnum;
use Nette\Localization\Translator;
use Nette\Utils\Html as NetteHtml;

final class Html extends NetteHtml
{
	private static Translator $translator;


	public static function setTranslator(Translator $translator): void
	{
		static::$translator = $translator;
	}


	public static function badge(
		string $content,
		Color $color = Color::Secondary,
		string $icon = null,
		bool $tryTranslate = true
	): self {
		$content = static::translate($content, $tryTranslate);
		$badge = static::el('span class="badge"')
			->addClass($color->for('badge'));

		if (!empty($icon)) {
			$icon = static::icon($icon, true);
			$badge->addHtml($icon)->addText(' ');
		}

		return $badge->addHtml(Strings::lower($content));
	}


	public static function price(float $value, Currency $unit, int $decimals = 2): self
	{
		$value = Format::price($value, $unit, $decimals);
		return self::badge($value, $unit->color())->addClass('badge-pill');
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


	public static function enumBadge(LabeledEnum $enum): self
	{
		$icon = $enum->icon();

		if ($icon && !Strings::match($icon, '/^fa-/i')) {
			$icon = 'fa-'.$icon;
		}

		return static::badge($enum->label(), $enum->color(), $icon);
	}


	private static function translate(string $content, bool $tryTranslate = true): string
	{
		if (!$tryTranslate || !isset(static::$translator)) {
			return $content;
		}

		return static::$translator->translate($content);
	}
}
