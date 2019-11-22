<?php declare(strict_types=1);

namespace Madewithlove;

use Exception;

class HtaccessException extends Exception
{
    public static function fromApiErrors(array $errors): self
    {
        $errorMessages = array_map(
            function (array $error): string {
                return $error['field'] . ': ' . $error['message'];
            },
            $errors
        );

        return new static(implode("\n", $errorMessages));
    }
}
