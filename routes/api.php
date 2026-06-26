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
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Routes protégées par Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
    });
});

// ─────────────────────────────────────────────────────────────────────────
// Routes protégées par Sanctum (utilisateur authentifié requis)
// ─────────────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ───────── PROFILE ─────────
    Route::prefix('profile')->group(function () {
        Route::get('/',          [ProfileController::class, 'show']);
        Route::put('/',          [ProfileController::class, 'update']);
        Route::put('/password',  [ProfileController::class, 'updatePassword']);
        Route::post('/photo',    [ProfileController::class, 'uploadPhoto']);
    });

    // ───────── USERS ─────────
    Route::prefix('users')->group(function () {
        // Listing : admin, chef_regiment, chef_groupe (scope dans le controller)
        Route::get('/', [UserController::class, 'index'])
            ->middleware('CheckUser:admin,chef_regiment,chef_groupe');

        // Détail : admin, CR, CG, candidat (lui-même) — Policy vérifie le scope
        Route::get('/{user}',        [UserController::class, 'show']);
        Route::put('/{user}',        [UserController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{user}',     [UserController::class, 'destroy'])
            ->middleware('CheckUser:admin');
        Route::get('/{user}/grade',  [UserController::class, 'showGrade']);
        Route::patch('/{user}/grade', [UserController::class, 'assignGrade'])
            ->middleware('CheckUser:admin,chef_groupe');
    });

    // ───────── VILLES ─────────
    Route::prefix('villes')->group(function () {
        Route::get('/',                      [VilleController::class, 'index']);
        Route::post('/',                     [VilleController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::get('/{ville}',              [VilleController::class, 'show']);
        Route::put('/{ville}',              [VilleController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{ville}',           [VilleController::class, 'destroy'])
            ->middleware('CheckUser:admin');
        Route::get('/{ville}/regiments',    [VilleController::class, 'regiments']);
        Route::get('/{ville}/events',       [VilleController::class, 'events']);
        Route::get('/{ville}/users',        [VilleController::class, 'users'])
            ->middleware('CheckUser:admin');
    });

    // ───────── REGIMENTS ─────────
    Route::prefix('regiments')->group(function () {
        Route::get('/',                          [RegimentController::class, 'index']);
        Route::post('/',                         [RegimentController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::get('/{regiment}',               [RegimentController::class, 'show']);
        Route::put('/{regiment}',               [RegimentController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{regiment}',            [RegimentController::class, 'destroy'])
            ->middleware('CheckUser:admin');
        Route::get('/{regiment}/groups',        [RegimentController::class, 'groups']);
        Route::get('/{regiment}/events',        [RegimentController::class, 'events']);
        Route::get('/{regiment}/users',         [RegimentController::class, 'users'])
            ->middleware('CheckUser:admin,chef_regiment');
        Route::patch('/{regiment}/chef',        [RegimentController::class, 'assignChef'])
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
        Route::get('/',           [GradeController::class, 'index']);
        Route::post('/',          [GradeController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::get('/{grade}',   [GradeController::class, 'show']);
        Route::put('/{grade}',   [GradeController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{grade}',[GradeController::class, 'destroy'])
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
        Route::get('/',                        [CategoryController::class, 'index']);
        Route::post('/',                       [CategoryController::class, 'store'])
            ->middleware('CheckUser:admin');
        Route::get('/{category}',             [CategoryController::class, 'show']);
        Route::put('/{category}',             [CategoryController::class, 'update'])
            ->middleware('CheckUser:admin');
        Route::delete('/{category}',          [CategoryController::class, 'destroy'])
            ->middleware('CheckUser:admin');
        Route::get('/{category}/guides',      [CategoryController::class, 'guides']);
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
