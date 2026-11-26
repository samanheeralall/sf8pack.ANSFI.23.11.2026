<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookSearchType;
use App\Repository\BookRepository;
use App\Search\BookSearchCriteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/books')]
final class BookController extends AbstractController
{
    #[Route('', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository, Request $request): Response
    {
        // Non soumis → valeurs par défaut du DTO → liste complète
        $criteria = new BookSearchCriteria();
        $form = $this->createForm(BookSearchType::class, $criteria);
        $form->handleRequest($request);

        $books = $bookRepository->search($criteria);

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(Book $book): Response
    {
        // Doctrine charge le livre via l'id, 404 automatique s'il n'existe pas
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
