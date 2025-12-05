<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    /**
     * Clear and rebuild all caches
     * URL: /clear?token=YOUR_SECRET_TOKEN
     * 
     * Set CACHE_CLEAR_TOKEN in .env file for security
     */
    public function clear(Request $request)
    {
        // Simple token-based security (optional but recommended)
        $token = env('CACHE_CLEAR_TOKEN', 'change-me-in-production');
        $providedToken = $request->get('token');
        
        if ($token !== 'change-me-in-production' && $providedToken !== $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid token.',
                'hint' => 'Add ?token=YOUR_TOKEN to the URL. Set CACHE_CLEAR_TOKEN in .env file.'
            ], 401);
        }

        $output = [];
        $output[] = "Starting cache clear and rebuild process...\n";

        try {
            // Clear caches
            $output[] = "1. Clearing config cache...";
            Artisan::call('config:clear');
            $output[] = "   ✓ Config cache cleared";
            
            $output[] = "2. Clearing application cache...";
            Artisan::call('cache:clear');
            $output[] = "   ✓ Application cache cleared";
            
            $output[] = "3. Clearing route cache...";
            Artisan::call('route:clear');
            $output[] = "   ✓ Route cache cleared";
            
            $output[] = "4. Clearing view cache...";
            Artisan::call('view:clear');
            $output[] = "   ✓ View cache cleared";
            
            $output[] = "5. Clearing all caches (optimize:clear)...";
            Artisan::call('optimize:clear');
            $output[] = "   ✓ All caches cleared\n";
            
            // Rebuild caches for production
            $output[] = "6. Caching configuration...";
            Artisan::call('config:cache');
            $output[] = "   ✓ Configuration cached";
            
            $output[] = "7. Caching routes...";
            Artisan::call('route:cache');
            $output[] = "   ✓ Routes cached";
            
            $output[] = "8. Caching views...";
            Artisan::call('view:cache');
            $output[] = "   ✓ Views cached";
            
            $output[] = "\n✅ All cache operations completed successfully!";
            
            // Return as HTML for browser viewing
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cache cleared and rebuilt successfully',
                    'output' => $output
                ]);
            }
            
            // Return as HTML
            $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Cleared - Warehouse Management System</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FF9900;
            margin-bottom: 20px;
        }
        .output {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #FF9900;
            font-family: "Courier New", monospace;
            white-space: pre-line;
            line-height: 1.6;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
        a {
            color: #FF9900;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Cache Management</h1>
        <div class="output">' . implode("\n", $output) . '</div>
        <div class="info">
            <strong>Note:</strong> All caches have been cleared and rebuilt for optimal performance.
            <br><br>
            <a href="/dashboard">← Go to Dashboard</a> | 
            <a href="/clear?token=' . urlencode($providedToken ?? '') . '">🔄 Refresh</a>
        </div>
    </div>
</body>
</html>';
            
            return response($html);
            
        } catch (\Exception $e) {
            $errorOutput = array_merge($output, [
                "\n❌ Error occurred:",
                $e->getMessage(),
                "\nStack trace:",
                $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing cache',
                    'error' => $e->getMessage(),
                    'output' => $errorOutput
                ], 500);
            }
            
            return response('
                <!DOCTYPE html>
                <html>
                <head><title>Cache Error</title></head>
                <body style="font-family: Arial; padding: 50px;">
                    <h1 style="color: red;">❌ Error Clearing Cache</h1>
                    <pre style="background: #f5f5f5; padding: 20px; border-radius: 5px;">' . 
                    htmlspecialchars(implode("\n", $errorOutput)) . 
                    '</pre>
                    <p><a href="/dashboard">← Go to Dashboard</a></p>
                </body>
                </html>
            ', 500);
        }
    }
}

