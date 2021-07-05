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
        $sprawy = $sprawaRepository->findAll();
        return $this->render('sprawa/index.html.twig', [
            'sprawy' => $sprawy,
        ]);
    }
    /**
     * @Route("/indexAjax", name="sprawa_indexAjax", methods={"GET","POST"})
     */
    public function indexAjax(SprawaRepository $sr, Request $request): ?Response
    {
        $fraza = $request->query->get("fraza");
        // $kontrahenci = $sr->WyszukajPoFragmencieNazwy($fraza);
        $sprawy = $sr->wyszukajPoFragmentachWyrazuOpisu($fraza);
        // $sprawy = $sr->findAll();
        
        $response = $this->render('sprawa/listaSpraw.html.twig',[
            'sprawy' => $sprawy,
            // 'kontrahent_id' => -1,
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
    }
    /**
     * @Route("/indexAjaxSelect2", name="sprawa_indexAjax_select2", methods={"GET","POST"})
     */
    public function indexAjaxSelect2(SprawaRepository $kr, Request $request): ?Response
    {
        $fraza = $request->query->get("fraza");
        if(!$fraza)$fraza = '';
        $pozycje = $kr->wyszukajPoFragmentachWyrazuOpisu($fraza);
        foreach($pozycje as $p)$p->NazwePobierzzopisu();
        
        $response = $this->render('listaZajaxDlaSelect2.html.twig',[
            'pozycje' => $pozycje,
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
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
        $pisma = $sprawa->getDokumenty();
        foreach($pisma as $p)
        {
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $p->getId(), 'numerStrony' => 1 ]));
        }
        return $this->render('sprawa/show.html.twig', [
            'sprawa' => $sprawa,
            'pisma' => $pisma,
            'pismo_id' => -1,
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
            $em = $this->getDoctrine()->getManager();
            foreach($sprawa->NiepotrzebneWyrazy() as $n)
            $em->remove($n);
            $em->flush();

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
