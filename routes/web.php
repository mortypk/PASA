<?php

use App\Http\Controllers\authentications\LoginBasic;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AncestorDataController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountiesController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatesController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\FriendMemberFormWizard;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\JuniorMemberFormWizard;
use App\Http\Controllers\MemberFormWizard;
use App\Http\Controllers\ModeOfArrivalsController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\PartnerMemberFormWizard;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortsController;
use App\Http\Controllers\RigController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SourceOfArrivalController;
use App\Http\Controllers\SubscribeMemberController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\AncestorLocalTravelDetailController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\MembersContactController;
use App\Http\Controllers\AncestorInternationalTravelDetailController;
use App\Http\Controllers\AncestorSpouseController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GlCodeController;
use App\Http\Controllers\GlCodesParentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::view('/membership-account','membership.welcome')->name('member');
// Route::get('/membership-account/level/{level}', MemberFormWizard::class)->name('level');
// Route::post('/membership-account/subscribe',[\App\Http\Controllers\SubscribeMemberController::class,'subscribe'])->name('subscribe');

Route::get('/membership/primary', [MemberFormWizard::class, "MemberFormWizard"])->name('level');
Route::post('/membership/primary', [MemberFormWizard::class, "MemberFormWizard"])->name('submitMembershipAccount');
Route::get('/membership/friend', [FriendMemberFormWizard::class, "FriendMemberFormWizard"])->name('friendForm');
Route::post('/membership/friend', [FriendMemberFormWizard::class, "FriendMemberFormWizard"])->name('submitFriendMembershipAccount');

Route::get('/membership/confirm-payment-intent', [PaymentController::class, "confirmPaymentIntent"])->name("confirmPaymentIntent");

Route::get('/fresh-db', function () {
    Artisan::call("optimize:clear");
    Artisan::call("cache:clear");
    // Artisan::call("view:clear");
    // Artisan::call("route:clear");
    // Artisan::call("clear-compiled");
    Artisan::call("config:cache");
    // Artisan::call('migrate');
    // Artisan::call('db:seed');
    dd('reset!');
});

// Route::get("reset",fn()=>dd(Hash::make("password")));

Route::get("env-clear", function () {
    Artisan::call("config:cache");
    Artisan::call("config:clear");
});

Route::get("login", [LoginBasic::class, "index"])->name("login");
Route::post("logout", [LoginBasic::class, "logout"])->name("logout");
Route::post("login", [LoginBasic::class, "login"])->name("loginRequest");
Route::group(['middleware' => ['auth']], function () {
    Route::get('members/view-pedigree/{id}', [SubscribeMemberController::class, 'viewPedigree'])->name("members.view-pedigree");
    Route::get('members/view-member/{id}', [SubscribeMemberController::class, 'viewMember'])->name("members.view-member");
    Route::get('members/edit-member/{id}', [SubscribeMemberController::class, 'editMember'])->name("members.edit-member");
    Route::post('members/edit-member/{id}', [SubscribeMemberController::class, 'memberDetailUpdate'])->name("members.detail-update");

    Route::resource('members', SubscribeMemberController::class);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::resource('/', DashboardController::class);
    Route::resource('/user', UserController::class);
    Route::resource('/ship', ShipController::class);
    Route::resource('mode-of-arrivals', ModeOfArrivalsController::class);
    //Route::resource('states', StatesController::class);
    Route::resource('ports', PortsController::class);
    Route::resource('counties', CountiesController::class);
    Route::resource('occupations', OccupationController::class);
    Route::resource('rigs', RigController::class);
    Route::resource('ancestor-data', AncestorDataController::class);
    Route::resource('ancestor-local-travel-details', AncestorLocalTravelDetailController::class);
    Route::resource('members-contacts', MembersContactController::class);
    Route::resource('ancestor_spouses', AncestorSpouseController::class);

    Route::resource('roles', RoleController::class);
    Route::resource('source-of-arrivals', SourceOfArrivalController::class);
    Route::resource('cities', CityController::class);
    Route::resource('countries', CountriesController::class);
    Route::get('payment-list', [PaymentController::class, "index"])->name("payment.list");
    Route::get('payment-list-user', [PaymentController::class, "index2"])->name("payment.list.user");
    Route::get('profile', [DashboardController::class, "profile"])->name("profile");
    Route::get('juniors', [DashboardController::class, "juniors"])->name("juniors");
    Route::get('partner', [DashboardController::class, "partner"])->name("partner");

    // add junior
    Route::get('/membership/junior', [JuniorMemberFormWizard::class, "JuniorMemberFormWizard"])->name('JuniorForm');
    Route::post('/membership/junior', [JuniorMemberFormWizard::class, "JuniorMemberFormWizard"])->name('submitJuniorMembershipAccount');
    Route::get('/membership/siblings/{junior}', [JuniorMemberFormWizard::class, "JuniorSiblings"])->name('JuniorSiblings');
    Route::match(["GET", "PATCH"], '/membership/siblings/edit/{sibling}', [JuniorMemberFormWizard::class, "editJuniorSibling"])->name('editJuniorSibling');
    // add partner
    Route::get('/membership/partner', [PartnerMemberFormWizard::class, "PartnerMemberFormWizard"])->name('ParterForm');
    Route::post('/membership/partner', [PartnerMemberFormWizard::class, "PartnerMemberFormWizard"])->name('submitParterMembershipAccount');
    Route::patch('/membership/partner', [PartnerMemberFormWizard::class, "UpdatePartnerMemberFormWizard"])->name('UpdatePartnerMemberFormWizard');

    Route::get('rig-select2', [ShipController::class, 'getRigJson'])->name('rig.select2');
    Route::post('ship-select2', [ShipController::class, 'getShipJson'])->name('ship.select2');
    Route::post('counties-select2', [ShipController::class, 'getCountiesJson'])->name('counties.select2');
    Route::post('ports-select2', [ShipController::class, 'getPortsJson'])->name('ports.select2');
    Route::post('occupation-select2', [OccupationController::class, 'getOccupationJson'])->name('occupation.select2');
    Route::post('mode-of-arrival-select2', [ModeOfArrivalsController::class, 'getArrivalJson'])->name('mode.of.arrival.select2');
    Route::post('countries-select2', [CountriesController::class, 'getCountriesJson'])->name('countries.select2');
    Route::post('source-of-arrival-select2', [SourceOfArrivalController::class, 'getArrivalJson'])->name('source.of.arrival.select2');
    Route::post('gender-select2', [GenderController::class, 'getGenderJson'])->name('gender.select2');
    Route::post('cities-select2', [CityController::class, 'getCitiesJson'])->name('cities.select2');
    Route::post('states-select2', [StatesController::class, 'getStatesJson'])->name('states.select2');

    // utilities
    Route::post('get-ship-first-date', [ModeOfArrivalsController::class, "getShipFirstDate"])->name("get-ship-first-date");

    //Finance Module
    Route::resource('transaction', TransactionController::class);
    Route::resource('gl_codes', GlCodeController::class);
    Route::resource('gl_codes_parent', GlCodesParentController::class);
    
    //Route::resource('gl_codes', GlCodeController::class)->only(['index']);
    
});
