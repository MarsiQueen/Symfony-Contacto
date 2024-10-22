<?php

namespace App\Controller;

use App\Entity\Contacto;
//use Symfony\Bridge\Doctrine\ManagerRegistry;
use App\Entity\Provincia;
use App\Form\ContactoFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ContactoController extends AbstractController
{

    private $contactos = [

        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],

        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],

        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],

        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],

        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]

    ];



    /**
     * @Route("/contacto/editar/{codigo}", name="editar")
     */
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo)
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        if ($contacto) {
            $formulario = $this->createForm(ContactoFormType::class, $contacto);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            }
            return $this->render('nuevo.html.twig', array(
                'formulario' => $formulario->createView()
            ));
        } else {
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => NULL
            ]);
        }
    }


    /**
     * @Route("/contacto/nuevo", name="nuevo")
     */
    public function nuevo(ManagerRegistry $doctrine, Request $request)
    {
        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            //$contacto = $formulario->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array('formulario' => $formulario->createView()));
    }



    /**
     * @Route("/contacto/insertarSinProvincia", name="insertar_sin_provincia_contacto")
     */
    public function insertarSinProvincia(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $provincia = $repositorio->findOneBy(["nombre" => "Alicante"]);

        $contacto = new Contacto();

        $contacto->setNombre("Inserción de prueba sin provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("Inserción.de.prueba.sin.provincia@contacto.es");
        $contacto->setProvincia($provincia);

        $entityManager->persist($contacto);

        $entityManager->flush();
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);

    }



    /**
     * @Route("/contacto/insertarConProvincia", name="insertar_con_provincia_contacto")
     */
    public function insertarConProvincia(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $provincia = new Provincia();

        $provincia->setNombre('Alicante');

        $contacto = new Contacto();

        $contacto->setNombre("Inserción de prueba con provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("Inserción.de.prueba.provincia@contacto.es");
        $contacto->setProvincia($provincia);

        $entityManager->persist($provincia);
        $entityManager->persist($contacto);

        $entityManager->flush();
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);

    }




    /**
     * @Route("/contacto/insertar", name="insertar_contacto")
     */
    public function insertar(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        foreach ($this->contactos as $c) {
            $contacto = new Contacto();
            $contacto->setNombre($c["nombre"]);
            $contacto->setTelefono($c["telefono"]);
            $contacto->setEmail($c["email"]);
            $entityManager->persist($contacto);
        }

        try {
            $entityManager->flush();
            return new Response ("Contactos insertados");
        }catch (\Exception $e){
            return new Response ("Error al insertar los datos");
        }
    }

    /**
     * @Route("/contacto/{codigo}", name="ficha_contacto")
     */
    public function ficha(ManagerRegistry $doctrine, $codigo): Response{
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render('ficha_contacto.html.twig', ["contacto" => $contacto]);
    }


    /**
     * @Route("/contacto/buscar/{texto}", name="buscar_contacto")
     */
    public function buscar($texto): Response{
        //Filtro
        $resultados = array_filter($this->contactos, function($contacto) use ($texto){
            return strpos($contacto['nombre'], $texto) !== FALSE;
        });

        return $this->render('lista_contactos.html.twig', ["contactos" => $resultados]);
    }

    /**
     * @Route("/contacto/update/{id}/{nombre}", name="modificar_contacto")
     */
    public function update(ManagerRegistry $doctrine, $id, $nombre): Response
    {
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(Contacto::class);
        $contacto = $repository->find($id);
        if ($contacto){
            $contacto->setNombre($nombre);
            try {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig', ['contacto' => $contacto]);
            } catch (\Exception $e) {
                return new Response("Error insertando objetos");
            }
        }else
            return $this->render('ficha_contacto.html.twig', ["contacto" => null]);
    }


    /**
     * @Route("/contacto/delete/{id}", name="eliminar_contacto")
     */
    public function delete(ManagerRegistry $doctrine, $id): Response{
        $entityManager = $doctrine->getManager();
        $repository = $entityManager->getRepository(Contacto::class);
        $contacto = $repository->find($id);
        if ($contacto){
            try {
                $entityManager->remove($contacto);
                $entityManager->flush();
                // Mensaje de éxito
                return new Response("<h3>Contacto eliminado</h3>
                    <a href='" . $this->generateUrl('contacto_list') . "'>Volver a la lista de contactos</a>");
            } catch (\Exception $e) {
                return new Response("Error eliminando objetos");
            }
        } else {
            return new Response("Contacto no encontrado");
        }
    }

    /**
     * @Route("/contactos", name="contacto_list")
     */
    public function list(ManagerRegistry $doctrine): Response
    {
        // Comprobar que el usuario está logueado
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('index'); // Cambia 'index' a la ruta que quieras
        }

        $repositorio = $doctrine->getRepository(Contacto::class);
        $contactos = $repositorio->findAll();

        return $this->render('contacto/list.html.twig', [
            'contactos' => $contactos,
        ]);
    }





}
