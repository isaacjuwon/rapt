<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class ExamPinPurchaseResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?ExamPinPurchaseData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return ExamPinPurchaseResponse
     */
    public static function make(array $data): ExamPinPurchaseResponse
    {
        return new ExamPinPurchaseResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? ExamPinPurchaseData::make($data['description']) 
                : null,
        );
    }

    /**
     * Check if the purchase was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code === 101;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return $this->description?->ref;
    }

    /**
     * Get the amount paid.
     *
     * @return float|null
     */
    public function getAmountPaid(): ?float
    {
        return $this->description?->amount_paid;
    }

    /**
     * Get the transaction date.
     *
     * @return string|null
     */
    public function getTransactionDate(): ?string
    {
        return $this->description?->transaction_date;
    }

    /**
     * Get the exam type.
     *
     * @return string|null
     */
    public function getExamType(): ?string
    {
        return $this->description?->examType;
    }

    /**
     * Get the quantity of pins purchased.
     *
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->description?->quantity;
    }

    /**
     * Get the PIN details.
     *
     * @return array|null
     */
    public function getPins(): ?array
    {
        return $this->description?->pins;
    }

    /**
     * Get the service ID.
     *
     * @return string|null
     */
    public function getServiceId(): ?string
    {
        return $this->description?->serviceId;
    }

    /**
     * Get the error message if the purchase failed.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }

        return match ($this->code) {
            400 => 'Invalid request method',
            103 => 'Invalid account credentials',
            107 => 'Invalid amount',
            102 => 'Low wallet balance',
            1007 => 'Transaction blocked',
            1009 => 'Account blocked',
            104 => 'Transaction ID already exists',
            105 => 'Transaction failed',
            108 => 'Below minimum amount allowed',
            118 => 'Missing service ID',
            207 => 'PIN unavailable',
            303 => 'Invalid service ID',
            304 => 'Unauthorized access',
            default => $this->message ?? 'Unknown error occurred',
        };
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'description' => $this->description?->toArray(),
        ];
    }
}

final readonly class ExamPinPurchaseData
{
    public function __construct(
        public ?string $ref,
        public ?float $amount_paid,
        public ?string $transaction_date,
        public ?string $examType,
        public ?int $quantity,
        public ?array $pins,
        public ?string $serviceId,
    ) {}

    /**
     * @param array $data
     * @return ExamPinPurchaseData
     */
    public static function make(array $data): ExamPinPurchaseData
    {
        return new ExamPinPurchaseData(
            ref: $data['ref'] ?? null,
            amount_paid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            transaction_date: $data['transaction_date'] ?? null,
            examType: $data['examType'] ?? null,
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : null,
            pins: $data['pins'] ?? null,
            serviceId: $data['serviceId'] ?? null,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ref' => $this->ref,
            'amount_paid' => $this->amount_paid,
            'transaction_date' => $this->transaction_date,
            'examType' => $this->examType,
            'quantity' => $this->quantity,
            'pins' => $this->pins,
            'serviceId' => $this->serviceId,
        ];
    }
}
