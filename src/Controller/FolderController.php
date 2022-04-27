<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use App\Service\PracaNaPlikach;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/folder")
 */
class FolderController extends AbstractController
{
    /**
     * @Route("/", name="folder_index", methods={"GET"})
     */
    public function index(FolderRepository $folderRepository): Response
    {
        return $this->render('folder/index.html.twig', [
            'folders' => $folderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/odczytZawartosciAjax", name="folder_odczytZawartosci_ajax", methods={"GET"})
     */
    public function odczytZawartosciAjax(Request $request, PracaNaPlikach $pnp)
    {
        $sciezkaWpisana = $request->query->get("fraza");

        $sciezkaOdcietaDoFolderu = $pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezkaWpisana);

        $szerokoscElementuPix = $request->query->get("rozmiar");
        $dlugoscNazwy = intval($szerokoscElementuPix / 14);
        $pisma = $pnp->UtworzPismaZfolderu($sciezkaOdcietaDoFolderu);
        $folder = new Folder();
        $folder->setSciezkaMoja($sciezkaOdcietaDoFolderu);
        $sciezkaTuJestemHtml = $this->renderView($folder->getSzablonSciezkaTuJestem(), [
            'sciezkaTuJestem' => $folder->SciezkaTuJestem(),
        ]);
        $listaPlikow = $this->renderView('pismo/listaNier.html.twig', [
            'pisma' => $pisma,
            'dlugoscNazwy' => $dlugoscNazwy,
        ]);
        $response = new Response();
        $response->setContent(
            json_encode([
                'listaPlikow' => $listaPlikow,
                'sciezkaTuJestemHtml' => $sciezkaTuJestemHtml,
            ])
        );
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }
    /**
     * @Route("/nazwyFolderowDlaAutocomplete/{id}", name="nazwy_folderow_dla_autocomplete", methods={"GET"})
     * @ParamConverter("folder", isOptional="true")
     */
    public function nazwyFolderowDlaAutocomplete(Request $request,Folder $folder = null, PracaNaPlikach $pnp)
    {
        $sciezka = $request->query->get("sciezkaWpisana");
        $poprzedniaOdcietaSciezka = $request->query->get("sciezkaOdcietaDoFolderuDotychczas");
        $sciezkaOdcietaDoFolderu = $pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezka);
        // $zmienilSieFolder = $sciezkaOdcietaDoFolderu != $poprzedniaOdcietaSciezka;
        // if(!$zmienilSieFolder)return new Response();
        $sciezkaPozostaloscDoWyszukania = $pnp->CzescSciezkiZaFolderem($sciezka, $sciezkaOdcietaDoFolderu);
        $foldery = $pnp->PobierzWszystkieNazwyFolderowZfolderu($sciezkaOdcietaDoFolderu);

        $folderyPasujaceDoFrazy = $pnp->FitrujFolderyPasujaceDoFrazy($foldery, $sciezkaPozostaloscDoWyszukania);
        $pelneFoldery = rtrim($sciezkaOdcietaDoFolderu, "/");
        $folder = $folder?? new Folder();
        $folder->setSciezkaMoja($pelneFoldery);
        $szablon = $folder->getSzablonSciezkaTuJestem();
        $sciezkaTuJestemHtml = $this->renderView($szablon, [
            'folder' => $folder,
            'sciezkaTuJestem' => $folder->SciezkaTuJestem(),
        ]);
        $response = new Response();
        $response->setContent(
            json_encode([
                'foldery' => $folderyPasujaceDoFrazy,
                'pelneFoldery' => $pelneFoldery,
                'sciezkaTuJestemHtml' => $sciezkaTuJestemHtml,
            ])
        );
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }

    /**
     * @Route("/sciezkaZawartoscFolderuAjax", name="sciezka_zawartosc_folderu_ajax", methods={"GET"})
     */
    public function sciezkaZawartoscFolderuAjax(Request $request, PracaNaPlikach $pnp)
    {
        $pisma = [];
        $dlugoscNazwy = 40;
        $folder = new Folder();
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);
        $folder->setSciezkaMoja("/jakas/dziwna/sciezka");

        $response = $this->renderForm('folder/_sciezkaZawartoscFolderu.html.twig', [
            'pisma' => $pisma,
            'dlugoscNazwy' => $dlugoscNazwy,
            'form' => $form,
            'sciezkaTuJestem' => $folder->SciezkaTuJestem(),
        ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return $response;
    }

    /**
     * @Route("/new/{sciezka}", name="folder_new", methods={"GET","POST"})
     */
    public function new(Request $request, PracaNaPlikach $pnp, string $sciezka = "/"): Response
    {
        $folder = new Folder();
        $folder->SciezkePobierzZadresuIkonwertuj($sciezka);
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);
        $sciezkaOdczytu = $folder->getSciezkaMoja() ?? "/";
        $pisma = $pnp->UtworzPismaZfolderu($sciezkaOdczytu);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($folder);
            $entityManager->flush();

            return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('folder/new.html.twig', [
            'folder' => $folder,
            'form' => $form,
            'pisma' => $pisma,
            'sciezkaTuJestem' => $folder->SciezkaTuJestem(),
        ]);
    }

    /**
     * @Route("/{id}", name="folder_show", methods={"GET"})
     */
    public function show(Folder $folder): Response
    {
        return $this->render('folder/show.html.twig', [
            'folder' => $folder,
        ]);
    }

    /**
     * @Route("/{id}/edit/{sciezka}", name="folder_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Folder $folder, PracaNaPlikach $pnp, string $sciezka = ""): Response
    {
        if (strlen($sciezka))
            $folder->SciezkePobierzZadresuIkonwertuj($sciezka);
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);
        $sciezkaOdczytu = $folder->getSciezkaMoja() ?? "/";
        $pisma = $pnp->UtworzPismaZfolderu($sciezkaOdczytu);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('folder/edit.html.twig', [
            'folder' => $folder,
            'form' => $form,
            'pisma' => $pisma,
            'sciezkaTuJestem' => $folder->SciezkaTuJestem()
        ]);
    }

    /**
     * @Route("/{id}", name="folder_delete", methods={"POST"})
     */
    public function delete(Request $request, Folder $folder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $folder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($folder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
    }
}
