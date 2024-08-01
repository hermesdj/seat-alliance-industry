<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'RecursiveTree\Seat\AllianceIndustry\Http\Controllers',
    'middleware' => ['web', 'auth', 'locale'],
    'prefix' => 'allianceindustry',
], function () {
    Route::get('/about', [
        'as' => 'allianceindustry.about',
        'uses' => 'AllianceIndustryController@about',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/orders', [
        'as' => 'allianceindustry.orders',
        'uses' => 'AllianceIndustryOrderController@orders',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/deliveries', [
        'as' => 'allianceindustry.deliveries',
        'uses' => 'AllianceIndustryDeliveryController@deliveries',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::get('/settings', [
        'as' => 'allianceindustry.settings',
        'uses' => 'AllianceIndustrySettingsController@settings',
        'middleware' => 'can:allianceindustry.settings'
    ]);

    Route::post('/settings/save', [
        'as' => 'allianceindustry.saveSettings',
        'uses' => 'AllianceIndustrySettingsController@saveSettings',
        'middleware' => 'can:allianceindustry.settings'
    ]);

    Route::get('/order/{id}/details', [
        'as' => 'allianceindustry.orderDetails',
        'uses' => 'AllianceIndustryOrderController@orderDetails',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::post('/order/{id}/reserveCorp', [
        'as' => 'allianceindustry.toggleReserveCorp',
        'uses' => 'AllianceIndustryOrderController@toggleReserveCorp',
        'middleware' => 'can:allianceindustry.corp_delivery'
    ]);

    Route::post('/order/{id}/confirmOrder', [
        'as' => 'allianceindustry.confirmOrder',
        'uses' => 'AllianceIndustryOrderController@confirmOrder',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/delivery/{id}/details', [
        'as' => 'allianceindustry.deliveryDetails',
        'uses' => 'AllianceIndustryDeliveryController@deliveryDetails',
        'middleware' => 'can:allianceindustry.view_orders'
    ]);

    Route::get('/order/{id}/deliveries/prepare', [
        'as' => 'allianceindustry.prepareDelivery',
        'uses' => 'AllianceIndustryDeliveryController@prepareDelivery',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/order/{id}/deliveries/add', [
        'as' => 'allianceindustry.addDelivery',
        'uses' => 'AllianceIndustryDeliveryController@addDelivery',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/order/delete', [
        'as' => 'allianceindustry.deleteOrder',
        'uses' => 'AllianceIndustryOrderController@deleteOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);


    Route::post('/deliveries/{deliveryId}/state', [
        'as' => 'allianceindustry.setDeliveryState',
        'uses' => 'AllianceIndustryDeliveryController@setDeliveryState',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/deliveries/{deliveryId}/state/{itemId}', [
        'as' => 'allianceindustry.setDeliveryItemState',
        'uses' => 'AllianceIndustryDeliveryController@setDeliveryItemState',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/delivery/{deliveryId}/delete', [
        'as' => 'allianceindustry.deleteDelivery',
        'uses' => 'AllianceIndustryDeliveryController@deleteDelivery',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::post('/delivery/{deliveryId}/delete/{itemId}', [
        'as' => 'allianceindustry.deleteDeliveryItem',
        'uses' => 'AllianceIndustryDeliveryController@deleteDeliveryItem',
        'middleware' => 'can:allianceindustry.create_deliveries'
    ]);

    Route::get('/orders/create', [
        'as' => 'allianceindustry.createOrder',
        'uses' => 'AllianceIndustryOrderController@createOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/{orderId}/update', [
        'as' => 'allianceindustry.updateOrderPrice',
        'uses' => 'AllianceIndustryOrderController@updateOrderPrice',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/{orderId}/extend', [
        'as' => 'allianceindustry.extendOrderTime',
        'uses' => 'AllianceIndustryOrderController@extendOrderTime',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/orders/submit', [
        'as' => 'allianceindustry.submitOrder',
        'uses' => 'AllianceIndustryOrderController@submitOrder',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::post('/user/orders/completed/delete', [
        'as' => 'allianceindustry.deleteCompletedOrders',
        'uses' => 'AllianceIndustryOrderController@deleteCompletedOrders',
        'middleware' => 'can:allianceindustry.create_orders'
    ]);

    Route::get('/priceprovider/buildtime')
        ->name('allianceindustry.priceprovider.buildtime.configuration')
        ->uses('AllianceIndustryController@buildTimePriceProviderConfiguration')
        ->middleware('can:pricescore.settings');

    Route::post('/priceprovider/buildtime')
        ->name('allianceindustry.priceprovider.buildtime.configuration.post')
        ->uses('AllianceIndustryController@buildTimePriceProviderConfigurationPost')
        ->middleware('can:pricescore.settings');
});