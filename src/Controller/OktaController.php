<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class OktaController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Link to this controller to start the "connect" process
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/okta", name="connect_okta_start", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('okta')
            ->redirect(['openid email profile'])
        ;
    }

    /**
     * @param EntityManagerInterface $entityManager
     *
     * @Route("/connect/okta", name="connect_okta_delete", methods={"DELETE"})
     *
     * @return JsonResponse
     */
    public function deleteAction(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $user->setOktaId(null);

        $this->denyAccessUnlessGranted('edit', $user);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'success' => false,
                    'content' => $this->translator->trans('error_delete_okta_connection', [], 'errors')
                ]
            );
        }
        return new JsonResponse(
            [
                'success' => true,
                'content' => $this->translator->trans('okta_connection_deleted', [], 'messages')
            ]
        );
    }

    /**
     * After going to okta, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/okta/check", name="connect_okta_check")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        return $this->redirectToRoute('app_index');
    }
}
