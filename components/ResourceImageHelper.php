<?php

namespace app\components;

use app\models\File;
use app\models\Resource;
use Yii;
use yii\base\Component;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

final class ResourceImageHelper extends Component
{
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];
    private const MAX_IMAGE_SIZE = 5242880;

    public static function getUploadedImageFile(Model $form, array $fieldNames = ['imageFile', 'image_file', 'image', 'file']): ?UploadedFile
    {
        $files = UploadedFile::getInstances($form, 'imageFile');
        foreach ($fieldNames as $fieldName) {
            array_push($files, ...UploadedFile::getInstancesByName($fieldName));
        }

        if (count($files) > 1) {
            throw new \InvalidArgumentException('Only one image file can be uploaded.');
        }

        return $files[0] ?? null;
    }

    public static function getUploadedImageFiles(Model $form, array $fieldNames = ['imageFiles', 'image_files', 'images', 'files', 'imageFile']): array
    {
        $files = [];
        $seen = [];
        foreach (UploadedFile::getInstances($form, 'imageFiles') as $file) {
            $key = self::uploadedFileKey($file);
            if (!isset($seen[$key])) {
                $files[] = $file;
                $seen[$key] = true;
            }
        }

        foreach ($fieldNames as $fieldName) {
            foreach (UploadedFile::getInstancesByName($fieldName) as $file) {
                $key = self::uploadedFileKey($file);
                if (!isset($seen[$key])) {
                    $files[] = $file;
                    $seen[$key] = true;
                }
            }
        }

        return $files;
    }

    public static function attachImage(
        string $resourceType,
        int $resourceId,
        int $userId,
        string $uploadFolder,
        UploadedFile $imageFile,
        Model $form,
        bool $isPrimary = true,
        int $sortOrder = 0
    ): Resource {
        $file = null;
        try {
            $file = self::createFileRecord($userId, $uploadFolder, $imageFile);
            return self::createImageResource($resourceType, $resourceId, $file, $isPrimary, $sortOrder);
        } catch (\Throwable $e) {
            if ($file !== null) {
                $fullPath = Yii::getAlias('@webroot/' . ltrim($file->path, '/\\'));
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
                $file->delete();
            }
            if (!$form->hasErrors('imageFile')) {
                $form->addError('imageFile', $e->getMessage());
            }
            throw $e;
        }
    }

    public static function markImagesNonPrimary(string $resourceType, int $resourceId): void
    {
        Resource::updateAll(
            ['is_primary' => 0],
            [
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'type' => 'image',
                'is_primary' => 1,
            ]
        );
    }

    public static function attachExistingImageFile(
        string $resourceType,
        int $resourceId,
        int $fileId,
        bool $isPrimary = true,
        int $sortOrder = 0
    ): Resource {
        $file = File::findOne(['id' => $fileId]);
        if ($file === null) {
            throw new \RuntimeException('File not found.');
        }

        return self::createImageResource($resourceType, $resourceId, $file, $isPrimary, $sortOrder);
    }

    public static function attachExistingImageResource(
        string $resourceType,
        int $resourceId,
        int $resourceImageId,
        bool $isPrimary = true,
        int $sortOrder = 0
    ): Resource {
        $sourceResource = Resource::find()
            ->with(['file'])
            ->where([
                'id' => $resourceImageId,
                'type' => 'image',
            ])
            ->one();

        if ($sourceResource === null) {
            throw new \RuntimeException('Image resource not found.');
        }

        return self::createImageResource($resourceType, $resourceId, $sourceResource->file, $isPrimary, $sortOrder);
    }

    public static function deleteImageRecords(string $resourceType, int $resourceId): void
    {
        $resources = Resource::find()
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

    public static function deleteImageResourceLinks(string $resourceType, int $resourceId): void
    {
        Resource::deleteAll([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'type' => 'image',
        ]);
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

    public static function attachImagesFromForm(
        string $resourceType,
        int $resourceId,
        int $userId,
        string $uploadFolder,
        Model $form,
        bool $hasPrimary = false,
        int $sortOrder = 0,
        int $maxFiles = 10
    ): array {
        $hasImages = !empty($form->imageFiles)
            || !empty($form->image_file_ids)
            || !empty($form->image_resource_ids);

        if (!$hasImages) {
            return [];
        }

        $resourceRows = [];
        $imageErrors = [];
        $uploadedFiles = [];
        $time = time();
        $imageFiles = $form->imageFiles ?? [];

        if (count($imageFiles) > $maxFiles) {
            foreach (array_slice($imageFiles, $maxFiles) as $imageFile) {
                $imageErrors[] = [
                    'name' => $imageFile->name,
                    'error' => 'Maximum image count exceeded.',
                ];
            }

            $imageFiles = array_slice($imageFiles, 0, $maxFiles);
        }

        foreach ($imageFiles as $imageFile) {
            try {
                $file = self::createFileRecord($userId, $uploadFolder, $imageFile);
                $uploadedFiles[] = $file;
            } catch (\Throwable $exception) {
                $imageErrors[] = [
                    'name' => $imageFile->name,
                    'error' => $exception->getMessage(),
                ];

                Yii::warning($exception->getMessage(), __METHOD__);
                continue;
            }

            $resourceRows[] = [
                $file->id,
                $resourceType,
                $resourceId,
                'image',
                $imageFile->name,
                null,
                $sortOrder++,
                $hasPrimary ? 0 : 1,
                $time,
                $time,
            ];

            $hasPrimary = true;
        }

        $existingFiles = [];
        if (!empty($form->image_file_ids)) {
            $existingFiles = File::find()
                ->where(['id' => $form->image_file_ids])
                ->indexBy('id')
                ->all();
        }

        foreach (($form->image_file_ids ?? []) as $fileId) {
            $file = $existingFiles[(int) $fileId] ?? null;
            if ($file === null) {
                $imageErrors[] = [
                    'name' => (string) $fileId,
                    'error' => 'File not found.',
                ];
                continue;
            }

            $resourceRows[] = [
                (int) $fileId,
                $resourceType,
                $resourceId,
                'image',
                $file->original_name,
                null,
                $sortOrder++,
                $hasPrimary ? 0 : 1,
                $time,
                $time,
            ];

            $hasPrimary = true;
        }

        $existingResources = [];
        if (!empty($form->image_resource_ids)) {
            $existingResources = Resource::find()
                ->where([
                    'id' => $form->image_resource_ids,
                    'type' => 'image',
                ])
                ->indexBy('id')
                ->all();
        }

        foreach (($form->image_resource_ids ?? []) as $resourceIdValue) {
            $resource = $existingResources[(int) $resourceIdValue] ?? null;
            if ($resource === null) {
                $imageErrors[] = [
                    'name' => (string) $resourceIdValue,
                    'error' => 'Image resource not found.',
                ];
                continue;
            }

            $resourceRows[] = [
                (int) $resource->file_id,
                $resourceType,
                $resourceId,
                'image',
                $resource->title,
                $resource->alt_text,
                $sortOrder++,
                $hasPrimary ? 0 : 1,
                $time,
                $time,
            ];

            $hasPrimary = true;
        }

        if (!empty($resourceRows)) {
            try {
                Yii::$app->db->createCommand()->batchInsert(
                    Resource::tableName(),
                    [
                        'file_id',
                        'resource_type',
                        'resource_id',
                        'type',
                        'title',
                        'alt_text',
                        'sort_order',
                        'is_primary',
                        'created_at',
                        'updated_at',
                    ],
                    $resourceRows
                )->execute();
            } catch (\Throwable $exception) {
                foreach ($uploadedFiles as $uploadedFile) {
                    $fullPath = Yii::getAlias('@webroot/' . ltrim($uploadedFile->path, '/\\'));
                    if (is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }

                throw $exception;
            }
        }

        return $imageErrors;
    }

    public static function createFileRecord(int $userId, string $folder, UploadedFile $imageFile): File
    {
        self::validateImageFile($imageFile);

        $fileName = Yii::$app->security->generateRandomString(16) . '.' . strtolower($imageFile->extension);
        $relativePath = trim($folder, '/') . '/' . $fileName;
        $fullPath = Yii::getAlias('@webroot/' . $relativePath);

        FileHelper::createDirectory(dirname($fullPath));

        if (!$imageFile->saveAs($fullPath)) {
            throw new \RuntimeException('Failed to save uploaded file: ' . $imageFile->name);
        }

        $imageSize = @getimagesize($fullPath);
        if ($imageSize === false) {
            @unlink($fullPath);
            throw new \RuntimeException('Invalid image file: ' . $imageFile->name);
        }

        [$width, $height] = $imageSize;

        $file = new File();
        $file->user_id = $userId;
        $file->disk = 'local';
        $file->path = $relativePath;
        $file->url = '/' . $relativePath;
        $file->original_name = $imageFile->name;
        $file->mime_type = $imageFile->type;
        $file->size_bytes = $imageFile->size;
        $file->width = $width;
        $file->height = $height;

        if (!$file->save(false)) {
            @unlink($fullPath);
            throw new \RuntimeException('Failed to save file record: ' . $imageFile->name);
        }

        return $file;
    }

    public static function createImageResource(
        string $resourceType,
        int $resourceId,
        File $file,
        bool $isPrimary,
        int $sortOrder = 0
    ): Resource {
        $resource = new Resource();
        $resource->file_id = $file->id;
        $resource->resource_type = $resourceType;
        $resource->resource_id = $resourceId;
        $resource->type = 'image';
        $resource->title = $file->original_name;
        $resource->alt_text = null;
        $resource->sort_order = $sortOrder;
        $resource->is_primary = $isPrimary ? 1 : 0;

        if (!$resource->save(false)) {
            throw new \RuntimeException('Failed to save image resource: ' . json_encode($resource->errors));
        }

        return $resource;
    }

    private static function validateImageFile(UploadedFile $imageFile): void
    {
        if ($imageFile->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed: ' . $imageFile->name);
        }

        $extension = strtolower((string) $imageFile->extension);
        if (!in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            throw new \RuntimeException('Unsupported image extension: ' . $imageFile->name);
        }

        if ((int) $imageFile->size > self::MAX_IMAGE_SIZE) {
            throw new \RuntimeException('Image file is too large: ' . $imageFile->name);
        }
    }

    private static function uploadedFileKey(UploadedFile $file): string
    {
        return implode('|', [
            $file->tempName,
            $file->name,
            (string) $file->size,
            (string) $file->error,
        ]);
    }

}
