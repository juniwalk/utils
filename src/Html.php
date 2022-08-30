<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\LabelledEnum;
use Nette\Utils\Html as NetteHtml;

final class Html extends NetteHtml
{
	/**
	 * @param  string  $content
	 * @param  Color  $color
	 * @param  string  $icon
	 * @return static
	 */
	public static function badge(string $content, Color $color = Color::Secondary, string $icon = null): self
	{
		$badge = static::el('span class="badge"')
			->addClass($color->for('badge'));

		if (!empty($icon)) {
			$icon = static::icon($icon, true);
			$badge->addHtml($icon)->addText(' ');
		}

		return $badge->addHtml(Strings::lower($content));
	}


	/**
	 * @param  float  $value
	 * @param  string  $unit
	 * @param  int  $decimals
	 * @return static
	 */
	public static function price(float $value, string $unit, int $decimals = 2): self
	{
		$value = Format::price($value, $unit, $decimals);
		return self::badge($value)->addClass('badge-pill');
	}


	/**
	 * @param  string  $icon
	 * @param  bool  $fixedWidth
	 * @return static
	 */
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


	/**
	 * @param  LabelledEnum  $enum
	 * @return static
	 */
	public static function enumBadge(LabelledEnum $enum): self
	{
		$icon = $enum->icon();

		if ($icon && !Strings::match($icon, '/^fa-/i')) {
			$icon = 'fa-'.$icon;
		}

		return static::badge($enum->label(), $enum->color(), $icon);
	}
}
