<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Utils\Traits;

use Contributte\Translation\LocalesResolvers\Session as SessionResolver;
use Nette\Application\Attributes\Persistent;
use Nette\Http\Session;
use Nette\Localization\Translator;

trait LocaleAware
{
	use RedirectAjaxHandler;

	#[Persistent]
	public ?string $locale = null;

	/** @var array<string, string> */
	private array $locales = [];

	private Session $session;
	private SessionResolver $sessionResolver;
	private Translator $translator;


	public function injectLocaleAwareness(
		Session $session,
		SessionResolver $sessionResolver,
		Translator $translator,
	): void {
		foreach ($translator->getLocalesWhitelist() as $lang) {
			$this->locales[$lang] = 'enum.locale.'.$lang;
		}

		$this->session = $session;
		$this->sessionResolver = $sessionResolver;
		$this->translator = $translator;

		$this->onStartup[] = $this->localeAllowed(...);
		$this->onRender[] = $this->localeAware(...);
	}


	public function handleLocale(string $lang): void
	{
		if (!isset($this->locales[$lang])) {
			$lang = null;
		}

		if ($this->session->isStarted()) {
			$this->sessionResolver->setLocale($lang);
		}

		$this->redirect('this', ['locale' => $lang]);
	}


	public function getLocale(): string
	{
		return $this->locale ?? $this->translator->getLocale();
	}


	public function getTranslator(): Translator
	{
		return $this->translator;
	}


	private function localeAllowed(): void
	{
		$locale = $this->getLocale();

		if (!isset($this->locales[$locale])) {
			$this->handleLocale($locale);
		}
	}


	private function localeAware(): void
	{
		$template = $this->getTemplate();
		$template->add('locale', $this->getLocale());
		$template->add('locales', $this->locales);
	}
}
