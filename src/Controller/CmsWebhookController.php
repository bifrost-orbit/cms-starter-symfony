<?php

namespace App\Controller;

use BifrostOrbit\Cms\CacheTags;
use BifrostOrbit\Cms\Webhook;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class CmsWebhookController
{
    #[Route('/cms-webhook', name: 'cms_webhook', methods: ['POST'])]
    public function __invoke(
        Request $request,
        TagAwareCacheInterface $cmsCache,
        #[Autowire(env: 'CMS_WEBHOOK_SECRET')] string $secret,
    ): Response {
        $message = Webhook::parse($secret, $request->headers->all(), $request->getContent());

        if ($message === null) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        $tags = Webhook::tagsForEvent($message['event'], $message['data']);
        $cmsCache->invalidateTags(CacheTags::safeAll($tags));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
