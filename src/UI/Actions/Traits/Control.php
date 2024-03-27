<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\UI\Actions\Traits;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Html;
use JuniWalk\Utils\UI\Actions\Component;
use Nette\Localization\Translator;

trait Control
{
	private ?Translator $translator = null;
	private Html|Component $control;
	private ?string $title = null;
	private ?Html $icon = null;


	public function setTranslator(?Translator $translator): void
	{
		$this->translator = $translator;
	}


	public function setTitle(?string $title): static
	{
		$this->title = $title;
		return $this;
	}


	public function getTitle(): ?string
	{
		return $this->title;
	}


	public function setIcon(string $icon, bool $fixedWidth = true, Color $color = null): static
	{
		$this->icon = Html::icon($icon, $fixedWidth, $color);
		return $this;
	}


	public function getIcon(): ?Html
	{
		return $this->icon;
	}


	public function hasClass(string $name): bool
	{
		$class = $this->control->getClass() ?? '';

		if (is_array($class)) {
			$class = implode(' ', $class);
		}

		return str_contains($class, $name);
	}


	public function addClass(string $class): static
	{
		$this->control->addClass($class);
		return $this;
	}


	public function setClass(string $class): static
	{
		$this->control->setClass($class);
		return $this;
	}


	public function addAttributes(array $attributes): static
	{
		$this->control->addAttributes($attributes);
		return $this;
	}


	public function getControl(): Html
	{
		$control = clone $this->control;

		if ($control instanceof Component) {
			$control = clone $control->create();
		}

		if ($this->icon instanceof Html) {
			$control->addHtml($this->icon)->addText(' ');
		}

		if ($title = $this->title) {
			$title = $this->translator?->translate($title) ?? $title;
			$control->setTitle($title);
		}

		return $control;
	}
}
