<?php

namespace App\Mcp\Tools;

use App\Services\GoogleSheetService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a specific QA point by its ID (first column) from a Google Sheet. Pass the full Google Sheet URL and the QA point number.')]
class GetQaPointTool extends Tool
{
    public function __construct(private GoogleSheetService $sheetService) {}

    /**
     * Fetch a single QA point by its row number from the given Google Sheet.
     */
    public function handle(Request $request): Response
    {
        $sheetUrl = $request->get('sheet_url');
        $pointId = $request->get('point_id');

        $spreadsheetId = GoogleSheetService::extractSheetId($sheetUrl);
        $row = $this->sheetService->getRowByNumber($spreadsheetId, $pointId);

        if (! $row) {
            return Response::text(json_encode([
                'error' => "QA point #{$pointId} not found.",
            ], JSON_UNESCAPED_UNICODE));
        }

        return Response::text(json_encode([
            'point_id' => $pointId,
            'data' => $row,
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
            'point_id' => $schema->integer()
                ->description('The QA point ID (value in the first column)')
                ->required(),
        ];
    }
}
