<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

/**
 * Service for reading data from Google Sheets.
 *
 * Supports authentication via API key (public sheets) or
 * service account credentials (private sheets).
 */
class GoogleSheetService
{
    private Sheets $sheets;

    /**
     * Initialize the Google Sheets client with available credentials.
     *
     * @throws \RuntimeException If no authentication method is configured.
     */
    public function __construct()
    {
        $client = new Client();
        $client->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false,
        ]));

        $credentialsPath = config('services.google.credentials_path');
        $apiKey = config('services.google.api_key');

        if ($apiKey) {
            $client->setDeveloperKey($apiKey);
        } elseif ($credentialsPath && file_exists($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Sheets::SPREADSHEETS_READONLY);
        } else {
            throw new \RuntimeException(
                'Google Sheets auth not configured. Set GOOGLE_API_KEY in .env for public sheets, '
                .'or place a service account JSON file as google-credentials.json for private sheets.'
            );
        }

        $this->sheets = new Sheets($client);
    }

    /**
     * Extract the spreadsheet ID from a full Google Sheets URL.
     *
     * @param  string  $url  Full Google Sheets URL or raw spreadsheet ID.
     * @return string The extracted spreadsheet ID.
     */
    public static function extractSheetId(string $url): string
    {
        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        return $url;
    }

    /**
     * Fetch all data rows from a spreadsheet tab as associative arrays.
     *
     * @param  string  $spreadsheetId  The Google Sheets spreadsheet ID.
     * @param  string  $range  The sheet tab name to read from.
     * @return array<int, array<string, string>> Rows keyed by header values.
     */
    public function getAllRows(string $spreadsheetId, string $range = 'QA Changes'): array
    {
        $response = $this->sheets->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) {
            return [];
        }

        $headers = array_shift($rows);

        return array_map(function ($row) use ($headers) {
            $row = array_pad($row, count($headers), '');
            $row = array_slice($row, 0, count($headers));

            return array_combine($headers, $row);
        }, $rows);
    }

    /**
     * Fetch a single row by its 1-based row number.
     *
     * @param  string  $spreadsheetId  The Google Sheets spreadsheet ID.
     * @param  int  $rowNumber  1-based row number (excluding the header).
     * @param  string  $range  The sheet tab name to read from.
     * @return array<string, string>|null The row data, or null if not found.
     */
    public function getRowByNumber(string $spreadsheetId, int $rowNumber, string $range = 'QA Changes'): ?array
    {
        $rows = $this->getAllRows($spreadsheetId, $range);

        return $rows[$rowNumber - 1] ?? null;
    }
}
