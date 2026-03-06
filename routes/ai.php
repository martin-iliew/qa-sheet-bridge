<?php

use App\Mcp\Servers\QaSheetServer;
use Laravel\Mcp\Facades\Mcp;

// Web server — accessible via HTTP (for remote AI clients like Claude.ai)
Mcp::web('/mcp', QaSheetServer::class);

// Local server — accessible via stdio (for Claude Code, Cursor, etc.)
Mcp::local('qa-sheets', QaSheetServer::class);
