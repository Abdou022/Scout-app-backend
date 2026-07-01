<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\GroupApplicationController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegimentController;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VilleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Scout App
|--------------------------------------------------------------------------
| Authentification : Laravel Sanctum (install:api)
| Stockage fichiers : disque 'public' → exécuter : php artisan storage:link
| Pagination : toutes les listes paginent avec paginate(15) par défaut
| Tri : toutes les listes triées par created_at DESC
|--------------------------------------------------------------------------
*/

// ─────────────────────────────────────────────────────────────────────────
// AUTH — Routes publiques (sans authentification)
// ─────────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); //ok najem n7assen 
    Route::post('/login',    [AuthController::class, 'login']); //ok

    // Routes protégées par Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); //ok
        Route::get('/me',      [AuthController::class, 'me']); //ok
    });
});

// ─────────────────────────────────────────────────────────────────────────
// Routes protégées par Sanctum (utilisateur authentifié requis)
// ─────────────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ───────── PROFILE ─────────
    Route::prefix('profile')->group(function () {
        Route::get('/',          [ProfileController::class, 'show']); //ok, same as /me in auth
        Route::put('/',          [ProfileController::class, 'update']); //ok najem n7assen 
        Route::put('/password',  [ProfileController::class, 'updatePassword']); //ok
        Route::post('/photo',    [ProfileController::class, 'uploadPhoto']); //ok
    });

    // ───────── USERS ───────── 2
    Route::prefix('users')->group(function () {
        // Listing : admin, chef_regiment, chef_groupe (scope dans le controller)
        Route::get('/', [UserController::class, 'index']) // !!!
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');

        // Détail : admin, CR, CG, candidat (lui-même) — Policy vérifie le scope
        Route::get('/{user}',        [UserController::class, 'show']); // !!!
        Route::put('/{user}',        [UserController::class, 'update']) //ok
            ->middleware('CheckUser:admin');
        Route::delete('/{user}',     [UserController::class, 'destroy']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{user}/grade',  [UserController::class, 'showGrade']); //ok
        Route::patch('/{user}/grade', [UserController::class, 'assignGrade']) //ok
            ->middleware('CheckUser:admin,chef_groupe');
    });

    // ───────── VILLES ─────────
    Route::prefix('villes')->group(function () {
        Route::get('/',                      [VilleController::class, 'index']); //ok
        Route::post('/',                     [VilleController::class, 'store']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{ville}',              [VilleController::class, 'show']); //ok
        Route::put('/{ville}',              [VilleController::class, 'update']) //ok
            ->middleware('CheckUser:admin');
        Route::delete('/{ville}',           [VilleController::class, 'destroy']) //ok (cant delete when users, regiments are linked)
            ->middleware('CheckUser:admin');
        Route::get('/{ville}/regiments',    [VilleController::class, 'regiments']);
        Route::get('/{ville}/events',       [VilleController::class, 'events']);
        Route::get('/{ville}/users',        [VilleController::class, 'users']) //ok
            ->middleware('CheckUser:admin');
    });

    // ───────── REGIMENTS ─────────
    Route::prefix('regiments')->group(function () {
        Route::get('/',                          [RegimentController::class, 'index']); //ok
        Route::post('/',                         [RegimentController::class, 'store']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{regiment}',               [RegimentController::class, 'show']); //ok
        Route::put('/{regiment}',               [RegimentController::class, 'update']) //ok
            ->middleware('CheckUser:admin');
        Route::delete('/{regiment}',            [RegimentController::class, 'destroy']) //ok (cant delete when groups and events are linked, users and chef will be detached if deleted)
            ->middleware('CheckUser:admin');
        Route::get('/{regiment}/groups',        [RegimentController::class, 'groups']);
        Route::get('/{regiment}/events',        [RegimentController::class, 'events']);
        Route::get('/{regiment}/users',         [RegimentController::class, 'users']) //ok (only admin can check and the chef of this regiment, other chefs can't check other regiments users)
            ->middleware('CheckUser:admin,chef_regiment');
        Route::patch('/{regiment}/chef',        [RegimentController::class, 'assignChef']) //ok
            ->middleware('CheckUser:admin');
    });

    // ───────── GROUPS ─────────
    Route::prefix('groups')->group(function () {
        Route::get('/',                                      [GroupController::class, 'index']);
        Route::post('/',                                     [GroupController::class, 'store'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::get('/{group}',                              [GroupController::class, 'show']);
        Route::put('/{group}',                              [GroupController::class, 'update'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::delete('/{group}',                           [GroupController::class, 'destroy'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::get('/{group}/members',                      [GroupController::class, 'members'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');
        Route::delete('/{group}/members/{user}',            [GroupController::class, 'removeMember'])
            ->middleware('CheckUser:admin,chef_groupe');
        Route::patch('/{group}/chef',                       [GroupController::class, 'assignChef'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::patch('/{group}/assistant',                  [GroupController::class, 'assignAssistant'])
            ->middleware('CheckUser:admin,chef_groupe');
        Route::get('/{group}/activities',                   [GroupController::class, 'activities'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe,candidat');
        Route::get('/{group}/applications',                 [GroupController::class, 'applications'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');
    });

    // ───────── GRADES ─────────
    Route::prefix('grades')->group(function () {
        Route::get('/',           [GradeController::class, 'index']); //ok
        Route::post('/',          [GradeController::class, 'store']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{grade}',   [GradeController::class, 'show']); //ok
        Route::post('/{grade}',   [GradeController::class, 'update']) //ok  had to change from put to update to get rid of form-data bugs
            ->middleware('CheckUser:admin');
        Route::delete('/{grade}',[GradeController::class, 'destroy']) //ok (cascade, when i delete a grade linked users will have null in grade_id)
            ->middleware('CheckUser:admin');
    });

    // ───────── CANDIDATURES ─────────
    Route::prefix('applications')->group(function () {
        // Listing avec filtrage dans le controller selon le rôle
        Route::get('/', [GroupApplicationController::class, 'index'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe,candidat');
        // Soumettre une candidature : candidat uniquement
        Route::post('/', [GroupApplicationController::class, 'store'])
            ->middleware('CheckUser:candidat');
        Route::get('/{application}',          [GroupApplicationController::class, 'show']);
        Route::patch('/{application}/accept', [GroupApplicationController::class, 'accept'])
            ->middleware('CheckUser:admin,chef_groupe');
        Route::patch('/{application}/refuse', [GroupApplicationController::class, 'refuse'])
            ->middleware('CheckUser:admin,chef_groupe');
        Route::delete('/{application}',       [GroupApplicationController::class, 'destroy'])
            ->middleware('CheckUser:candidat');
    });

    // ───────── CATEGORIES ─────────
    Route::prefix('categories')->group(function () {
        Route::get('/',                        [CategoryController::class, 'index']); //ok
        Route::post('/',                       [CategoryController::class, 'store']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{category}',             [CategoryController::class, 'show']); //ok
        Route::put('/{category}',             [CategoryController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{category}',          [CategoryController::class, 'destroy']) //ok
            ->middleware('CheckUser:admin');
        Route::get('/{category}/guides',      [CategoryController::class, 'guides']); //ok
    });

    // ───────── SONGS ─────────
    Route::prefix('songs')->group(function () {
        Route::get('/',               [SongController::class, 'index']);
        Route::post('/',              [SongController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::get('/{song}',        [SongController::class, 'show']);
        Route::put('/{song}',        [SongController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{song}',     [SongController::class, 'destroy'])
            ->middleware('CheckUser:admin');
        Route::post('/{song}/audio', [SongController::class, 'uploadAudio'])
            ->middleware('CheckUser:admin');
    });

    // ───────── GUIDES ─────────
    Route::prefix('guides')->group(function () {
        Route::get('/',                [GuideController::class, 'index']);
        Route::post('/',              [GuideController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::post('/upload-image',  [GuideController::class, 'uploadImage'])
            ->middleware('CheckUser:admin');
        Route::post('/upload-video',  [GuideController::class, 'uploadVideo'])
            ->middleware('CheckUser:admin');
        Route::get('/{guide}',        [GuideController::class, 'show']);
        Route::put('/{guide}',        [GuideController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{guide}',     [GuideController::class, 'destroy'])
            ->middleware('CheckUser:admin');
    });

    // ───────── EVENTS ─────────
    Route::prefix('events')->group(function () {
        // Listing filtré automatiquement par la ville de l'utilisateur
        Route::get('/',           [EventController::class, 'index']);
        Route::post('/',          [EventController::class, 'store'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::get('/{event}',   [EventController::class, 'show']);
        Route::put('/{event}',   [EventController::class, 'update'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::delete('/{event}',[EventController::class, 'destroy'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::get('/{event}/attendance', [EventController::class, 'attendance'])
            ->middleware('CheckUser:admin,chef_regiment');
    });

    // ───────── ACTIVITIES ─────────
    Route::prefix('activities')->group(function () {
        Route::get('/',                [ActivityController::class, 'index'])
            ->middleware('CheckUser:admin,chef_groupe,candidat');
        Route::post('/',              [ActivityController::class, 'store'])
            ->middleware('CheckUser:chef_groupe');
        Route::get('/{activity}',     [ActivityController::class, 'show'])
            ->middleware('CheckUser:admin,chef_groupe,candidat');
        Route::put('/{activity}',     [ActivityController::class, 'update'])
            ->middleware('CheckUser:chef_groupe');
        Route::delete('/{activity}',  [ActivityController::class, 'destroy'])
            ->middleware('CheckUser:chef_groupe');
        Route::get('/{activity}/attendance', [ActivityController::class, 'attendance'])
            ->middleware('CheckUser:admin,chef_groupe');
    });

    // ───────── ATTENDANCE ─────────
    Route::prefix('attendance')->group(function () {
        Route::get('/',                          [AttendanceController::class, 'index'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');
        Route::post('/',                         [AttendanceController::class, 'store'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');
        // Route spécifique avant {attendance} pour éviter le conflit de paramètre
        Route::get('/user/{user}',              [AttendanceController::class, 'userAttendances']);
        Route::get('/{attendance}',             [AttendanceController::class, 'show']);
        Route::put('/{attendance}',             [AttendanceController::class, 'update'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');
        Route::delete('/{attendance}',          [AttendanceController::class, 'destroy'])
            ->middleware('CheckUser:admin');
    });
});
