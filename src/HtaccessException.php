<?php

declare(strict_types=1);

namespace Madewithlove\HtaccessApiClient;

use Exception;

final class HtaccessException extends Exception
{
    /**
     * @param array<string, array<string>> $errors
     */
    public static function fromApiErrors(array $errors): self
    {
        $errorMessages = [];
        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $errorMessages[] = $field . ': ' . $message;
            }
        }

        return new self(implode("\n", $errorMessages));
    }
}
