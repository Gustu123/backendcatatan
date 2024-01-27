<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetDetailController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\BudgetDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/update-password', [UserController::class, 'updatePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('wallet', WalletController::class)->only(['index', 'store', 'destroy', 'show']);
    Route::resource('debt', DebtController::class)->only(['index', 'store', 'destroy', 'show']);
    Route::resource('budget', BudgetController::class)->only(['index', 'store', 'destroy', 'show']);
    Route::resource('budgetdetail', BudgetDetailController::class)->only(['index']);
    // Route::post('/update-password', [UserController::class, 'updatePassword']);
    Route::get('/transaction/dashboard', [TransactionController::class, 'persentase']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::get('/transaction/excel/{cashout?}', [TransactionController::class, 'exportToExcel']);
    Route::get('/transaction/pdf/{cashout?}', [TransactionController::class, 'exportToPdf']);
    Route::resource('transaction', TransactionController::class)->only(['index', 'store', 'destroy', 'show', ]);
});




// Route::post('/forgot-password', function (Request $request) {
//     $request->validate(['email' => 'required|email']);

//     $status = Password::sendResetLink(
//         $request->only('email')
//     );
//     dd($status);
//     return $status === Password::RESET_LINK_SENT
//         ? back()->with(['status' => __($status)])
//         : back()->withErrors(['email' => __($status)]);
// })->middleware('guest')->name('password.email');

// Route::get('/reset-password/{token}', function (string $token) {
//     return view('auth.reset-password', ['token' => $token]);
// })->middleware('guest')->name('password.reset');

// Route::post('/reset-password', function (Request $request) {
//     $request->validate([
//         'token' => 'required',
//         'email' => 'required|email',
//         'password' => 'required|min:8|confirmed',
//     ]);

//     $status = Password::reset(
//         $request->only('email', 'password', 'password_confirmation', 'token'),
//         function (User $user, string $password) {
//             $user->forceFill([
//                 'password' => Hash::make($password)
//             ])->setRememberToken(Str::random(60));

//             $user->save();

//             event(new PasswordReset($user));
//         }
//     );

//     return $status === Password::PASSWORD_RESET
//         ? redirect()->route('login')->with('status', __($status))
//         : back()->withErrors(['email' => [__($status)]]);
// })->middleware('guest')->name('password.update');

// Route::get('/forgot-password', function () {
//     return view('auth.forgot-password');
// })->middleware('guest')->name('password.request');
// Route::post('/forgot-password', function (Request $request) {
//     $request->validate(['email' => 'required|email']); 

//     $status = password::sendResetLink(
//         $request->only('email')
//     );

//     return $status === password::RESET_LINK_SENT
//                 ? back()->with(['status' => __($status)])
//                 : back()->withErrors(['email' => __($status)]);

// })->middleware('guest')->name('password.email');
// Route::get('/reset-password/{token}', function (string $token) {
//     return view('auth.reset-password', ['token' => $token]);
// })->middleware('guest')->name('password.reset');

// Route::post('/reset-password', function (Request $request) {
//     $request->validate([
//         'token' => 'required',
//         'email' => 'required|email',
//         'password' => 'required|min:8|confirmed',
//     ]);

//     $status = Password::reset(
//         $request->only('email', 'password', 'password_confirmation', 'token'),
//         function (User $user, string $password) {
//             $user->forceFill([
//                 'password' => Hash::make($password)
//             ])->setRememberToken(Str::random(60));
 
//             $user->save();
 
//             event(new PasswordReset($user));
//         }
//     );

//     return $status === Password::PASSWORD_RESET
//     ? redirect()->route('login')->with('status', __($status))
//     : back()->withErrors(['email' => [__($status)]]);
// })->middleware('guest')->name('password.update');
