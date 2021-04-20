<?php

namespace common\components;

use GeoIp2\Database\Reader;
use Yii;
use yii\base\Component;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\httpclient\Client;

class GeoIp extends Component
{
    private const IP2LOCATION_IPV4_FILE = 'IP2LOCATION-LITE-DB11.BIN';
    private const IP2LOCATION_IPV6_FILE = 'IP2LOCATION-LITE-DB11.IPV6.BIN';
    private const MAXMIND_DB_FILE = 'GeoLite2-City.mmdb';

    public $cacheDuration = 24 * 60 * 60;
    public $ip2locationDownloadToken;
    public $maxmindDownloadToken;

    private $databasePath = '@common/runtime/geo_ip/';

    public function setDatabasePath($value)
    {
        $this->databasePath = Yii::getAlias($value);
    }

    public function getDatabasePath()
    {
        return $this->databasePath;
    }

    public function init(): void
    {
        parent::init();

        $this->databasePath = Yii::getAlias($this->databasePath);

        if (!file_exists($this->databasePath)) {
            mkdir($this->databasePath);
        }
    }

    /**
     * @param string $ip
     * @param bool $useCache
     * @return array|null
     */
    public function getData(string $ip, bool $useCache = true): ?array
    {
        $result = $this->getFromMaxmind($ip);
        if (!$result) {
            $result = $this->getFromIp2location($ip);
        }

        if (!$result) {
            $result = $this->getFromIpapi($ip);
        }

        if ($useCache && $result) {
            Yii::$app->cache->set(
                $ip,
                $result,
                $this->cacheDuration
            );
        }

        return $result;
    }

    public function getFromMaxmind(string $ip): ?array
    {
        if (empty($this->maxmindDownloadToken)) {
            return null;
        }

        try {
            $reader = new Reader($this->databasePath . DIRECTORY_SEPARATOR . self::MAXMIND_DB_FILE);
            $record = $reader->city($ip);

            return [
                'ip' => $ip,
                'country_code' => $record->country->isoCode,
                'region_name' => $record->mostSpecificSubdivision->name,
                'city' => $record->city->name,
                'zip_code' => $record->postal->code,
                'time_zone' => $record->location->timeZone,
                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude,
            ];
        } catch (\Exception $e) {
            Yii::error('Maxmind Database - ' . $e->getMessage(), 'ipinfo');
        }

        return null;
    }

    /**
     * @param string $ip
     * @return array|null
     */
    public function getFromIp2location(string $ip): ?array
    {
        if (empty($this->ip2locationDownloadToken)) {
            return null;
        }

        try {
            $dbName = self::IP2LOCATION_IPV4_FILE;
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $dbName = self::IP2LOCATION_IPV6_FILE;
            }

            $reader = new \IP2Location\Database(
                $this->databasePath . DIRECTORY_SEPARATOR . $dbName,
                \IP2Location\Database::FILE_IO
            );

            $record = $reader->lookup($ip, \IP2Location\Database::ALL);

            if (!$record) {
                Yii::info('Ip2location Database - Not found data for IP: ' . $ip, 'ipinfo');
                return null;
            }

            return [
                'ip' => $ip,
                'country_code' => $record['countryCode'],
                'region_name' => $record['regionName'],
                'city' => $record['cityName'],
                'zip_code' => $record['zipCode'],
                'time_zone' => $this->tzOffsetToName((int)$record['timeZone']),
                'latitude' => $record['latitude'],
                'longitude' => $record['longitude'],
            ];
        } catch (\Exception $e) {
            Yii::error('Ip2location Database - ' . $e->getMessage(), 'ipinfo');
        }

        return null;
    }

    /**
     * @param string $ip
     * @return array|null
     */
    public function getFromIpapi(string $ip): ?array
    {
        $client = new Client();

        try {
            $result = $client->createRequest()
                ->setMethod('get')
                ->setUrl('https://ipapi.co/' . $ip . '/json/')
                ->send();

            if ($result->isOk && mb_strpos($result->data['city'], 'Ratelimited') === false) {
                return [
                    'ip' => $result->data['ip'],
                    'country_code' => $result->data['country'],
                    'region_name' => $result->data['region'],
                    'city' => $result->data['city'],
                    'zip_code' => $result->data['postal'],
                    'time_zone' => $result->data['timezone'],
                    'latitude' => $result->data['latitude'],
                    'longitude' => $result->data['longitude'],
                ];
            }
        } catch (\Exception $e) {
            Yii::error('ipapi.co - ' . $e->getMessage(), 'ipinfo');
        }

        return null;
    }

    public function updateDb()
    {
        $this->downloadMaxmindDb();
        foreach (['DB11LITEBIN', 'DB11LITEBINIPV6'] as $name) {
            $this->downloadDb($name);
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function downloadDb(string $name): bool
    {
        if (empty($this->ip2locationDownloadToken)) {
            return false;
        }

        $url = 'https://www.ip2location.com/download/?token=' . $this->ip2locationDownloadToken . '&file=' . $name;
        $file = Yii::getAlias('@common/runtime/ip2location.' . $name . '.zip');

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $url, ['sink' => $file]);

        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            Console::error('Error :- Unable to open the Zip File - ' . $file);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $zip->extractTo($this->databasePath);
        $zip->close();
        unlink($file);

        return true;
    }

    protected function downloadMaxmindDb(): bool
    {
        if (empty($this->maxmindDownloadToken)) {
            return false;
        }

        $url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City'
            . '&license_key=' . $this->maxmindDownloadToken
            . '&suffix=tar.gz'
        ;
        $fileTarGz = Yii::getAlias('@common/runtime/maxmind.tar.gz');
        $fileTar = Yii::getAlias('@common/runtime/maxmind.tar');

        // Clear files if exists
        @unlink($fileTarGz);
        @unlink($fileTar);
        $directories = preg_grep('~^GeoLite2-City_~', scandir(Yii::getAlias('@common/runtime')));
        foreach ($directories as $directory) {
            FileHelper::removeDirectory(Yii::getAlias('@common/runtime/' . $directory));
        }

        $client = new \GuzzleHttp\Client();
        $client->request('GET', $url, ['sink' => $fileTarGz]);

        // decompress from gz
        $p = new \PharData($fileTarGz);
        $p->decompress();

        $runtimePath = Yii::getAlias('@common/runtime');

        $phar = new \PharData($fileTar);
        $phar->extractTo($runtimePath, null, true);

        $directories = preg_grep('~^GeoLite2-City_~', scandir($runtimePath));

        copy(
            $runtimePath . DIRECTORY_SEPARATOR . reset($directories) . DIRECTORY_SEPARATOR . 'GeoLite2-City.mmdb',
            $this->databasePath . 'GeoLite2-City.mmdb'
        );

        return true;
    }

    /**
     * @param int $offset
     * @param bool|null $isDst
     * @return string
     */
    protected static function tzOffsetToName(int $offset, bool $isDst = null): string
    {
        if ($isDst === null) {
            $isDst = date('I');
        }

        $offset *= 3600;
        $zone = timezone_name_from_abbr('', $offset, $isDst);
        if ($zone === false) {
            foreach (timezone_abbreviations_list() as $abbr) {
                foreach ($abbr as $city) {
                    if (
                        (bool)$city['dst'] === (bool)$isDst
                        && strlen($city['timezone_id']) > 0
                        && $city['offset'] == $offset
                    ) {
                        $zone = $city['timezone_id'];
                        break;
                    }
                }

                if ($zone !== false) {
                    break;
                }
            }
        }

        return $zone;
    }
}
