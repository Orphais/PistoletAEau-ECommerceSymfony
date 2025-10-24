<?php

namespace App\DataFixtures;

use App\Entity\OrderItem;
use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Image;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->addProvider(new FakerPicsumImagesProvider($this->faker));

    }
    public function load(ObjectManager $manager): void
    {
        // Categories
        $categories = [];
        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setName($this->faker->word());
            $category->setDescription($this->faker->sentence());

            $categories[] = $category;
            $manager->persist($category);
        }

        // Products
        $products = [];
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName($this->faker->word());
            $product->setDescription($this->faker->sentence());
            $product->setPrice($this->faker->randomFloat(2, 1, 100));
            $product->setCategory($this->faker->randomElement($categories));

            if ($i % 3 == 0) {
                $product->setStock(0);
                $product->setStatus(\App\Enum\ProductStatus::OUT_OF_STOCK);
            } else {
                $product->setStock($this->faker->numberBetween(1, 100));

                if ($this->faker->boolean()) {
                    $product->setStatus(\App\Enum\ProductStatus::AVAILABLE);
                } else {
                    $product->setStatus(\App\Enum\ProductStatus::PREORDER);
                }
            }

            $products[] = $product;
            $manager->persist($product);
        }

        // Images
        $images = [];
        foreach ($products as $product) {
            $image = new Image();
            $image->setUrl($this->faker->imageUrl());
            $image->setProduct($product);

            $images[] = $image;
            $manager->persist($image);
        }

        // Address
        $addresses = [];
        for ($i = 0; $i < 10; $i++) {
            $address = new Address();
            $address->setStreet($this->faker->streetAddress());
            $address->setCity($this->faker->city());
            $address->setPostalCode($this->faker->postcode());
            $address->setCountry($this->faker->country());

            $addresses[] = $address;
            $manager->persist($address);
        }

        // Users
        $users = [];
        $admin = new User();
        $admin->setEmail("admin@tiramisou.com");
        $admin->setFirstName("Admin");
        $admin->setLastName("Admin");
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($this->faker->email());
            $user->setFirstName($this->faker->firstName());
            $user->setLastName($this->faker->lastName());
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );
            $user->setRoles(['ROLE_USER']);

            $user->setAddress($addresses[$i]);

            $users[] = $user;
            $manager->persist($user);
        }

        // Orders
        $orders = [];
        for ($i = 0; $i < 50; $i++) {
            $order = new Order();
            $order->setCustomer($this->faker->randomElement($users));
            $order->setReference("commande - " . "$i");
            $order->setStatus($this->faker->randomElement([
                \App\Enum\OrderStatus::IN_PREPARATION,
                \App\Enum\OrderStatus::SHIPPED,
                \App\Enum\OrderStatus::DELIVERED,
                \App\Enum\OrderStatus::CANCELLED,
            ]));

            $orders[] = $order;
            $manager->persist($order);
        }

        // Order items
        $orderItems = [];
        for ($i = 0; $i < 100; $i++) {
            $orderItem = new OrderItem();
            $product = $this->faker->randomElement($products);
            $orderItem->setProduct($product);
            $orderItem->setPurchaseOrder($this->faker->randomElement($orders));
            $orderItem->setQuantity($this->faker->numberBetween(1, 5));
            $orderItem->setProductPrice($product->getPrice() + $this->faker->randomFloat(null, -2, 2));

            $orderItems[] = $orderItem;
            $manager->persist($orderItem);
        }

        $manager->flush();
    }
}
