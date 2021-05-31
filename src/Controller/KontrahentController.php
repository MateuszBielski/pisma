<?php

namespace App\Controller;

use App\Entity\Kontrahent;
use App\Form\KontrahentType;
use App\Repository\KontrahentRepository;
use App\Repository\PismoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/kontrahent")
 */
class KontrahentController extends AbstractController
{
    /**
     * @Route("/", name="kontrahent_index", methods={"GET"})
     */
    public function index(KontrahentRepository $kontrahentRepository): Response
    {
        $kontrahenci = [];
        for($i = 0; $i < 12 ; $i++)
        {
            $k = new Kontrahent;
            $k->setNazwa("nad_odb".$i);
            $kontrahenci[] = $k;
        }
        return $this->render('kontrahent/index.html.twig', [
            'kontrahents' => $kontrahentRepository->findAll()//$kontrahenci//,
        ]);
    }

    /**
     * @Route("/new", name="kontrahent_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $kontrahent = new Kontrahent();
        $form = $this->createForm(KontrahentType::class, $kontrahent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($kontrahent);
            $entityManager->flush();

            return $this->redirectToRoute('kontrahent_index');
        }

        return $this->render('kontrahent/new.html.twig', [
            'kontrahent' => $kontrahent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="kontrahent_show", methods={"GET"})
     */
    public function show($id ,KontrahentRepository $kontrahentRepository, PismoRepository $pr): Response
    {
        $kontrahent = $kontrahentRepository->find($id);
        $pisma = $pr->findWszystkiePismaKontrahenta($kontrahent);
        foreach($pisma as $p)$p->UstalStroneIKierunek();
        return $this->render('kontrahent/show.html.twig', [
            'kontrahents' => $kontrahentRepository->findAll(),
            'kontrahent' => $kontrahent,
            'pisma' => $pisma,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="kontrahent_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Kontrahent $kontrahent): Response
    {
        $form = $this->createForm(KontrahentType::class, $kontrahent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('kontrahent_index');
        }

        return $this->render('kontrahent/edit.html.twig', [
            'kontrahent' => $kontrahent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="kontrahent_delete", methods={"POST"})
     */
    public function delete(Request $request, Kontrahent $kontrahent): Response
    {
        if ($this->isCsrfTokenValid('delete'.$kontrahent->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($kontrahent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('kontrahent_index');
    }
}
