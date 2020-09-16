<?php

namespace App\Controller;

use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="bloglist")
     */
    public function postsList(Request $request)
    {
        //get query parameter
        $limit = $request->get("limit", 10);
        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();
        return $this->json(
            [
                "page" => 5,
                "limit" => $limit,
                "data" => array_map(function(BlogPost $item){
                    return $this->generateUrl("blog_by_slug",["slug" => $item->getSlug()]);
                }, $items)
            ]
        );
    }

    /**
     * @Route("/posts/{id}", name="blog_by_id", requirements={"id" = "\d+"}, methods={"GET"})
     * @ParamConverter("post",class="App:BlogPost")
     */
    public function postById($post)
    {
        /**
         * can also do
         * $this->getDoctrine()->getRepository(BlogPost::class)->find($id)
         */
        return $this->json($post);
    }

    /**
     * @Route("/posts/{slug}", name="blog_by_slug", methods={"GET"})
     * @ParamConverter("post", class="App:BlogPost", options={"mapping": {"slug":"slug"}})
     */
    public function postBySlug($post)
    {
        /**
         * can also do
         * $this->getDoctrine()->getRepository(BlogPost::class)->findOneBy(["slug"=> $slug])
         */
        return $this->json($post);
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');
        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();
        return $this->json($blogPost);
    }

    /**
     * @Route("/posts/{id}", name="delete_post", methods={"DELETE"})
     */
    public function delete(BlogPost $post){
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->json(null,Response::HTTP_NO_CONTENT);
    }

}