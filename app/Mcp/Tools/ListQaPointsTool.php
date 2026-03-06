<?php

namespace App\Mcp\Tools;

use App\Services\GoogleSheetService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all QA points from a Google Sheet. Returns every row with all columns. Pass the full Google Sheet URL.')]
class ListQaPointsTool extends Tool
{
    public function __construct(private GoogleSheetService $sheetService) {}

    /**
     * Fetch all QA points from the given Google Sheet.
     */
    public function handle(Request $request): Response
    {
        $sheetUrl = $request->get('sheet_url');
        $spreadsheetId = GoogleSheetService::extractSheetId($sheetUrl);
        $rows = $this->sheetService->getAllRows($spreadsheetId);

        return Response::text(json_encode([
            'total' => count($rows),
            'points' => $rows,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Define the JSON schema for this tool's input parameters.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'sheet_url' => $schema->string()
                ->description('The full Google Sheet URL (e.g. https://docs.google.com/spreadsheets/d/...)')
                ->required(),
        ];
    }
}
