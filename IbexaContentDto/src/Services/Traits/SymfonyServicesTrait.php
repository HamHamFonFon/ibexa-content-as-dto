<?php

namespace Kaliop\IbexaContentDto\Services\Traits;

trait SymfonyServicesTrait
{

    protected RouterInterface $router;
    protected TranslatorInterface $translator;

    #[Required]
    public function injectRouter(RouterInterface $router): void
    {
        $this->router = $this->router ?: $router;
    }

    #[Required]
    public function injectTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $this->translator ?: $translator;
    }
}