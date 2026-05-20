<?php

namespace app\helpers;

use app\models\Files;
use app\models\Resources;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

final class ResourceImageHelper
{
    public static function getUploadedImageFile(
        Model $form,
        array $fieldNames = ['imageFile', 'image_file', 'image', 'file']
    ): ?UploadedFile {
        $file = UploadedFile::getInstance($form, 'imageFile');
        if ($file instanceof UploadedFile) {
            return $file;
        }

        foreach ($fieldNames as $fieldName) {
            $file = UploadedFile::getInstanceByName($fieldName);
            if ($file instanceof UploadedFile) {
                return $file;
            }

            $files = UploadedFile::getInstancesByName($fieldName);
            if (!empty($files)) {
                return $files[0];
            }
        }

        return null;
    }

    public static function attachImage(
        string $resourceType,
        int $resourceId,
        int $userId,
        string $uploadFolder,
        UploadedFile $imageFile,
        Model $form,
        bool $isPrimary = true
    ): Resources {
        $uploadFolder = trim(str_replace('\\', '/', $uploadFolder), '/');
        $uploadDir = Yii::getAlias('@webroot/' . str_replace('/', DIRECTORY_SEPARATOR, $uploadFolder));
        FileHelper::createDirectory($uploadDir);

        $fileName = Yii::$app->security->generateRandomString(16) . '.' . $imageFile->extension;
        $relativePath = $uploadFolder . '/' . $fileName;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$imageFile->saveAs($fullPath)) {
            $form->addError('imageFile', 'Failed to upload image.');
            throw new \RuntimeException('Failed to upload image.');
        }

        try {
            $file = self::createFileRecord($userId, $imageFile, $relativePath, $fullPath);
            return self::createImageResource($resourceType, $resourceId, $file, $isPrimary);
        } catch (\Throwable $e) {
            @unlink($fullPath);
            if (!$form->hasErrors('imageFile')) {
                $form->addError('imageFile', 'Failed to save image information.');
            }
            throw $e;
        }
    }

    public static function markImagesNonPrimary(string $resourceType, int $resourceId): void
    {
        Resources::updateAll(
            ['is_primary' => 0],
            [
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'type' => 'image',
                'is_primary' => 1,
            ]
        );
    }

    public static function deleteImageRecords(string $resourceType, int $resourceId): void
    {
        $resources = Resources::find()
            ->with(['file'])
            ->where([
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'type' => 'image',
            ])
            ->all();

        foreach ($resources as $resource) {
            $relatedRecords = $resource->getRelatedRecords();
            $file = $relatedRecords['file'] ?? null;
            $fullPath = null;

            if ($file && $file->path) {
                $fullPath = Yii::getAlias('@webroot/' . ltrim($file->path, '/\\'));
            }

            $resource->delete();

            if ($file !== null) {
                $file->delete();
            }
            if ($fullPath !== null && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    public static function logMissingMultipartImage(?UploadedFile $imageFile): void
    {
        if ($imageFile !== null) {
            return;
        }

        Yii::warning([
            'message' => 'Multipart request did not include an uploaded image file.',
            'post_keys' => array_keys(Yii::$app->request->post()),
            'file_keys' => array_keys($_FILES),
            'expected_file_keys' => ['imageFile', 'image_file', 'image', 'file'],
        ], __METHOD__);
    }

    private static function createFileRecord(
        int $userId,
        UploadedFile $imageFile,
        string $relativePath,
        string $fullPath
    ): Files {
        $size = @getimagesize($fullPath);

        $file = new Files();
        $file->user_id = $userId;
        $file->disk = 'local';
        $file->path = $relativePath;
        $file->url = Yii::getAlias('@web/' . $relativePath);
        $file->original_name = $imageFile->name;
        $file->mime_type = $imageFile->type;
        $file->size_bytes = $imageFile->size;
        $file->width = $size ? $size[0] : null;
        $file->height = $size ? $size[1] : null;

        if (!$file->save()) {
            throw new \RuntimeException('Failed to save file record: ' . json_encode($file->errors));
        }

        return $file;
    }

    private static function createImageResource(
        string $resourceType,
        int $resourceId,
        Files $file,
        bool $isPrimary
    ): Resources {
        $resource = new Resources();
        $resource->file_id = $file->id;
        $resource->resource_type = $resourceType;
        $resource->resource_id = $resourceId;
        $resource->type = 'image';
        $resource->title = $file->original_name;
        $resource->alt_text = null;
        $resource->sort_order = 0;
        $resource->is_primary = $isPrimary ? 1 : 0;

        if (!$resource->save()) {
            throw new \RuntimeException('Failed to save image resource: ' . json_encode($resource->errors));
        }

        return $resource;
    }
}
