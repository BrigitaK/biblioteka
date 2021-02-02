<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Author;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        // $authors = $this->getDoctrine()
        // ->getRepository(Author::class)
        // ->findAll();
        //rusiavimas
        $authors = $this->getDoctrine()
        ->getRepository(Author::class);
        if('name_az' == $r->query->get('sort')) {
            $authors = $authors->findBy([],['name' => 'asc', 'surname' => 'asc']);
        }
        elseif('name_za' == $r->query->get('sort')) {
            $authors = $authors->findBy([],['name' => 'desc']);
        }
        elseif('surname_az' == $r->query->get('sort')) {
            $authors = $authors->findBy([],['surname' => 'asc']);
        }
        elseif('surname_za' == $r->query->get('sort')) {
            $authors = $authors->findBy([],['surname' => 'desc']);
        }
        else {
            $authors = $authors->findAll();
        }
        
        
        return $this->render('author/index.html.twig', [
            'authors' => $authors,
            'sortBy' => $r->query->get('sort') ?? 'default'
        ]);
    }
    /**
     * @Route("/author/create", name="author_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        return $this->render('author/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }
    /**
     * @Route("/author/store", name="author_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $author = New Author;
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $errors = $validator->validate($author);


        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('author_create');
        }

        //susikuriam manager
        $entityManager = $this->getDoctrine()->getManager();
        //i ta manager persiunciam author
        $entityManager->persist($author);
        //ir viska irasom i duomenu baze
        $entityManager->flush();

        //grazinu redirect
        return $this->redirectToRoute('author_index');
    }

     /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"})
     */
    public function edit(int $id): Response
    {
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        return $this->render('author/edit.html.twig', [
            'author' => $author,
        ]);
    }
       /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"})
     */
    public function update(Request $r, $id): Response
    {
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        //grazinu redirect
        return $this->redirectToRoute('author_index');
    }
      /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"})
     */
    public function delete($id): Response
    {
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id);

        if ($author->getBooks()->count() > 0) {
            return new Response('Šio autorius ištrinti negalima, nes turi knygų.');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        //grazinu redirect
        return $this->redirectToRoute('author_index');
    }
}
