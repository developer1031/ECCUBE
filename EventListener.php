<?php
namespace Plugin\Efo;

use Eccube\Application;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class EventListener
{
    /**
     * @var \Eccube\Application
     */
    protected $app;

    protected $responsePayload;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onRenderProductDetailBefore(FilterResponseEvent $event)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $event->getResponse()->getContent());

        $xpath = new \DOMXPath($doc);

        $script = $doc->createElement('script');
        $script->setAttribute('src', $this->app['config']['efo_assets_urlpath'] . '/product-detail.js');
        $body = $xpath->query('body')->item(0);
        $body->appendChild($script);

        $event->getResponse()->setContent($doc->saveHTML());
    }

    public function onRouteShoppingLoginResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        /** @var \Plugin\Efo\Service\ConfigService $configService */
        $configService = $this->app['eccube.plugin.efo.service.config'];
        $dest = $configService->getShoppingLoginDestination();

        // デフォルト
        if (!$dest || $response->isRedirect()) {
            return;
        }

        $response = $this->app->redirect($this->app->url($dest[0], $dest[1]));
        $event->setResponse($response);
    }

    public function onRouteShoppingNonmemberResponse(FilterResponseEvent $event)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $event->getResponse()->getContent());

        $xpath = new \DOMXPath($doc);

        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $this->app['eccube.plugin.efo.service.customer_property'];

        $Properties = $customerPropertyService->all();

        foreach ($Properties as $Property) {
            if (!$Property->isEnabled()) {
                $forms = $xpath->query('//*[@id="detail_box__' . $Property->getProperty() . '"]');

                foreach ($forms as $form) {
                    $style = 'display: none;' . $form->getAttribute('style');
                    $form->setAttribute('style', $style);
                    // $form->parentNode->removeChild($form);
                }
            }
        }


        $scripts = array(
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/js/languages/jquery.validationEngine-ja.js',
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/js/jquery.validationEngine.js',
            $this->app['config']['efo_assets_urlpath'] . '/autoKana/jquery.autoKana.js',
            $this->app['config']['efo_assets_urlpath'] . '/shopping-nonmember.js',
        );

        $body = $xpath->query('body')->item(0);

        foreach ($scripts as $src) {
            $script = $doc->createElement('script');
            $script->setAttribute('src', $src);
            $body->appendChild($script);
        }


        $styles = array(
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/css/validationEngine.jquery.css',
            $this->app['config']['efo_assets_urlpath'] . '/shopping-nonmember.css',
        );

        $head = $xpath->query('head')->item(0);

        foreach ($styles as $href) {
            $link = $doc->createElement('link');
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('href', $href);
            $head->appendChild($link);
        }


        $event->getResponse()->setContent($doc->saveHTML());
    }

    public function onRouteEntryResponse(FilterResponseEvent $event)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $event->getResponse()->getContent());

        $xpath = new \DOMXPath($doc);

        /** @var \Plugin\Efo\Service\CustomerPropertyService $customerPropertyService */
        $customerPropertyService = $this->app['eccube.plugin.efo.service.customer_property'];

        $Properties = $customerPropertyService->all();

        foreach ($Properties as $Property) {
            if (!$Property->isEnabled()) {
                $forms = $xpath->query('//*[@id="top_box__' . $Property->getProperty() . '"]');

                foreach ($forms as $form) {
                    $style = 'display: none;' . $form->getAttribute('style');
                    $form->setAttribute('style', $style);
                    // $form->parentNode->removeChild($form);
                }

                $forms = $xpath->query('//*[@id="confirm_box__' . $Property->getProperty() . '"]');

                foreach ($forms as $form) {
                    $style = 'display: none;' . $form->getAttribute('style');
                    $form->setAttribute('style', $style);
                    // $form->parentNode->removeChild($form);
                }
            }
        }


        $scripts = array(
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/js/languages/jquery.validationEngine-ja.js',
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/js/jquery.validationEngine.js',
            $this->app['config']['efo_assets_urlpath'] . '/autoKana/jquery.autoKana.js',
            $this->app['config']['efo_assets_urlpath'] . '/entry.js',
        );

        $body = $xpath->query('body')->item(0);

        foreach ($scripts as $src) {
            $script = $doc->createElement('script');
            $script->setAttribute('src', $src);
            $body->appendChild($script);
        }


        $styles = array(
            $this->app['config']['efo_assets_urlpath'] . '/jQuery-Validation-Engine/css/validationEngine.jquery.css',
            $this->app['config']['efo_assets_urlpath'] . '/entry.css',
        );

        $head = $xpath->query('head')->item(0);

        foreach ($styles as $href) {
            $link = $doc->createElement('link');
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('href', $href);
            $head->appendChild($link);
        }


        $event->getResponse()->setContent($doc->saveHTML());
    }
}
