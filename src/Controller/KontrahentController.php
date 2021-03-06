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
            'kontrahents' => $kontrahentRepository->findBy([],['nazwa'=>'ASC']),//All()
        ]);
    }
    /**
     * @Route("/indexAjax", name="kontrahent_indexAjax", methods={"GET","POST"})
     */
    public function indexAjax(KontrahentRepository $kr, Request $request): ?Response
    {
        $fraza = $request->query->get("fraza");
        $kontrahenci = $kr->WyszukajPoFragmencieNazwy($fraza);
        
        $response = $this->render('kontrahent/listaKontrahentow.html.twig',[
            'kontrahents' => $kontrahenci,
            'kontrahent_id' => -1,
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
    }
    /**
     * @Route("/indexAjaxSelect2", name="kontrahent_indexAjax_select2", methods={"GET","POST"})
     */
    public function indexAjaxSelect2(KontrahentRepository $kr, Request $request): ?Response
    {
        $fraza = $request->query->get("fraza");
        if(!$fraza)$fraza = '';
        
        $response = $this->render('listaZajaxDlaSelect2.html.twig',[
            'pozycje' => $kr->WyszukajPoFragmencieNazwy($fraza)
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
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
    public function show($id ,KontrahentRepository $kontrahentRepository, PismoRepository $pr, Request $request): Response
    {
        $kontrahent = $kontrahentRepository->find($id);
        $pisma = $pr->findWszystkiePismaKontrahenta($kontrahent);
        $pismoId = $request->get('pismo_id');
        $numerStrony = $request->get('numerStrony');
        if($numerStrony == null)$numerStrony = 1;
        $pismo = null;
        $sciezkiDlaStron = [];
        $sciezkaPng = '';
        if($pismoId == null)$pismoId = -1;
        else{
            $pismo = $pr->find($pismoId);
            $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
            
            $num = 0;
            foreach($sciezkiDoPodgladow as $sc)
            {
                // echo "\n".$sc;
                $sciezkiDlaStron[] = $this->generateUrl('kontrahent_show',['id'=> $id,'pismo_id'=> $pismoId,'numerStrony' => ++$num ]);
            }
            $sciezkaPng = $sciezkiDoPodgladow[$numerStrony - 1]; 
        }
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            $p->setSciezkaGenerUrl($this->generateUrl('kontrahent_show',['id'=>$id,'pismo_id'=> $p->getId()]));
        }
        return $this->render('kontrahent/show.html.twig', [
            'kontrahents' => $kontrahentRepository->findBy([],['nazwa'=>'ASC']),
            'kontrahent' => $kontrahent,
            'pisma' => $pisma,
            // 'pismo'
            'pismo_id' => $pismoId,
            'sciezki_dla_stron' => $sciezkiDlaStron,
            'numerStrony' => $numerStrony,
            'sciezka_png' => $sciezkaPng,
            'wierszZopcjami' => true,
            'przyciskUsun' => (count($pisma)?"ukryj":"poka??")
        ]);
    }

    /**
     * @Route("/{id}/edit", name="kontrahent_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Kontrahent $kontrahent, PismoRepository $pr): Response
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
            'pisma' => $pr->findWszystkiePismaKontrahenta($kontrahent),
            'pismo_id' => -1,
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
