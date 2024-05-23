<?php

namespace App\MessageHandler;

use App\Entity\Product;
use App\Message\ProcessXmlMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
#[AsMessageHandler]
class ProcessXmlMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(ProcessXmlMessage $message)
    {
        $filePath = $message->getFilePath();
        $this->processXmlFile($filePath);
    }

    private function processXmlFile(string $filePath): void
    {
        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent);
        foreach ($xml->product as $product_row) {
            $product = new Product();
            $product->setTitle((string)$product_row->name);
            $product->setDescription((string)$product_row->description);
            $product->setWeight((string)$product_row->weight);
            $product->setCategory((string)$product_row->category);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();

    }
}