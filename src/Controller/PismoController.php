<?php

namespace App\Controller;

use App\Entity\Pismo;
use App\Form\PismoLadowaniePdfType;
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
use Symfony\Component\String\Slugger\SluggerInterface;

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
        $pisma = $pismoRepository->findAll();
        $foldPdf = $this->getParameter('sciezka_do_zarejestrowanych');
        $entityManager = $this->getDoctrine()->getManager();
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            $entityManager->persist($p);
        }
        //jeśli data jest w bazie, to nic nie robi
        $entityManager->flush();
            
        return $this->render('pismo/index.html.twig', [
            'pisma' => $pisma,
        ]);;
    }
    /**
     *@Route("/noweIndex", name="pismo_nowe_index", methods={"GET","POST"}) 
     */
    public function NoweIndex(Request $request, SluggerInterface $slugger): Response
    {
        /*
        $skany = [];
        for($i = 7 ; $i < 12 ; $i++)$skany[] = 'skan'.$i.'.pdf';
        */
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = new Pismo;
        $form = $this->createForm(PismoLadowaniePdfType::class, $pismo);//
        $form->handleRequest($request);
        
        $pnp = new PracaNaPlikach;
        if ($form->isSubmitted() && $form->isValid()) {
            $plikiPdf= $form->get('plik')->getData();
            foreach($plikiPdf as $plikPdf)
            {
                if ($plikPdf) {
                    $originalFilename = pathinfo($plikPdf->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    
                    // $newFilename = $safeFilename.'-'.uniqid().'.'.$plikPdf->guessExtension();
                    $newFilename = $safeFilename.'.'.$plikPdf->guessExtension();//bez unikalnego numeru
                    // echo $newFilename;
                    // Move the file to the directory where brochures are stored
                    try {
                        $plikPdf->move(
                            $this->getParameter('sciezka_do_skanow'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
            }

            // return $this->redirectToRoute('pismo_nowe_ze_skanu',['nazwa' => $newFilename, 'numerStrony'=> 1]);
            // return 
        }
        return $this->render('pismo/noweIndex.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'),'pdf'),
            'form' => $form->createView(),
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

        if ($form->isSubmitted() && $form->isValid() && $pnp->PrzeniesPlikiPdfiPodgladu($this->getParameter('sciezka_do_zarejestrowanych'),$pismo)) {

            // $strona = $form->get('strona')->getData();
            // $kierunek = $form->get('kierunek')->getData();
            // $pismo->UstalStroneNaPodstawieKierunku($strona,$kierunek);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_nowe_index');
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
        // ;
        $pismo = $pismoRepository->find($id);
        $pismo->UstalStroneIKierunek();
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        $pisma = $pismoRepository->findAll();
        foreach($pisma as $p)$p->UstalStroneIKierunek();
       
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
        $pismo->UstalStroneIKierunek();
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $this->getDoctrine()->getManager()->flush();
            $pnp = new PracaNaPlikach;
            $pnp->UaktualnijNazwyPlikowPodgladu($pismo);
            return $this->redirectToRoute('pismo_show',['id'=>$pismo->getId(), 'numerStrony' => $numerStrony]);
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

