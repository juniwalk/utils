<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Interfaces\Currency;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use Nette\Application\UI\Link;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Nette\Utils\Html as NetteHtml;

final class Html extends NetteHtml
{
	public const TRANSLATION_REGEX = '/^(?:[a-z0-9-_]+\.){1,}(?:[a-z0-9-_]+)$/i';

	public static bool $disableTranslation = false;
	private static ?Translator $translator = null;


	public static function setTranslator(Translator $translator): void
	{
		static::$translator = $translator;
	}


	public function addText(mixed $text, mixed ...$args): static
	{
		if (is_string($text) && sizeof($args) > 0) {
			$text = sprintf($text, ...$args);
		}

		return parent::addText($text);
	}


	public function insert(?int $index, HtmlStringable|string|null $child, bool $replace = false): static
	{
		if ($child === null) {
			return $this;
		}

		return parent::insert($index, $child, $replace);
	}


	public function hasAnyClassOf(string ...$class): bool
	{
		return (bool) array_intersect_key(
			array_fill_keys($class, true),
			$this->getClass() ?? [],
		);
	}


	public static function highlight(mixed $content, Color $color = Color::Primary, bool $translate = true): self
	{
		return static::el('strong')->addText(static::translate($content, $translate))
			->addClass($color->for('text'));
	}


	public static function subtext(mixed $content, Color $color = Color::Secondary, bool $translate = true): self
	{
		return static::el('i')->addText(static::translate($content, $translate))
			->addClass($color->for('text'));
	}


	public static function badge(
		string $content,
		Color $color = Color::Secondary,
		string $icon = null,
		bool $translate = true,
		bool $isPill = false
	): self {
		$content = static::translate($content, $translate);
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
		bool $translate = true,
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
			$translate,
			$isPill
		);
	}


	public static function price(
		float|int $amount,
		Currency $unit,
		int $decimals = 2,
		bool $isColoredBySign = false
	): self {
		$value = Format::price($amount, $unit, $decimals);
		$color = $unit->color();

		if ($isColoredBySign && $amount > 0) {
			$color = Color::Success;
		}

		if ($isColoredBySign && $amount < 0) {
			$color = Color::Danger;
		}

		return self::badge($value, $color, isPill: true, translate: false);
	}


	public static function icon(string $icon, bool $fixedWidth = false, Color $color = null): self
	{
		static $types = ['fa', 'fas', 'fab', 'far', 'fi'];

		$html = static::el('i')->addClass($icon);
		$html->addClass($color?->for('text'));

		if (!$html->hasAnyClassOf(...$types)) {
			$html->addClass('fas');
		}

		if ($fixedWidth && !Strings::match($icon, '/fa-fw/i')) {
			$html->addClass('fa-fw');
		}

		return $html;
	}


	public static function option(
		string $label,
		mixed $value,
		string $content = null,
		string $icon = null,
		Color $color = null,
		bool $translate = true,
	): self {
		$content = static::translate($content, $translate);
		$labelHtml = static::translate($label, $translate);
		$label = Strings::stripHtml($labelHtml);

		if (!$content && $label <> $labelHtml) {
			$content = $labelHtml;
		}

		return Html::el('option', $label)->value($value)
			->data('color', $color?->for('text'))
			->data('content', $content)
			->data('icon', $icon);
	}


	public static function optionEnum(LabeledEnum $enum, bool $badge = false, bool $translate = true): self
	{
		$option = Html::option(
			value: $enum->value,
			label: $enum->label(),
			icon: $enum->icon(),
			color: $enum->color(),
			translate: $translate,
		);

		if ($badge === true) {
			$badge = Html::badgeEnum($enum, $translate);
			$option->data('content', $badge);
		}

		return $option;
	}


	public static function link(string $label, Link|string $href = null, self|string $icon = null, bool $translate = true): self
	{
		$label = static::translate($label, $translate);
		$link = static::el('a')->setHref($href);

		if (!empty($icon)) {
			if (!$icon instanceof self) {
				$icon = static::icon($icon, true);
			}

			$link->addHtml($icon)->addText(' ');
		}

		return $link->addHtml($label);
	}


	public static function code(string $content, string $title = null, bool $allowCopy = false): self
	{
		$code = static::el('code', $content);

		if ($allowCopy) {
			$code->data('copy', $content);
		}

		if (!empty($title)) {
			$code->setTitle($content);
			$code->setText($title);
		}

		return $code;
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


	public static function progressBar(float $percent, Color $color): self
	{
		$percent = max(0, min(100, round($percent, 0)));

		$progressBar = static::el('div class="progress-bar progress-bar-striped"')
			->addStyle('width: '.$percent.'%')
			->addClass($color->for('bg'));

		return static::el('div class="progress"')
			->addHtml($progressBar);
	}


	private static function translate(mixed $text, bool $translate = true): ?string
	{
		if (!$translate || static::$disableTranslation || !static::$translator) {
			return (string) $text;
		}

		if (!$text || !Strings::match($text, static::TRANSLATION_REGEX)) {
			return (string) $text;
		}

		return static::$translator->translate($text);
	}
}
