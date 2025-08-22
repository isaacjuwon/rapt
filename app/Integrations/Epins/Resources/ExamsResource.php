<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\PurchaseExamPin;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Responses\ExamPinPurchaseResponse;
use Throwable;

final readonly class ExamsResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Purchase exam PIN.
     *
     * @param PurchaseExamPin $examPin
     * @return ExamPinPurchaseResponse
     */
    public function purchase(PurchaseExamPin $examPin): ExamPinPurchaseResponse
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/exams/',
                options: $examPin->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return ExamPinPurchaseResponse::make($response->json());
    }
}
