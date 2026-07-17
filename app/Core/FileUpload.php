<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure File Upload Handler
 * 
 * Handles file uploads with validation, secure storage, and unique naming.
 */
class FileUpload
{
    private array $errors = [];

    /**
     * Upload a file securely.
     * 
     * @return string|false The stored filename on success, false on failure.
     */
    public function upload(array $file, ?string $subDirectory = null): string|false
    {
        $this->errors = [];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Validate file size
        $maxSize = Config::get('upload_max_size', 10 * 1024 * 1024);
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / (1024 * 1024), 1);
            $this->errors[] = "File size exceeds the maximum limit of {$maxMB} MB.";
            return false;
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = Config::get('upload_allowed_types', ['jpg', 'jpeg', 'png', 'pdf']);
        if (!in_array($extension, $allowedTypes, true)) {
            $this->errors[] = "File type .{$extension} is not allowed. Allowed types: " . implode(', ', $allowedTypes);
            return false;
        }

        // Validate MIME type
        $mimeType = mime_content_type($file['tmp_name']);
        $allowedMimes = Config::get('upload_allowed_mimes', [
            'image/jpeg',
            'image/png',
            'application/pdf',
        ]);
        if (!in_array($mimeType, $allowedMimes, true)) {
            $this->errors[] = "File MIME type is not allowed.";
            return false;
        }

        // Generate unique filename
        $uniqueName = $this->generateUniqueFilename($extension);

        // Determine upload path
        $uploadPath = Config::get('upload_path', dirname(__DIR__, 2) . '/storage/uploads');
        if ($subDirectory) {
            $uploadPath .= '/' . trim($subDirectory, '/');
        }

        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $destination = $uploadPath . '/' . $uniqueName;

        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = "Failed to save the uploaded file.";
            Logger::error("File upload failed: could not move file to {$destination}");
            return false;
        }

        Logger::info("File uploaded successfully: {$uniqueName}");

        // Return the relative path (subdirectory/filename)
        return $subDirectory ? $subDirectory . '/' . $uniqueName : $uniqueName;
    }

    /**
     * Delete an uploaded file.
     */
    public function delete(string $filename): bool
    {
        $uploadPath = Config::get('upload_path', dirname(__DIR__, 2) . '/storage/uploads');
        $filepath = $uploadPath . '/' . $filename;

        if (file_exists($filepath)) {
            $result = unlink($filepath);
            if ($result) {
                Logger::info("File deleted: {$filename}");
            }
            return $result;
        }

        return false;
    }

    /**
     * Get the full path to an uploaded file.
     */
    public static function getFullPath(string $filename): string
    {
        $uploadPath = Config::get('upload_path', dirname(__DIR__, 2) . '/storage/uploads');
        return $uploadPath . '/' . $filename;
    }

    /**
     * Check if an uploaded file exists.
     */
    public static function exists(string $filename): bool
    {
        return file_exists(self::getFullPath($filename));
    }

    /**
     * Get upload errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first upload error.
     */
    public function getFirstError(): string
    {
        return $this->errors[0] ?? '';
    }

    /**
     * Generate a unique filename.
     */
    private function generateUniqueFilename(string $extension): string
    {
        return date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * Get human-readable upload error message.
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds the server upload size limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the form upload size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.',
            default => 'Unknown upload error.',
        };
    }
}
