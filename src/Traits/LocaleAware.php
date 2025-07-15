<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Contributte\Translation\LocalesResolvers\Session as SessionResolver;
use Nette\Application\Attributes\Persistent;
use Nette\Localization\Translator;

trait LocaleAware
{
	use RedirectAjaxHandler;

	#[Persistent]
	public string $locale;

	private SessionResolver $sessionResolver;
	private Translator $translator;


	public function injectSessionResolver(SessionResolver $sessionResolver): void
	{
		$this->sessionResolver = $sessionResolver;
	}

	public function injectTranslator(Translator $translator): void
	{
		$this->translator = $translator;
	}

	public function injectLocaleAwareness(): void
	{
		$this->onRender[] = function(): void {
			$locale = $this->translator->getLocale();
			$locales = [];
	
			foreach ($this->translator->getLocalesWhitelist() as $lang) {
				$locales[$lang] = 'enum.locale.'.$lang;
			}
	
			if (!isset($locales[$locale])) {
				$locale = $this->translator->getDefaultLocale();
			}
	
			$template = $this->getTemplate();
			$template->add('locales', $locales);
			$template->add('locale', $locale);
		};
	}


	public function handleLocale(string $lang): void
	{
		$this->sessionResolver->setLocale($this->locale = $lang);
		$this->redirect('this');
	}


	public function getTranslator(): Translator
	{
		return $this->translator;
	}
}
