<?php

namespace App\Exceptions;

use Exception;

class TrackUploadException extends Exception
{
    public static function fileTooLarge(): self
    {
        return new self('Le fichier est trop volumineux. Maximum 20 MB.');
    }

    public static function invalidFormat(): self
    {
        return new self('Format de fichier invalide. Seuls les fichiers MP3 sont acceptés.');
    }

    public static function storageFailed(): self
    {
        return new self('Échec du stockage du fichier. Veuillez réessayer.');
    }
}
