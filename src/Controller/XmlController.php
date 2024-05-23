<?php

namespace App\Controller;

use App\Form\XmlUploadType;
use App\Message\ProcessXmlMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class XmlController extends AbstractController
{
    #[Route('/upload_xml', name: 'upload_xml')]
    public function index()
    {
        return $this->render('xml/index.html.twig');
    }
    #[Route('/store_xml', name: 'store_xml',methods: ['POST'])]
    public function uploadXml(Request $request, EntityManagerInterface $entityManager,MessageBusInterface $bus): Response
    {
//        $form = $this->createForm(XmlUploadType::class);
//        $form->handleRequest($request);

        if ($request->files->has('xml_file')) {
            /** @var UploadedFile $xmlFile */
            $xmlFile = $request->files->get('xml_file');

            if ($xmlFile) {
                $originalFilename = pathinfo($xmlFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $xmlFile->getClientOriginalExtension();

                try {
                    $xmlFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('upload_xml');
                }

                $xmlFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $newFilename;

                $bus->dispatch(new ProcessXmlMessage($xmlFilePath));


                $this->addFlash('success', 'XML data successfully uploaded and stored.');

                return $this->redirectToRoute('upload_xml');
            }
        }

        return $this->render('xml/index.html.twig');
    }
}
