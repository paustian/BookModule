<?php

declare(strict_types=1);

/*
 * This file is part of the BookModule package.
 *
 * Copyright is the same as Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Paustian\BookModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Paustian\BookModule\Form\ConfigType;
use Zikula\ThemeModule\Engine\Annotation\Theme;


/**
 * Class ConfigController.
 *
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Template("@PaustianBookModule//Config/config.html.twig")
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function config(
        Request $request
    )
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException($this->trans('You do not have permission to access the Book admin interface.'));
        }
        $dataValues = $this->getVars();

        $form = $this->createForm(ConfigType::class, $dataValues);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                // save modvars
                $this->setVars($formData);
                $this->addFlash('status', 'Done! Configuration updated.');
            }
            // redirecting prevents values from being repeated in the form
            return $this->redirectToRoute('paustianbookmodule_config_config');
        }

        return [
            'form' => $form->createView()
        ];
    }
}