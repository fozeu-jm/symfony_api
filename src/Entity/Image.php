<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\UploadImageAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity()
 * @Vich\Uploadable()
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post"={
 *          "method" = "POST",
 *          "path" = "/images",
 *          "controller" = UploadImageAction::class,
 *          "defaults" = {"_api_receive"=false}
 *      }
 *     },
 *     itemOperations={"get","put","delete"}
 * )
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="url")
     * @Assert\NotNull()
     */
    private $file;

    /**
     * @ORM\Column(type="string",nullable=true, length=255)
     * @Groups({"single_post"})
     */
    private $url;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }


    public function setFile($file): void
    {
        $this->file = $file;
    }


    public function getUrl()
    {
        return "/images/" . $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

}