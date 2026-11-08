<?php

namespace App\Twig;

use BifrostOrbit\Cms\BlockRenderer;
use BifrostOrbit\Cms\Client;
use BifrostOrbit\Cms\Preview;
use BifrostOrbit\Cms\Seo;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;

final class CmsExtension
{
    private const TEMPLATES = [
        'hero' => 'blocks/hero.html.twig',
        'featureGrid' => 'blocks/feature_grid.html.twig',
        'featureItem' => 'blocks/feature_item.html.twig',
        'action' => 'blocks/action.html.twig',
    ];

    public function __construct(private readonly Client $cms)
    {
    }

    #[AsTwigFunction('cms_blocks', needsEnvironment: true, isSafe: ['html'])]
    public function blocks(Environment $twig, ?iterable $blocks): string
    {
        return $this->renderer($twig)->render($blocks ?? []);
    }

    #[AsTwigFunction('cms_block', needsEnvironment: true, isSafe: ['html'])]
    public function block(Environment $twig, ?array $block): string
    {
        return $this->renderer($twig)->renderOne($block);
    }

    #[AsTwigFunction('cms_head', isSafe: ['html'])]
    public function head(array $page): string
    {
        return Seo::headTags($page) . Seo::jsonLd($page);
    }

    #[AsTwigFunction('cms_bridge', isSafe: ['html'])]
    public function bridge(): string
    {
        return Preview::bridgeScript($this->cms);
    }

    private function renderer(Environment $twig): BlockRenderer
    {
        return new BlockRenderer(
            array_map(fn (string $template) => fn (array $block) => $twig->render($template, ['block' => $block]), self::TEMPLATES),
            fn (array $block) => $twig->render('blocks/unknown.html.twig', ['block' => $block]),
        );
    }
}
