<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * DebugController
 *
 * Diagnostic-only controller used to inspect the users_central table and
 * verify authentication configuration when users report they cannot log in.
 *
 * This controller is intentionally NOT meant for production traffic. Access
 * is gated in the route definition (local environment or a valid debug
 * token) — see routes/web.php.
 */
class DebugController extends Controller
{
    /**
     * Diagnostic endpoint: GET /debug/users
     *
     * Inspects the users_central table and reports:
     * - table existence + row count
     * - list of users (id, name, email, role, is_active, created_at)
     * - password hashing sanity check
     * - database connection status
     */
    public function users(Request $request): JsonResponse
    {
        $result = [
            'timestamp' => now()->toDateTimeString(),
            'database_connection' => null,
            'table_exists' => false,
            'row_count' => null,
            'users' => [],
            'password_hash_check' => null,
            'errors' => [],
        ];

        // 1. Test database connection status
        try {
            DB::connection()->getPdo();
            $result['database_connection'] = [
                'status' => 'connected',
                'driver' => DB::connection()->getDriverName(),
                'database' => DB::connection()->getDatabaseName(),
            ];
        } catch (Throwable $e) {
            $result['database_connection'] = [
                'status' => 'failed',
                'message' => $e->getMessage(),
            ];

            // No point continuing if we can't even connect.
            return response()->json($result, 500);
        }

        // 2. Check if users_central table exists and get row count
        try {
            $tableExists = Schema::hasTable('users_central');
            $result['table_exists'] = $tableExists;

            if (!$tableExists) {
                $result['errors'][] = "Table 'users_central' does not exist.";
                return response()->json($result, 404);
            }

            $result['row_count'] = DB::table('users_central')->count();

            // 3. List all users with relevant columns
            $availableColumns = Schema::getColumnListing('users_central');
            $wantedColumns = ['id', 'name', 'email', 'role', 'is_active', 'created_at'];
            $selectColumns = array_values(array_intersect($wantedColumns, $availableColumns));

            if (empty($selectColumns)) {
                $result['errors'][] = 'None of the expected columns exist on users_central.';
            } else {
                $result['users'] = DB::table('users_central')
                    ->select($selectColumns)
                    ->orderBy('id')
                    ->get();
            }
        } catch (Throwable $e) {
            $result['errors'][] = 'Error querying users_central: ' . $e->getMessage();
            return response()->json($result, 500);
        }

        // 4. Verify password hashing is working
        try {
            $testPassword = 'debug-test-password-123';
            $testHash = Hash::make($testPassword);
            $result['password_hash_check'] = [
                'hash_driver' => config('hashing.driver'),
                'can_hash' => !empty($testHash),
                'can_verify' => Hash::check($testPassword, $testHash),
            ];

            // Optionally verify one of the known seeded users' credentials
            // (only if the email exists and password check succeeds/fails safely).
            $knownCredentials = [
                'admin@trimurti.edu' => 'admin123',
                'guru@trimutti.edu' => 'guru123',
                'siswa@trimurti.edu' => 'siswa123',
            ];

            $credentialChecks = [];
            foreach ($knownCredentials as $email => $plainPassword) {
                $user = DB::table('users_central')->where('email', $email)->first();

                if (!$user) {
                    $credentialChecks[$email] = 'user_not_found';
                    continue;
                }

                if (empty($user->password)) {
                    $credentialChecks[$email] = 'no_password_hash_stored';
                    continue;
                }

                $credentialChecks[$email] = Hash::check($plainPassword, $user->password)
                    ? 'password_matches'
                    : 'password_does_not_match';
            }

            $result['password_hash_check']['credential_checks'] = $credentialChecks;
        } catch (Throwable $e) {
            $result['errors'][] = 'Error verifying password hashing: ' . $e->getMessage();
        }

        return response()->json($result);
    }
}
