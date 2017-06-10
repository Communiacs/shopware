<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\MediaBundle;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\File;

class MediaReplaceService implements MediaReplaceServiceInterface
{
    /** @var MediaServiceInterface */
    private $mediaService;

    /** @var ModelManager */
    private $modelManager;

    /** @var Manager */
    private $thumbnailManager;

    /**
     * @param MediaServiceInterface $mediaService
     * @param Manager               $thumbnailManager
     * @param ModelManager          $modelManager
     */
    public function __construct(MediaServiceInterface $mediaService, Manager $thumbnailManager, ModelManager $modelManager)
    {
        $this->mediaService = $mediaService;
        $this->thumbnailManager = $thumbnailManager;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function replace($mediaId, File $file)
    {
        $media = $this->modelManager->find(Media::class, $mediaId);

        if (!$this->validateMediaType($media, $file)) {
            throw new \RuntimeException(sprintf('To replace the media file, an %s file is required', $media->getType()));
        }

        $fileContent = file_get_contents($file->getRealPath());

        $this->mediaService->write($media->getPath(), $fileContent);

        $media->setExtension($this->getExtension($file));
        $media->setFileSize(filesize($file->getRealPath()));

        if ($media->getType() === $media::TYPE_IMAGE) {
            $imageSize = getimagesize($file->getRealPath());

            if ($imageSize) {
                $media->setWidth($imageSize[0]);
                $media->setHeight($imageSize[1]);
            }

            $media->removeThumbnails();
            $this->thumbnailManager->createMediaThumbnail($media, $media->getDefaultThumbnails(), true);
            $media->createAlbumThumbnails($media->getAlbum());
        }

        $this->modelManager->flush();
    }

    /**
     * @param Media $media
     * @param File  $file
     *
     * @return bool
     */
    private function validateMediaType(Media $media, File $file)
    {
        $uploadedFileExtension = $file->guessExtension();
        $types = $media->getTypeMapping();

        if (!array_key_exists($uploadedFileExtension, $types)) {
            $types[$uploadedFileExtension] = Media::TYPE_UNKNOWN;
        }

        return $media->getType() === $types[$uploadedFileExtension];
    }

    /**
     * @param File $file
     *
     * @return string
     */
    private function getExtension(File $file)
    {
        $extension = strtolower($file->guessExtension());

        switch ($extension) {
            case 'jpeg':
                $extension = 'jpg';
                break;
        }

        return (string) $extension;
    }
}
