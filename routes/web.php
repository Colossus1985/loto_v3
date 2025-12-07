<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard',                                    [DashboardController::class, 'index'])->name('dashboard');

// Routes pour les personnes
Route::resource('persons',      PersonController::class);
Route::post('persons/{person}/join-group',                  [PersonController::class, 'joinGroup'])->name('persons.join-group');
Route::get('persons/{person}/transactions-data',            [PersonController::class, 'getTransactionsData'])->name('persons.transactions-data');

// Routes pour les groupes
Route::resource('groups',       GroupController::class);
Route::post('groups/{group}/add-person',                    [GroupController::class, 'addPerson'])->name('groups.add-person');
Route::delete('groups/{group}/remove-person/{person}',      [GroupController::class, 'removePerson'])->name('groups.remove-person');
Route::post('groups/{group}/add-funds/{person}',            [GroupController::class, 'addFunds'])->name('groups.add-funds');
Route::post('groups/{group}/transfer-from-floating/{person}', [GroupController::class, 'transferFromFloating'])->name('groups.transfer-from-floating');
Route::get('groups/{group}/games-data',                     [GroupController::class, 'getGamesData'])->name('groups.games-data');

// Routes pour les jeux
Route::get('groups/{group}/play',                           [GameController::class, 'create'])->name('games.create');
Route::post('groups/{group}/play',                          [GameController::class, 'store'])->name('games.store');

// Routes pour les gains
Route::get('games/{game}/win',                              [GameController::class, 'showWinForm'])->name('games.win');
Route::post('games/{game}/win',                             [GameController::class, 'recordWin'])->name('games.record-win');        