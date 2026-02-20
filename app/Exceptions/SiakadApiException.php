<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception khusus untuk error dari koneksi SIAKAD API.
 */
class SiakadApiException extends RuntimeException
{
    protected int $httpStatusCode;
    protected array $responseBody;

    public function __construct(
        string    $message       = 'SIAKAD API Error',
        int       $httpStatus    = 0,
        array     $responseBody  = [],
        Throwable $previous      = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
        $this->httpStatusCode = $httpStatus;
        $this->responseBody   = $responseBody;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    /**
     * Buat exception dari response HTTP gagal.
     */
    public static function fromHttpResponse(int $status, array $body = [], ?Throwable $previous = null): self
    {
        $message = $body['message'] ?? $body['error'] ?? "HTTP Error {$status} dari SIAKAD API";
        return new self($message, $status, $body, $previous);
    }

    /**
     * Buat exception dari connection error (timeout, network, dll).
     */
    public static function connectionError(string $detail, ?Throwable $previous = null): self
    {
        return new self("Gagal terhubung ke SIAKAD API: {$detail}", 0, [], $previous);
    }
}
