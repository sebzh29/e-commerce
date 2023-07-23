<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/list.html.twig', compact('produits'));
    }

    #[Route('/ajout', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //On crée un new produit
        $product = new Products();

        //On crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        //On traite la requete du formulaire
        $productForm->handleRequest($request);

        //On verifie si le form est soumis et valide
        if($productForm->isSubmitted() && $productForm->isValid()){
            //On recupere les images
            $images = $productForm->get('images')->getData();

            foreach ($images as $image){
                //On definit le dossier de destination
                $folder = 'products';

                //On appelle le service d ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            //On genere le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //On arrondie le prix
//            $prix = $product->getPrice() * 100;
//            $product->setPrice($prix);

            //On stock
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            //On redirige
            return $this->redirectToRoute('admin_products_index');

        }
        return $this->render('admin/products/add.html.twig', [
            'productForm'=>$productForm->createView()
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(
        Products $product,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        //On verifie si l'user peut editer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        //On divise le prix par 100
//        $prix = $product->getPrice() / 100;
//        $product->setPrice($prix);

        //On crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        //On traite la requete du formulaire
        $productForm->handleRequest($request);

        //On verifie si le form est soumis et valide
        if($productForm->isSubmitted() && $productForm->isValid()){

            //On recupere les images
            $images = $productForm->get('images')->getData();

            foreach ($images as $image){
                //On definit le dossier de destination
                $folder = 'products';

                //On appelle le service d ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            //On genere le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //On arrondie le prix
//            $prix = $product->getPrice() * 100;
//            $product->setPrice($prix);

            //On stock
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            //On redirige
            return $this->redirectToRoute('admin_products_index');

        }
        return $this->render('admin/products/edit.html.twig', [
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        //On verifie si l'user peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);
        return $this->render('admin/products/list.html.twig');
    }

    #[Route('/suppression/image/{id}', name: 'delete_image', methods:
        ['DELETE'])]
    public function deleteImage(
        Images $image,
        Request $request,
        EntityManagerInterface $em,
        PictureService $pictureService
    ): JsonResponse
    {
        // On recupere le contenu de la requete
        $data = json_decode(($request->getContent()), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data ['_token'])){
            // Le token csrf est valide
            // On recupere le nom de l'image
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                // On supprime l'image de la BDD
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);

            }
            // La suppression a echoue
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

       return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
