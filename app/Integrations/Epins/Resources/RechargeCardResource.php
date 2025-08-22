<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\GenerateRechargeCard;
use App\Integrations\Epins\EpinsConnector;
use Throwable;

final readonly class RechargeCardResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Generate recharge card PINs.
     *
     * @param GenerateRechargeCard $rechargeCard
     * @return array
     */
    public function generate(GenerateRechargeCard $rechargeCard): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/epin/',
                options: $rechargeCard->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Generate recharge card PINs with raw data.
     *
     * @param array $data
     * @return array
     */
    public function generateRaw(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/epin/',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Generate data card PINs.
     *
     * @param string $network
     * @param int $dataPlan
     * @param int $pinQuantity
     * @param string $reference
     * @return array
     */
    public function generateDataCard(string $network, int $dataPlan, int $pinQuantity, string $reference): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/datacard/',
                options: [
                    'json' => [
                        'service' => 'datacard',
                        'network' => $network,
                        'DataPlan' => $dataPlan,
                        'pinQuantity' => $pinQuantity,
                        'ref' => $reference,
                    ]
                ]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Check if PIN generation was successful.
     *
     * @param array $response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return $this->connector->isSuccessful($response);
    }

  
    /**
     * Format PINs for printing.
     *
     * @param array $pins
     * @return array
     */
    public function formatPinsForPrinting(array $pins): array
    {
        $formatted = [];

        foreach ($pins as $pin) {
            $formatted[] = [
                'network' => strtoupper($pin['network'] ?? ''),
                'pin' => $pin['pin'] ?? '',
                'serial' => $pin['serial'] ?? '',
                'dial_code' => $pin['dial'] ?? '',
                'customer_care' => $pin['customercare'] ?? '',
                'card_name' => $pin['cardname'] ?? '',
                'amount' => $pin['amount'] ?? '',
                'date' => $pin['date'] ?? '',
                'logo' => $pin['logo'] ?? '',
                'formatted_pin' => $this->formatPinWithSpaces($pin['pin'] ?? ''),
                'instructions' => $this->getPinInstructions($pin['network'] ?? ''),
            ];
        }

        return $formatted;
    }

    /**
     * Format PIN with spaces for better readability.
     *
     * @param string $pin
     * @return string
     */
    private function formatPinWithSpaces(string $pin): string
    {
        // Remove any existing spaces or dashes
        $cleanPin = preg_replace('/[\s\-]/', '', $pin);
        
        // Add spaces every 4 characters
        return chunk_split($cleanPin, 4, ' ');
    }

    /**
     * Get PIN usage instructions by network.
     *
     * @param string $network
     * @return string
     */
    private function getPinInstructions(string $network): string
    {
        return match (strtolower($network)) {
            'mtn' => 'Dial *311*PIN# to recharge',
            'airtel' => 'Dial *126*PIN# to recharge',
            'glo' => 'Dial *123*PIN# to recharge',
            'etisalat', '9mobile' => 'Dial *222*PIN# to recharge',
            default => 'Follow network instructions to recharge',
        };
    }


    /**
     * Generate PIN printing template data.
     *
     * @param array $response
     * @return array
     */
    public function generatePrintingTemplate(array $response): array
    {
        $pins = $this->getPins($response);
        $formattedPins = $this->formatPinsForPrinting($pins);

        return [
            'transaction_ref' => $this->getTransactionReference($response),
            'denomination' => $this->getDenomination($response),
            'quantity' => $this->getQuantity($response),
            'amount_paid' => $this->getAmountPaid($response),
            'transaction_date' => $this->getTransactionDate($response),
            'pins' => $formattedPins,
            'total_pins' => count($formattedPins),
            'network_name' => $formattedPins[0]['network'] ?? '',
            'print_date' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
