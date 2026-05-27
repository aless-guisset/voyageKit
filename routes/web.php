<?php

use App\Http\Controllers\TripController;
use App\Http\Controllers\TripGroupController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ItineraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('trips.index'));

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Voyages ───────────────────────────────────────────────────────────────
    Route::resource('trips', TripController::class);

    // ── Groupes & Membres ─────────────────────────────────────────────────────
    Route::prefix('trips/{trip}')->name('trips.')->group(function () {
        Route::get('/groups',                                                      [TripGroupController::class, 'index'])->name('groups.index');
        Route::post('/groups',                                                     [TripGroupController::class, 'storeGroup'])->name('groups.store');
        Route::patch('/groups/{group}',                                            [TripGroupController::class, 'updateGroup'])->name('groups.update');
        Route::delete('/groups/{group}',                                           [TripGroupController::class, 'destroyGroup'])->name('groups.destroy');
        Route::post('/groups/{group}/members',                                     [TripGroupController::class, 'storeMember'])->name('groups.members.store');
        Route::patch('/groups/{group}/members/{member}',                           [TripGroupController::class, 'updateMember'])->name('groups.members.update');
        Route::delete('/groups/{group}/members/{member}',                          [TripGroupController::class, 'destroyMember'])->name('groups.members.destroy');
        Route::post('/groups/{group}/members/{member}/lists',                      [TripGroupController::class, 'storeMemberList'])->name('groups.members.lists.store');
        Route::patch('/members/{member}/budget',                                   [TripGroupController::class, 'updateMemberBudget'])->name('members.budget.update');
    });

    // ── Itinéraires ──────────────────────────────────────────────────────────
    Route::prefix('trips/{trip}/itinerary')->name('trips.itinerary.')->group(function () {
        Route::get('/',                              [ItineraryController::class, 'show'])->name('show');
        Route::post('/generate-days',               [ItineraryController::class, 'generateDays'])->name('generate-days');
        Route::post('/days',                         [ItineraryController::class, 'storeDay'])->name('days.store');
        Route::patch('/days/{day}',                  [ItineraryController::class, 'updateDay'])->name('days.update');
        Route::delete('/days/{day}',                 [ItineraryController::class, 'destroyDay'])->name('days.destroy');
        Route::post('/days/{day}/events',            [ItineraryController::class, 'storeEvent'])->name('events.store');
        Route::patch('/days/{day}/events/{event}',   [ItineraryController::class, 'updateEvent'])->name('events.update');
        Route::delete('/days/{day}/events/{event}',  [ItineraryController::class, 'destroyEvent'])->name('events.destroy');
    });

    // ── Budget ────────────────────────────────────────────────────────────────
    Route::prefix('trips/{trip}/budget')->name('trips.budget.')->group(function () {
        Route::get('/',                          [BudgetController::class, 'show'])->name('show');
        Route::patch('/',                        [BudgetController::class, 'update'])->name('update');
        Route::post('/incomes',                  [BudgetController::class, 'storeIncome'])->name('incomes.store');
        Route::patch('/incomes/{income}',        [BudgetController::class, 'updateIncome'])->name('incomes.update');
        Route::delete('/incomes/{income}',       [BudgetController::class, 'destroyIncome'])->name('incomes.destroy');
        Route::post('/expenses',                 [BudgetController::class, 'storeExpense'])->name('expenses.store');
        Route::patch('/expenses/{expense}',      [BudgetController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('/expenses/{expense}',     [BudgetController::class, 'destroyExpense'])->name('expenses.destroy');
        Route::post('/activities',               [BudgetController::class, 'storeActivity'])->name('activities.store');
        Route::patch('/activities/{activity}',   [BudgetController::class, 'updateActivity'])->name('activities.update');
        Route::delete('/activities/{activity}',  [BudgetController::class, 'destroyActivity'])->name('activities.destroy');
    });

    // ── Listes de packing ─────────────────────────────────────────────────────
    Route::get('/lists',                                    [PackingListController::class, 'index'])->name('packing.index');
    Route::post('/lists',                                   [PackingListController::class, 'store'])->name('packing.store');
    Route::get('/lists/{packingList}',                      [PackingListController::class, 'show'])->name('packing.show');
    Route::delete('/lists/{packingList}',                   [PackingListController::class, 'destroy'])->name('packing.destroy');
    Route::post('/lists/{packingList}/items',               [PackingListController::class, 'storeItem'])->name('packing.items.store');
    Route::patch('/lists/{packingList}/items/{item}',       [PackingListController::class, 'updateItem'])->name('packing.items.update');
    Route::delete('/lists/{packingList}/items/{item}',      [PackingListController::class, 'destroyItem'])->name('packing.items.destroy');
    Route::post('/lists/{packingList}/save-template',       [PackingListController::class, 'saveAsTemplate'])->name('packing.save-template');
    Route::post('/templates',                               [PackingListController::class, 'storeTemplate'])->name('templates.store');
});

require __DIR__.'/auth.php';
