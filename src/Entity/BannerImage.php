<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BannerImage
{
    /**
     * @var UploadedFile
     * @Assert\Image()
     */
    private $bannerImage;

    /**
     * @return UploadedFile
     */
    public function getBannerImage()
    {
        return $this->bannerImage;
    }

    /**
     * @param UploadedFile $bannerImage
     * @return $this
     */
    public function setBannerImage($bannerImage)
    {
        $this->bannerImage = $bannerImage;

        return $this;
    }
}