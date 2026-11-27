<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Entity\User;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Security\Permission\BookPermission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Pas de #[IsGranted] sur la classe : il s'ajouterait à ceux des méthodes
// et bloquerait le créateur d'un livre (addedBy) sur edit.
#[Route('/admin/books')]
class BookController extends AbstractController
{
    #[Route('', name: 'app_admin_books', methods: ['GET'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('admin/book/index.html.twig', [
            'books' => $bookRepository->findAllWithAuthorsAndGenres(),
        ]);
    }

    #[Route('/new', name: 'app_admin_book_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_LIBRARIAN')]
    public function new(Request $request, EntityManagerInterface $em, #[CurrentUser] User $user): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book, [
            'can_change_availability' => $this->isGranted(BookPermission::CHANGE_AVAILABILITY, $book),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setAddedBy($user);
            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Book created.');

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('admin/book/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_book_edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    #[IsGranted(BookPermission::EDIT_DETAILS, subject: 'book')]
    public function edit(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        // Book non nullable → id inconnu = 404 automatique
        $form = $this->createForm(BookType::class, $book, [
            'can_change_availability' => $this->isGranted(BookPermission::CHANGE_AVAILABILITY, $book),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas de persist : le livre vient de la base, Doctrine le suit déjà
            $em->flush();

            $this->addFlash('success', 'Book updated.');

            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('admin/book/edit.html.twig', [
            'form' => $form,
            'book' => $book,
        ]);
    }
}
