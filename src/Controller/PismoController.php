<?php

namespace App\Controller;

use App\Entity\Pismo;
use App\Form\PismoType;
use App\Repository\PismoRepository;
use App\Service\PracaNaPlikach;
use App\Service\UruchomienieProcesu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Imagick;
use Symfony\Component\Process\Exception\ProcessFailedException;


/**
 * @Route("/pismo")
 */
class PismoController extends AbstractController
{
    /**
     * @Route("/", name="pismo_index", methods={"GET"})
     */
    public function index(PismoRepository $pismoRepository): ?Response
    {
        /*
        $imagick = new Imagick();
        $imagick->readImage("/var/www/html/skany/współczynnikiLabWarsztatyArchiwumZPO.pdf");
        // echo __DIR__;
        print_r($imagick->getSize()) ;
        // $imagick->setImageResolution ( 100, 2000 );
        // print_r($imagick->identifyImage(true));
        $imagick->writeImages('/var/www/html/skany/obraz.jpg',false);
        */
        $folder = $this->getParameter('sciezka_do_skanow');
        // echo $folder;
        // $process = new Process(['pdftopng',  $folder.'zych.pdf', $folder.'zychRozp']);//'-gray',
        // $process->run();
        // echo $process->getOutput();
        

        // $process->wait();


        return $this->render('pismo/index.html.twig', [
            'pisma' => $pismoRepository->findAll(),
        ]);;
    }
    /**
     *@Route("/noweIndex", name="pismo_nowe_index", methods={"GET"}) 
     */
    public function NoweIndex(): Response
    {
        /*
        $skany = [];
        for($i = 7 ; $i < 12 ; $i++)$skany[] = 'skan'.$i.'.pdf';
        */
        $pnp = new PracaNaPlikach;
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        
        return $this->render('pismo/noweIndex.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'),'pdf'),
            ]);
    }

    /**
     * @Route("/new", name="pismo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pismo = new Pismo();
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_index');
        }

        return $this->render('pismo/new.html.twig', [
            'pismo' => $pismo,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/noweZeSkanu/{nazwa}/{numerStrony}", name="pismo_nowe_ze_skanu", methods={"GET","POST"})
     */
    public function noweZeSkanu(Request $request, string $nazwa, $numerStrony = 1): Response
    {
        $pnp = new PracaNaPlikach;
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->getParameter('sciezka_do_skanow'),$nazwa);
        $pnp->setUruchomienieProcesu(new UruchomienieProcesu);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->getParameter('sciezka_do_png'),$pismo);
        
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $pnp->RejestrujPismo($this->getParameter('sciezka_do_zarejestrowanych'),$pismo)) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_index');
        }
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        return $this->render('pismo/noweZeSkanu.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'),'pdf'),
            'pismo' => $pismo,
            'form' => $form->createView(),
            'sciezki_png_dla_stron' => $sciezkiDoPodgladow,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'numerStrony' => $numerStrony,
        ]);
    }

    /**
     * @Route("/{id}/{numerStrony}", name="pismo_show", methods={"GET"})
     */
    public function show(int $id,int $numerStrony, PismoRepository $pismoRepository): Response//Pismo $pismo
    {
        // $pnp = new PracaNaPlikach;
        $pismo = $pismoRepository->find($id);
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();

       
        return $this->render('pismo/show.html.twig', [
            'pismo' => $pismo,
            'pisma' => $pismoRepository->findAll(),
            'sciezki_png_dla_stron' => $sciezkiDoPodgladow,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'numerStrony' => $numerStrony,
        ]);
    }

    /**
     * @Route("/edit/{id}/{numerStrony}", name="pismo_edit", methods={"GET","POST"})
     */
    public function edit($numerStrony, Request $request, Pismo $pismo): Response
    {
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pismo_index');
        }
        // $numerStrony = 1;
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        return $this->render('pismo/edit.html.twig', [
            'pismo' => $pismo,
            'form' => $form->createView(),
            // 'pisma' => $pismoRepository->findAll(),
            'sciezki_png_dla_stron' => $sciezkiDoPodgladow,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'numerStrony' => $numerStrony,
        ]);
    }

    /**
     * @Route("/{id}", name="pismo_delete", methods={"POST"})
     */
    public function delete(Request $request, Pismo $pismo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pismo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pismo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pismo_index');
    }
}

