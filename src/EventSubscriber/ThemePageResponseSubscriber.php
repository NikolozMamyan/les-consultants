<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Repository\ThemePageRepository;
use App\Service\AdminDemoDataProvider;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ThemePageResponseSubscriber implements EventSubscriberInterface
{
    private const ROUTE_PAGES = [
        'app_home' => 'home',
        'app_expertises' => 'services',
        'app_cabinet' => 'about',
        'app_contact' => 'contact',
    ];

    private readonly CssSelectorConverter $selectorConverter;

    public function __construct(
        private readonly ThemePageRepository $repository,
        private readonly AdminDemoDataProvider $definitions,
    ) {
        $this->selectorConverter = new CssSelectorConverter();
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'applyTheme'];
    }

    public function applyTheme(ResponseEvent $event): void
    {
        $route = $event->getRequest()->attributes->getString('_route');
        $response = $event->getResponse();
        $contentType = $response->headers->get('Content-Type');
        if (
            str_starts_with($route, 'admin_')
            || str_starts_with($route, '_')
            || (!$response->isSuccessful() && Response::HTTP_UNPROCESSABLE_ENTITY !== $response->getStatusCode())
            || (null !== $contentType && !str_starts_with($contentType, 'text/html'))
        ) {
            return;
        }

        $slugs = ['header', 'footer'];
        if (isset(self::ROUTE_PAGES[$route])) {
            $slugs[] = self::ROUTE_PAGES[$route];
        }

        $storedPages = [];
        foreach ($this->repository->findBy(['slug' => $slugs]) as $page) {
            $storedPages[$page->getSlug()] = $page;
        }

        $themePages = [];
        foreach ($slugs as $slug) {
            $page = $storedPages[$slug] ?? null;
            if (null !== $page && ([] !== $page->getContent() || [] !== $page->getCarousels())) {
                $themePages[$slug] = $page;
            }
        }

        if ([] === $themePages) {
            return;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            return;
        }

        foreach ($dom->childNodes as $node) {
            if (XML_PI_NODE === $node->nodeType) {
                $dom->removeChild($node);
                break;
            }
        }

        $xpath = new \DOMXPath($dom);
        foreach ($themePages as $slug => $page) {
            $fieldDefinitions = $this->fieldDefinitions($slug);
            foreach ($page->getContent() as $selector => $value) {
                foreach ($this->query($xpath, $selector) as $element) {
                    if (!$element instanceof \DOMElement) {
                        continue;
                    }
                    if ($this->isThemeStatic($element)) {
                        continue;
                    }
                    if ('img' === strtolower($element->tagName)) {
                        $element->setAttribute('src', $value);
                        continue;
                    }
                    $attribute = $fieldDefinitions[$selector]['attribute'] ?? null;
                    if (is_string($attribute)) {
                        $element->setAttribute($attribute, $value);
                        continue;
                    }

                    $this->replaceVisibleText($dom, $element, $value);
                    if ('a' === strtolower($element->tagName)) {
                        $href = $element->getAttribute('href');
                        if (str_starts_with($href, 'mailto:')) {
                            $element->setAttribute('href', 'mailto:'.$value);
                        } elseif (str_starts_with($href, 'tel:')) {
                            $element->setAttribute('href', 'tel:'.preg_replace('/[^+\d]/', '', $value));
                        }
                    }
                }
            }

            foreach ($page->getCarousels() as $selector => $settings) {
                foreach ($this->query($xpath, $selector) as $element) {
                    if ($element instanceof \DOMElement) {
                        $element->setAttribute('data-carousel-interval-value', (string) $settings['interval']);
                        $element->setAttribute('data-carousel-mode-value', $settings['mode']);
                    }
                }
            }
        }

        $response->setContent($dom->saveHTML());
    }

    /** @return iterable<\DOMNode> */
    private function query(\DOMXPath $xpath, string $selector): iterable
    {
        try {
            return $xpath->query($this->selectorConverter->toXPath($selector)) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function replaceVisibleText(\DOMDocument $dom, \DOMElement $element, string $value): void
    {
        $textNodes = $this->textNodes($element);

        if ([] !== $textNodes && $element->childElementCount > 0) {
            $textNodes[0]->nodeValue = $value.' ';
            foreach (array_slice($textNodes, 1) as $node) {
                $node->nodeValue = '';
            }

            return;
        }

        while ($element->firstChild) {
            $element->removeChild($element->firstChild);
        }
        $element->appendChild($dom->createTextNode($value));
    }

    /** @return array<string, array<string, mixed>> */
    private function fieldDefinitions(string $slug): array
    {
        $page = $this->definitions->page($slug);
        $fields = [];

        foreach ($page['sections'] ?? [] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[$field['selector']] = $field;
            }
            foreach ($section['carousel']['cards'] ?? [] as $card) {
                foreach ($card['fields'] as $field) {
                    $fields[$field['selector']] = $field;
                }
            }
        }

        return $fields;
    }

    private function isThemeStatic(\DOMElement $element): bool
    {
        $node = $element;
        while ($node instanceof \DOMElement) {
            if ($node->hasAttribute('data-theme-static')) {
                return true;
            }
            $node = $node->parentNode;
        }

        return false;
    }

    /** @return list<\DOMText> */
    private function textNodes(\DOMNode $parent): array
    {
        $nodes = [];
        foreach ($parent->childNodes as $node) {
            if ($node instanceof \DOMText && '' !== trim($node->nodeValue)) {
                $nodes[] = $node;
            } elseif ($node->hasChildNodes()) {
                array_push($nodes, ...$this->textNodes($node));
            }
        }

        return $nodes;
    }
}
