<?php
/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\SnippetBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Component\Content\Compat\Structure;
use Sulu\Component\Content\Compat\StructureManagerInterface;
use Sulu\Component\Rest\RequestParametersTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles snippet types and defaults.
 */
class SnippettypesController extends Controller implements ClassResourceInterface
{
    use RequestParametersTrait;

    /**
     * Returns all snippet types.
     *
     * @return JsonResponse
     */
    public function cgetAction(Request $request)
    {
        $defaults = $this->getBooleanRequestParameter($request, 'defaults');
        $webspaceKey = $this->getRequestParameter($request, 'webspace', $defaults);

        $defaultSnippetManager = $this->get('sulu_snippet.default_snippet.manager');

        /** @var StructureManagerInterface $structureManager */
        $structureManager = $this->get('sulu.content.structure_manager');
        $types = $structureManager->getStructures(Structure::TYPE_SNIPPET);

        $templates = [];
        foreach ($types as $type) {
            $template = [
                'template' => $type->getKey(),
                'title' => $type->getLocalizedTitle($this->getUser()->getLocale()),
            ];

            if ($defaults) {
                $default = $defaultSnippetManager->load($webspaceKey, $type->getKey(), $this->getUser()->getLocale());

                $template['defaultUuid'] = !$default ? null : $default->getUuid();
                $template['defaultTitle'] = !$default ? null : $default->getTitle();
            }

            $templates[] = $template;
        }

        $data = [
            '_embedded' => $templates,
            'total' => count($templates),
        ];

        return new JsonResponse($data);
    }

    /**
     * Save default snippet for given key.
     *
     * @param string $key
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function putDefaultAction($key, Request $request)
    {
        $default = $request->get('default');
        $webspaceKey = $this->getRequestParameter($request, 'webspace', true);

        $type = $this->get('sulu.content.structure_manager')->getStructure($key, Structure::TYPE_SNIPPET);
        $defaultSnippet = $this->get('sulu_snippet.default_snippet.manager')->save(
            $webspaceKey,
            $key,
            $default,
            $this->getUser()->getLocale()
        );

        return new JsonResponse(
            [
                'template' => $type->getKey(),
                'title' => $type->getLocalizedTitle($this->getUser()->getLocale()),
                'defaultUuid' => !$defaultSnippet ? null : $defaultSnippet->getUuid(),
                'defaultTitle' => !$defaultSnippet ? null : $defaultSnippet->getTitle(),
            ]
        );
    }

    /**
     * Remove default snippet for given key.
     *
     * @param string $key
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteDefaultAction($key, Request $request)
    {
        $webspaceKey = $this->getRequestParameter($request, 'webspace', true);

        $type = $this->get('sulu.content.structure_manager')->getStructure($key, Structure::TYPE_SNIPPET);
        $this->get('sulu_snippet.default_snippet.manager')->remove($webspaceKey, $key);

        return new JsonResponse(
            [
                'template' => $type->getKey(),
                'title' => $type->getLocalizedTitle($this->getUser()->getLocale()),
                'defaultUuid' => null,
                'defaultTitle' => null,
            ]
        );
    }
}
