<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function getProducts(): array
    {
        if (empty($this->query)) {
            return [];
        }

        if (strlen($this->query) < 2) {
            return [];
        }

        return $this->productRepository->searchByName($this->query);
    }
}
