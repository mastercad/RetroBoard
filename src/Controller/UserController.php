<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('index');

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * Undocumented function.
     *
     * @Route("/user/show/{id}", name="user_profile", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(int $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $this->denyAccessUnlessGranted('edit', $user);

        return $this->render('user/edit.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/show", name="user_profile", methods={"GET"})
     */
    public function ownProfile()
    {
        return $this->show($this->getUser()->getId());
    }

    /**
     * @Route("/user/upload/avatar", name="user_avatar_upload", methods={"POST"})
     */
    public function avatarUpload(Request $request, EntityManagerInterface $entityManager)
    {
//        $uploadDir = __DIR__.'/../../var/tmp/upload';
        $uploadDir = __DIR__.'/../../public/upload';
        $id = $request->get('id');

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $this->denyAccessUnlessGranted('edit', $user);

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

//        $filename = $_FILES['file']['name'];
        $filename = $user->getId().'_tmp';

        $publicPathName = '/upload/'.$filename;

        /* Getting File size */
        $filesize = $_FILES['file']['size'];

        /* Location */
        $location = $uploadDir.'/'.$filename;

        /* Upload file */
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $src = 'default.png';

            // checking file is image or not
            if (is_array(getimagesize($location))) {
                $src = $location;
            }

            $returnArray = ['name' => $filename, 'size' => $filesize, 'src' => $publicPathName];
        }

        return new JsonResponse($returnArray);
    }

    /**
     * @Route("/user/edit", name="user_edit", methods={"POST"})
     */
    public function edit(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request)
    {
        $userRequest = $request->request->get('user');
        $user = $this->getDoctrine()->getRepository(User::class)->find($userRequest['id']);

        if (!$user instanceof User) {
            return new JsonResponse(
                [
                    'code' => 500,
                    'success' => false,
                    'content' => $this->translator->trans('something_went_wrong', [], 'errors')
                ]
            );
        }

        $this->denyAccessUnlessGranted('edit', $user);

        $emailConstraint = new Email();
        $emailConstraint->message = $this->translator->trans('email_invalid', [], 'errors');

        $errorList = $validator->validate(
            $userRequest['email'],
            $emailConstraint
        );

        if (0 < count($errorList)) {
//                throw new InvalidArgumentException("Email ".$email." invalid!");
            return new JsonResponse(
                [
                'code' => 500,
                'success' => false,
                'content' => $this->translator->trans('email_invalid', [], 'errors')
                ]
            );
        }

        $user->setEmail($userRequest['email']);
        $user->setColor($userRequest['color']);
        $user->setName($userRequest['name']);

        $userAbsoluteAvatarPath = __DIR__.'/../../public/upload';

        if (preg_match('/_tmp$/', $userRequest['avatar_path'])) {
            rename($userAbsoluteAvatarPath.'/'.$user->getId().'_tmp', $userAbsoluteAvatarPath.'/'.$user->getId());
            $userRequest['avatar_path'] = '/upload/'.$user->getId();
        }

        $user->setAvatarPath($userRequest['avatar_path']);

        if (empty($user->getAvatarPath())
            && file_exists($userAbsoluteAvatarPath.'/'.$user->getId())
        ) {
            unlink($userAbsoluteAvatarPath.'/'.$user->getId());
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'code' => 500,
                    'success' => false,
                    'content' => $this->translator->trans('something_went_wrong', [], 'errors')
                ]
            );
        }

        return new JsonResponse([
            'code' => 201,
            'success' => true,
            'content' => $this->translator->trans('successfully_saved', [], 'messages')
        ]);
    }
}
