<?php

namespace Kaliop\IbexaContentDto\Services\Traits;

trait SymfonyServicesTrait
{

    protected RouterInterface $router;
    protected TranslatorInterface $translator;

    /**
     * @required
     * @param RouterInterface $router
     *
     * @return void
     */
    public function injectRouter(RouterInterface $router): void
    {
        $this->router = $this->router ?: $router;
    }

    /**
     * @required
     * @param TranslatorInterface $translator
     *
     * @return void
     */
    public function injectTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $this->translator ?: $translator;
    }
}