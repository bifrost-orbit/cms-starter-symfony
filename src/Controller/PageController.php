<?php

namespace App\Controller;

use BifrostOrbit\Cms\CacheTags;
use BifrostOrbit\Cms\Client;
use BifrostOrbit\Cms\Preview;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class PageController extends AbstractController
{
    #[Route('/{path}', name: 'cms_page', requirements: ['path' => '.*'], methods: ['GET'], priority: -100)]
    public function show(Request $request, Client $cms, TagAwareCacheInterface $cmsCache, string $path = ''): Response
    {
        $path = '/' . $path;

        $token = $request->query->getString('preview');
        if ($token !== '') {
            $claims = $cms->verifyPreviewToken($token);
            if ($claims === null) {
                return new Response('That preview link is invalid or has expired', Response::HTTP_UNAUTHORIZED);
            }
            $response = new RedirectResponse(Preview::redirectPath($path, $claims));
            $response->headers->setCookie(Cookie::create('cms_preview', '1', secure: true, sameSite: Cookie::SAMESITE_NONE));

            return $response;
        }

        $preview = $request->cookies->get('cms_preview') === '1';
        $tag = CacheTags::page((string) $cms->defaultRegion, $path);
        $result = $preview
            ? $cms->resolve($path, ['preview' => true])
            : $cmsCache->get(CacheTags::safe($tag), function (ItemInterface $item) use ($cms, $path, $tag) {
                $item->expiresAfter(60);
                $item->tag(CacheTags::safeAll([CacheTags::ALL, $tag]));

                return $cms->resolve($path);
            });

        $response = match ($result['kind']) {
            'redirect' => new RedirectResponse($result['to'], $result['status']),
            'gone' => throw new GoneHttpException(),
            'notFound' => throw new NotFoundHttpException(),
            'page' => $this->render('page.html.twig', ['page' => $result['page'], 'preview' => $preview]),
        };

        return $preview ? $response->setPrivate()->setMaxAge(0) : $response->setPublic()->setMaxAge(60);
    }
}
