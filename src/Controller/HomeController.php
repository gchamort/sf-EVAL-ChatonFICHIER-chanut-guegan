<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $finder=new Finder();
        $finder->directories()->in("../public/photos");

        //création d'un formulaire ajout de dossier
        $form = $this->createFormBuilder()
                ->add('folderName', TextType::class, ['label'=>'Ajouter une categorie '])
                ->add('ajouter', SubmitType::class, ['label'=>'Envoyer',
                                                                'attr'=>["class"=>'btn btn-primary']])
                ->getForm();

        //reception d'un nouveau nom de dossier
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $data=$form->getData();

            // création nouveau dossier à partir du nom reçu
            $filesystem = new Filesystem();
            try {
                $filesystem->mkdir('../public/photos/'.$data["folderName"]);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating directory at ".$exception->getPath();
            }
        }

        return $this->render('home/index.html.twig', [
            "dossiers"=>$finder,
            "formulaire"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/chatons/{nomDuDossier}", name="dossier")
     */
    public function afficherDossier($nomDuDossier, Request $request)
    {
        $path = "../public/photos/".urldecode($nomDuDossier);

        $finder=new Finder();
        $finder->files()->in($path);

        //création d'un formulaire
        $form = $this->createFormBuilder()
                ->add('photo', FileType::class, ['label'=>'Ajouter un chaton'])
                ->add('ajouter', SubmitType::class, ['label'=>'Envoyer',
                                                                'attr'=>["class"=>'btn btn-primary']])
                ->getForm();

        // si il y a une réponse au formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $data=$form->getData();

            $filesystem = new Filesystem();
            $newFileName = $data["photo"]->getClientOriginalName();

            // détection si le fichier existe déjà
            // if ($filesystem->exists($path."/".$newFileName))
            // {
                // $extension_pos = strrpos($filename, '.');
                // $thumb = substr($filename, 0, $extension_pos) . '_thumb' . substr($filename, $extension_pos);
                // return new Response($newFileName);


                // uniqid("") .
            // }
            // else
            // {

            // }
            // création d'un nouveau fichiers à partir de celui reçu

            try {
                $data["photo"]->move($path, $newFileName);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating directory at ".$exception->getPath();
            }
        }

        return $this->render('home/afficherDossier.html.twig', [
            "nomDuDossier"=>urldecode($nomDuDossier),
            "fichiers"=>$finder,
            "formulaire"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/remove/{dirName}/{fileName}", name="removeFile")
     */
    public function removeFile($dirName, $fileName)
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists('../public/photos/'.$dirName.'/'.$fileName))
        {
            try {
                $filesystem->remove('../public/photos/'.$dirName.'/'.$fileName);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }

        $response = $this->forward('App\Controller\HomeController::afficherDossier', ['nomDuDossier' => $dirName]);
        return $response;
    }

    /**
     * @Route("/remove/{dirName}", name="removeDir")
     */
    public function removeDir($dirName)
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists('../public/photos/'.$dirName))
        {
            try {
                $filesystem->remove('../public/photos/'.$dirName);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }

        $response = $this->forward('App\Controller\HomeController::index', []);
        return $response;
    }
}
