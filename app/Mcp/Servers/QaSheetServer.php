<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetQaPointTool;
use App\Mcp\Tools\ListQaPointsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('qa-sheet-bridge')]
#[Version('1.0.0')]
#[Instructions('Use this server to fetch QA issues from Google Sheets. Provide the full Google Sheet URL and a QA point number to retrieve specific issues. Data may be in Bulgarian (Cyrillic).')]
/**
 * MCP server that exposes QA sheet tools to AI assistants.
 */
class QaSheetServer extends Server
{
    /** @var array<int, class-string<\Laravel\Mcp\Server\Tool>> */
    protected array $tools = [
        ListQaPointsTool::class,
        GetQaPointTool::class,
    ];
}
