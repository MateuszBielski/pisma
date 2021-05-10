<?php

namespace App\Controller;

use App\Entity\Pismo;
use App\Form\PismoType;
use App\Repository\PismoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Imagick;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
        $process = new Process(['pdftopng',  $folder.'zych.pdf', $folder.'zychRozp']);//'-gray',
        // $process->run();
        // echo $process->getOutput();
        

        // $process->wait();


        return $this->render('pismo/index.html.twig', [
            'pismos' => $pismoRepository->findAll(),
        ]);;
    }
    /**
     *@Route("/noweIndex", name="pismo_nowe_index", methods={"GET"}) 
     */
    public function NoweIndex(): Response
    {
        $skany = [];
        for($i = 7 ; $i < 12 ; $i++)$skany[] = 'skan'.$i.'.pdf';
        return $this->render('pismo/noweIndex.html.twig', [
            'skany' => $skany,
            ]);;
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
     * @Route("/{id}", name="pismo_show", methods={"GET"})
     */
    public function show(): Response//Pismo $pismo
    {
        $pismo = new Pismo();
        
        return $this->render('pismo/show.html.twig', [
            'pismo' => $pismo,
            'sciezka_do_img' => $this->getParameter('sciezka_do_skanow').'zychRozp-000001.png'
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pismo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Pismo $pismo): Response
    {
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pismo_index');
        }

        return $this->render('pismo/edit.html.twig', [
            'pismo' => $pismo,
            'form' => $form->createView(),
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

