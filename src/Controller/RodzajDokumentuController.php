<?php

namespace App\Controller;

use App\Entity\RodzajDokumentu;
use App\Form\RodzajDokumentuType;
use App\Repository\RodzajDokumentuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rodzaj/dokumentu")
 */
class RodzajDokumentuController extends AbstractController
{
    /**
     * @Route("/", name="rodzaj_dokumentu_index", methods={"GET"})
     */
    public function index(RodzajDokumentuRepository $rodzajDokumentuRepository): Response
    {
        return $this->render('rodzaj_dokumentu/index.html.twig', [
            'rodzaj_dokumentus' => $rodzajDokumentuRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="rodzaj_dokumentu_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $rodzajDokumentu = new RodzajDokumentu();
        $form = $this->createForm(RodzajDokumentuType::class, $rodzajDokumentu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rodzajDokumentu);
            $entityManager->flush();

            return $this->redirectToRoute('rodzaj_dokumentu_index');
        }

        return $this->render('rodzaj_dokumentu/new.html.twig', [
            'rodzaj_dokumentu' => $rodzajDokumentu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="rodzaj_dokumentu_show", methods={"GET"})
     */
    public function show(RodzajDokumentu $rodzajDokumentu): Response
    {
        return $this->render('rodzaj_dokumentu/show.html.twig', [
            'rodzaj_dokumentu' => $rodzajDokumentu,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="rodzaj_dokumentu_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, RodzajDokumentu $rodzajDokumentu): Response
    {
        $form = $this->createForm(RodzajDokumentuType::class, $rodzajDokumentu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('rodzaj_dokumentu_index');
        }

        return $this->render('rodzaj_dokumentu/edit.html.twig', [
            'rodzaj_dokumentu' => $rodzajDokumentu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="rodzaj_dokumentu_delete", methods={"POST"})
     */
    public function delete(Request $request, RodzajDokumentu $rodzajDokumentu): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rodzajDokumentu->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($rodzajDokumentu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('rodzaj_dokumentu_index');
    }
}
