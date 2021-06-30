<?php

namespace App\Controller;

use App\Entity\Sprawa;
use App\Form\SprawaType;
use App\Repository\SprawaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sprawa")
 */
class SprawaController extends AbstractController
{
    /**
     * @Route("/", name="sprawa_index", methods={"GET"})
     */
    public function index(SprawaRepository $sprawaRepository): Response
    {
        return $this->render('sprawa/index.html.twig', [
            'sprawy' => $sprawaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="sprawa_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $sprawa = new Sprawa();
        $form = $this->createForm(SprawaType::class, $sprawa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sprawa);
            $entityManager->flush();

            return $this->redirectToRoute('sprawa_index');
        }

        return $this->render('sprawa/new.html.twig', [
            'sprawa' => $sprawa,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sprawa_show", methods={"GET"})
     */
    public function show(Sprawa $sprawa): Response
    {
        return $this->render('sprawa/show.html.twig', [
            'sprawa' => $sprawa,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sprawa_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sprawa $sprawa): Response
    {
        $form = $this->createForm(SprawaType::class, $sprawa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sprawa_index');
        }

        return $this->render('sprawa/edit.html.twig', [
            'sprawa' => $sprawa,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sprawa_delete", methods={"POST"})
     */
    public function delete(Request $request, Sprawa $sprawa): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sprawa->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sprawa);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sprawa_index');
    }
}
