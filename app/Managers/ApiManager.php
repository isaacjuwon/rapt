<?php

declare(strict_types=1);

namespace App\Managers;

use App\Integrations\Planetscale\PlanetscaleConnector;
use App\Integrations\Paystack\PaystackConnector;
use App\Integrations\Epins\EpinsConnector;
use Illuminate\Support\Manager;

class ApiManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('services.api.default', 'planetscale');
    }

    /**
     * Create a PlanetScale connector instance.
     *
     * @return \App\Integrations\Planetscale\PlanetscaleConnector
     */
    public function createPlanetscaleDriver()
    {
        return $this->container->make(PlanetscaleConnector::class);
    }

    /**
     * Create a Paystack connector instance.
     *
     * @return \App\Integrations\Paystack\PaystackConnector
     */
    public function createPaystackDriver()
    {
        return $this->container->make(PaystackConnector::class);
    }

    /**
     * Create an E-pins connector instance.
     *
     * @return \App\Integrations\Epins\EpinsConnector
     */
    public function createEpinsDriver()
    {
        return $this->container->make(EpinsConnector::class);
    }



    /**
     * Get the PlanetScale connector instance.
     *
     * @return \App\Integrations\Planetscale\PlanetscaleConnector
     */
    public function planetscale(): PlanetscaleConnector
    {
        return $this->driver('planetscale');
    }

    /**
     * Get the Paystack connector instance.
     *
     * @return \App\Integrations\Paystack\PaystackConnector
     */
    public function paystack(): PaystackConnector
    {
        return $this->driver('paystack');
    }

    /**
     * Get the E-pins connector instance.
     *
     * @return \App\Integrations\Epins\EpinsConnector
     */
    public function epins(): EpinsConnector
    {
        return $this->driver('epins');
    }

    /**
     * Get databases resource.
     *
     * @return \App\Integrations\Planetscale\Resources\DatabaseResource
     */
    public function databases()
    {
        return $this->planetscale()->databases();
    }

    /**
     * Get backups resource.
     *
     * @return \App\Integrations\Planetscale\Resources\BackupResource
     */
    public function backups()
    {
        return $this->planetscale()->backups();
    }

    /**
     * Test connection to PlanetScale.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->planetscale()->testConnection();
    }

    /**
     * Get current user information.
     *
     * @return array
     */
    public function me(): array
    {
        return $this->planetscale()->me();
    }

    /**
     * List all organizations.
     *
     * @return array
     */
    public function organizations(): array
    {
        return $this->planetscale()->organizations();
    }

    /**
     * Send a custom request through PlanetScale connector.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $options
     * @return \Illuminate\Http\Client\Response
     */
    public function send(string $method, string $uri, array $options = [])
    {
        return $this->planetscale()->send($method, $uri, $options);
    }

    /**
     * List databases for an organization.
     *
     * @param  string  $organization
     * @return \Illuminate\Support\Collection
     */
    public function listDatabases(string $organization)
    {
        return $this->databases()->list($organization);
    }

    /**
     * Get a specific database.
     *
     * @param  string  $organization
     * @param  string  $database
     * @return array
     */
    public function getDatabase(string $organization, string $database): array
    {
        return $this->databases()->get($organization, $database);
    }

    /**
     * Create a new database.
     *
     * @param  string  $organization
     * @param  array  $data
     * @return array
     */
    public function createDatabase(string $organization, array $data): array
    {
        return $this->databases()->create($organization, $data);
    }

    /**
     * List branches for a database.
     *
     * @param  string  $organization
     * @param  string  $database
     * @return \Illuminate\Support\Collection
     */
    public function listBranches(string $organization, string $database)
    {
        return $this->databases()->branches($organization, $database);
    }

    /**
     * List backups for a branch.
     *
     * @param  string  $organization
     * @param  string  $database
     * @param  string  $branch
     * @return \Illuminate\Support\Collection
     */
    public function listBackups(string $organization, string $database, string $branch)
    {
        return $this->backups()->list($organization, $database, $branch);
    }

    /**
     * Create a backup.
     *
     * @param  \App\Integrations\Planetscale\Entities\CreateBackup  $backup
     * @return array
     */
    public function createBackup($backup): array
    {
        return $this->backups()->create($backup);
    }

    /**
     * Delete a backup.
     *
     * @param  string  $organization
     * @param  string  $database
     * @param  string  $branch
     * @param  string  $backup
     * @return bool
     */
    public function deleteBackup(string $organization, string $database, string $branch, string $backup): bool
    {
        return $this->backups()->delete($organization, $database, $branch, $backup);
    }

    /**
     * Get available regions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRegions()
    {
        return $this->databases()->regions();
    }

    /**
     * Get connection strings for a branch.
     *
     * @param  string  $organization
     * @param  string  $database
     * @param  string  $branch
     * @return array
     */
    public function getConnectionStrings(string $organization, string $database, string $branch): array
    {
        return $this->databases()->connectionStrings($organization, $database, $branch);
    }

    // Paystack Methods

    /**
     * Get payments resource.
     *
     * @return \App\Integrations\Paystack\Resources\PaymentResource
     */
    public function payments()
    {
        return $this->paystack()->payments();
    }

    /**
     * Get dedicated virtual accounts resource.
     *
     * @return \App\Integrations\Paystack\Resources\DedicatedVirtualAccountResource
     */
    public function dedicatedVirtualAccounts()
    {
        return $this->paystack()->dedicatedVirtualAccounts();
    }

    /**
     * Initialize a payment.
     *
     * @param  \App\Integrations\Paystack\Entities\InitializePayment  $payment
     * @return array
     */
    public function initializePayment($payment): array
    {
        return $this->payments()->initialize($payment);
    }

    /**
     * Verify a payment transaction.
     *
     * @param  string  $reference
     * @return array
     */
    public function verifyPayment(string $reference): array
    {
        return $this->payments()->verify($reference);
    }

    /**
     * List payment transactions.
     *
     * @param  array  $params
     * @return \Illuminate\Support\Collection
     */
    public function listTransactions(array $params = [])
    {
        return $this->payments()->list($params);
    }

    /**
     * Create a dedicated virtual account.
     *
     * @param  \App\Integrations\Paystack\Entities\CreateDedicatedVirtualAccount  $account
     * @return array
     */
    public function createDedicatedVirtualAccount($account): array
    {
        return $this->dedicatedVirtualAccounts()->create($account);
    }

    /**
     * List dedicated virtual accounts.
     *
     * @param  array  $params
     * @return \Illuminate\Support\Collection
     */
    public function listDedicatedVirtualAccounts(array $params = [])
    {
        return $this->dedicatedVirtualAccounts()->list($params);
    }

    /**
     * Get supported banks.
     *
     * @return array
     */
    public function getBanks(): array
    {
        return $this->paystack()->listBanks();
    }

    /**
     * Resolve account number.
     *
     * @param  string  $accountNumber
     * @param  string  $bankCode
     * @return array
     */
    public function resolveAccount(string $accountNumber, string $bankCode): array
    {
        return $this->paystack()->resolveAccountNumber($accountNumber, $bankCode);
    }

    // E-pins Methods

    /**
     * Get airtime resource.
     *
     * @return \App\Integrations\Epins\Resources\AirtimeResource
     */
    public function airtime()
    {
        return $this->epins()->airtime();
    }

    /**
     * Get data resource.
     *
     * @return \App\Integrations\Epins\Resources\DataResource
     */
    public function data()
    {
        return $this->epins()->data();
    }

    /**
     * Get electricity resource.
     *
     * @return \App\Integrations\Epins\Resources\ElectricityResource
     */
    public function electricity()
    {
        return $this->epins()->electricity();
    }

    /**
     * Get TV subscription resource.
     *
     * @return \App\Integrations\Epins\Resources\TvSubscriptionResource
     */
    public function tvSubscription()
    {
        return $this->epins()->tvSubscription();
    }

    /**
     * Get wallet resource.
     *
     * @return \App\Integrations\Epins\Resources\WalletResource
     */
    public function wallet()
    {
        return $this->epins()->wallet();
    }

    /**
     * Get recharge card resource.
     *
     * @return \App\Integrations\Epins\Resources\RechargeCardResource
     */
    public function rechargeCard()
    {
        return $this->epins()->rechargeCard();
    }

    /**
     * Get exams resource.
     *
     * @return \App\Integrations\Epins\Resources\ExamsResource
     */
    public function exams()
    {
        return $this->epins()->exams();
    }

    /**
     * Purchase airtime.
     *
     * @param  \App\Integrations\Epins\Entities\PurchaseAirtime  $airtime
     * @return array
     */
    public function purchaseAirtime($airtime): array
    {
        return $this->airtime()->purchase($airtime);
    }

    /**
     * Purchase data bundle.
     *
     * @param  \App\Integrations\Epins\Entities\PurchaseData  $data
     * @return array
     */
    public function purchaseData($data): array
    {
        return $this->data()->purchase($data);
    }

    /**
     * Purchase electricity token.
     *
     * @param  \App\Integrations\Epins\Entities\PurchaseElectricity  $electricity
     * @return array
     */
    public function purchaseElectricity($electricity): array
    {
        return $this->electricity()->purchase($electricity);
    }

    /**
     * Purchase TV subscription.
     *
     * @param  \App\Integrations\Epins\Entities\PurchaseTvSubscription  $subscription
     * @return array
     */
    public function purchaseTvSubscription($subscription): array
    {
        return $this->tvSubscription()->purchase($subscription);
    }

    /**
     * Get wallet balance.
     *
     * @return array
     */
    public function getWalletBalance(): array
    {
        return $this->wallet()->getBalance();
    }

    /**
     * Validate service (meter, smartcard, etc.).
     *
     * @param  string  $serviceId
     * @param  string  $billerNumber
     * @param  string  $vcode
     * @return array
     */
    public function validateService(string $serviceId, string $billerNumber, string $vcode): array
    {
        return $this->epins()->validateService($serviceId, $billerNumber, $vcode);
    }

    /**
     * Generate recharge card PINs.
     *
     * @param  \App\Integrations\Epins\Entities\GenerateRechargeCard  $rechargeCard
     * @return array
     */
    public function generateRechargeCard($rechargeCard): array
    {
        return $this->rechargeCard()->generate($rechargeCard);
    }

    /**
     * Generate data card PINs.
     *
     * @param  string  $network
     * @param  int  $dataPlan
     * @param  int  $pinQuantity
     * @param  string  $reference
     * @return array
     */
    public function generateDataCard(string $network, int $dataPlan, int $pinQuantity, string $reference): array
    {
        return $this->rechargeCard()->generateDataCard($network, $dataPlan, $pinQuantity, $reference);
    }

    /**
     * Purchase exam PIN.
     *
     * @param  \App\Integrations\Epins\Entities\PurchaseExamPin  $examPin
     * @return array
     */
    public function purchaseExamPin($examPin): array
    {
        return $this->exams()->purchase($examPin);
    }

    /**
     * Purchase WAEC result checker PIN.
     *
     * @param  string  $reference
     * @return array
     */
    public function purchaseWaecPin(string $reference): array
    {
        return $this->exams()->purchaseWaecPin($reference);
    }

    /**
     * Purchase NECO result checker PIN.
     *
     * @param  string  $reference
     * @return array
     */
    public function purchaseNecoPin(string $reference): array
    {
        return $this->exams()->purchaseNecoPin($reference);
    }

}
