<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GitHubController extends AbstractController
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
     * @Route("/connect/github", name="connect_github_start", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('github')
            ->redirect()
        ;
    }

    /**
     * @param EntityManagerInterface $entityManager
     *
     * @Route("/connect/github", name="connect_github_delete", methods={"DELETE"})
     *
     * @return JsonResponse
     */
    public function deleteAction(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $user->setGithubId(null);

        $this->denyAccessUnlessGranted('edit', $user);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(['success' => false, 'content' => $this->translator->trans('error_delete_github_connection', [], 'errors')]);
        }
        return new JsonResponse(['success' => true, 'content' => $this->translator->trans('github_connection_deleted', [], 'messages')]);
    }

    /**
     * After going to Github, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     *
     * @Route("/connect/github/check", name="connect_github_check")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        return $this->redirectToRoute('app_index');
    }
}