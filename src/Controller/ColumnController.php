<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ColumnController extends AbstractController
{
    /**
     * @Route("/column", name="column")
     */
    public function index()
    {
        return $this->render('column/index.html.twig', [
            'controller_name' => 'ColumnController',
        ]);
    }
}
