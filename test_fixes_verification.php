<?php
/**
 * VERIFICATION SCRIPT FOR CRITICAL FIXES
 * Sistema de Gestión Hotelera
 *
 * This script verifies the fixes for:
 * 1. Notification System for Pending Reservations
 * 2. CSS Icon Size Issues in Client Table
 */

echo "🔍 VERIFICATION SCRIPT FOR CRITICAL FIXES\n";
echo "==========================================\n\n";

// Test 1: Notification System
echo "📧 TESTING NOTIFICATION SYSTEM...\n";
echo "-----------------------------------\n";

// Check if Recepcionista users exist
$recepcionistas = \App\Models\User::role('Recepcionista')->active()->get();
echo "✓ Recepcionistas found: " . $recepcionistas->count() . "\n";

if ($recepcionistas->count() > 0) {
    $recepcionista = $recepcionistas->first();

    // Check current unread notifications
    $currentUnread = $recepcionista->unreadNotifications()->count();
    echo "✓ Current unread notifications: $currentUnread\n";

    // Find a test reservation
    $reserva = \App\Models\Reserva::where('estado', 'Pendiente de Confirmación')->first();

    if ($reserva) {
        echo "✓ Found test reservation ID: " . $reserva->id . "\n";
        echo "✓ Reservation client: " . $reserva->nombre_cliente . "\n";
        echo "✓ Room number: " . $reserva->habitacion->numero . "\n";

        // Send test notification
        $recepcionista->notify(new \App\Notifications\ReservaPendienteNotification($reserva, 'TEST: System verification notification'));
        echo "✓ Test notification sent\n";

        // Process queue
        echo "⏳ Processing notification queue...\n";
        \Artisan::call('queue:work', ['--tries' => 1, '--timeout' => 30, '--stop-when-empty' => true]);

        // Check if notification was created
        $newUnread = $recepcionista->fresh()->unreadNotifications()->count();

        if ($newUnread > $currentUnread) {
            echo "✅ NOTIFICATION SYSTEM: WORKING\n";
            echo "   - Unread notifications increased from $currentUnread to $newUnread\n";

            // Get the latest notification details
            $latestNotification = $recepcionista->fresh()->unreadNotifications()->latest()->first();
            if ($latestNotification) {
                echo "   - Latest notification message: " . ($latestNotification->data['mensaje'] ?? 'N/A') . "\n";
                echo "   - Notification type: " . ($latestNotification->data['tipo'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ NOTIFICATION SYSTEM: FAILED\n";
            echo "   - No new notifications were created\n";
        }
    } else {
        echo "⚠️  No pending reservations found for testing\n";
    }
} else {
    echo "❌ No Recepcionista users found in system\n";
}

echo "\n";

// Test 2: CSS Files
echo "🎨 TESTING CSS FIXES...\n";
echo "------------------------\n";

$cssFile = public_path('css/admin_custom.css');

if (file_exists($cssFile)) {
    echo "✓ CSS file found: $cssFile\n";

    $cssContent = file_get_contents($cssFile);

    // Check for icon size fixes
    if (strpos($cssContent, '.table .btn i') !== false) {
        echo "✅ CSS ICON FIXES: APPLIED\n";
        echo "   - Table button icon size constraints added\n";

        // Extract the specific fix
        if (preg_match('/\.table \.btn i \{[^}]+\}/s', $cssContent, $matches)) {
            echo "   - Fix details: " . trim($matches[0]) . "\n";
        }
    } else {
        echo "❌ CSS ICON FIXES: NOT FOUND\n";
        echo "   - Icon size constraints missing\n";
    }

    // Check for FontAwesome fixes
    if (strpos($cssContent, '.fas, .far, .fab') !== false) {
        echo "✓ FontAwesome global fixes applied\n";
    } else {
        echo "⚠️  FontAwesome global fixes not found\n";
    }
} else {
    echo "❌ CSS file not found: $cssFile\n";
}

echo "\n";

// Test 3: API Controller Fix
echo "🔧 TESTING API CONTROLLER FIXES...\n";
echo "-----------------------------------\n";

$apiControllerFile = app_path('Http/Controllers/Api/ReservaApiController.php');

if (file_exists($apiControllerFile)) {
    echo "✓ API Controller file found\n";

    $controllerContent = file_get_contents($apiControllerFile);

    // Check for notification fix
    if (strpos($controllerContent, 'recepcionistas = \\App\\Models\\User::role(\'Recepcionista\')->active()->get()') !== false) {
        echo "✅ API NOTIFICATION FIX: APPLIED\n";
        echo "   - Receptionist notification code added to API reservation creation\n";

        if (strpos($controllerContent, 'ReservaPendienteNotification') !== false) {
            echo "   - ReservaPendienteNotification import confirmed\n";
        }
    } else {
        echo "❌ API NOTIFICATION FIX: NOT FOUND\n";
        echo "   - Missing receptionist notification code in API\n";
    }
} else {
    echo "❌ API Controller file not found\n";
}

echo "\n";

// Summary
echo "📊 VERIFICATION SUMMARY\n";
echo "========================\n";
echo "Issue 1 - Notification System: " . ($newUnread > $currentUnread ? "✅ FIXED" : "❌ NEEDS ATTENTION") . "\n";
echo "Issue 2 - CSS Icon Sizes: " . (strpos($cssContent ?? '', '.table .btn i') !== false ? "✅ FIXED" : "❌ NEEDS ATTENTION") . "\n";
echo "Issue 3 - API Notifications: " . (strpos($controllerContent ?? '', 'recepcionistas = \\App\\Models\\User::role') !== false ? "✅ FIXED" : "❌ NEEDS ATTENTION") . "\n";

echo "\n";
echo "🎯 NEXT STEPS:\n";
echo "===============\n";
echo "1. Visit http://localhost:8001/clientes to verify table icon sizes\n";
echo "2. Check dashboard notifications for pending reservations\n";
echo "3. Create a test reservation via landing page to verify end-to-end flow\n";
echo "4. Ensure queue worker is running: php artisan queue:work\n";

echo "\n✨ Verification completed!\n";