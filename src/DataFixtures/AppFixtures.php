<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $slugify = new Slugify();

        // Users
        for ($index = 1; $index <= 5; $index++) {
            $user = new User();
            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setSlug($slugify->slugify($user->getFirstName(). ' '.$user->getLastName()))
                ->setEmail($user->getLastName().'@gmail.com')
                ->setPassword('test')
                ->setPicture('400x400.jpg')
                ->setAddress($faker->address())
                ->setZipcode($faker->postcode())
                ->setPhone($faker->e164PhoneNumber())
            ;
            $manager->persist($user);
        }

        // Categories
        $categories = [];
        for ($index = 1; $index <= 5; $index++) {
            $category = new Category();
            $category
                ->setName($faker->sentence())
                ->setSlug($slugify->slugify($category->getName()))
            ;

            $manager->persist($category);
            $categories[] = $category;
        }

        // Products
        for ($index = 1; $index <= 20; $index++) {
            $product = new Product();
            $product
                ->setName($faker->sentence())
                ->setSlugProduct($slugify->slugify($product->getName()))
                ->setCategory($categories[mt_rand(0, count($categories) - 1)])
                ->setContent($faker->sentences(3, true))
                ->setInStock(array_rand([true, false]))
                ->setPrice(mt_rand(100, 10000) / 100)
                ->setPicture('400x400.jpg')
            ;

            $manager->persist($product);
        }

        $manager->flush();
    }
}
