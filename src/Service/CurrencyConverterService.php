<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpClient\HttpClient;




class CurrencyConverterService
{
    private const URL = "https://api.freecurrencyapi.com/v1/latest";

    private string $apiKey;
    private AdapterInterface $cache;
    private int $cachePeriod = 3600;
    private LoggerInterface $logger;

    private array $defaultCurrencies = [
        'USD' => 1.0,
        'EUR' => 0.93,
        'GBP' => 0.8,
    ];

    public function __construct(string $apiKey, AdapterInterface $cache,  LoggerInterface $logger)
    {
        $this->apiKey = $apiKey;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    private function requestData()
    {
        $this->logger->info('getting currency exchange rate from API');

        $client = HttpClient::create();
        try {
            $response = $client->request('GET', self::URL,
                ['query' => [
                    'apikey' => $this->apiKey,
                ],
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $data = $response->toArray();
                return $data['data'];
            }
        } catch (\Exception $e) {
            return $this->defaultCurrencies;
        }
        return null;
    }

    /**
     * Convert a given value from one currency to another using exchange rate data fetched from an API.
     * Returns -1 in case of failure to retrieve exchange rate data.
     *
     * @param float $value The value to convert.
     * @param string $currency The currency to convert to.
     * @return float The converted value, or -1 if the conversion fails.
     */
    public function convertPrice(float $value, string $currency): float
    {
        $cacheKey = 'currency';
        $cachedData = $this->cache->getItem($cacheKey);
        $conversionData = $cachedData->get();
        if (!$cachedData->isHit()) {
            $conversionData = $this->requestData();
            $cachedData->set($conversionData);
            $cachedData->expiresAfter($this->cachePeriod);
            $this->cache->save($cachedData);
        }

        if (isset($conversionData[$currency])) {
            return $conversionData[$currency] * $value;
        } else {
            return -1;
        }
    }
}