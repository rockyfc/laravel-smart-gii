<?php

Route::get('/', 'ModelController@index')->name('gii.index');

Route::get('/model', 'ModelController@index')->name('gii.model.index');
Route::get('/model/fixer-show', 'ModelController@showfixer')->name('gii.model.fixer.show');
Route::post('/model/fixer', 'ModelController@fixer')->name('gii.model.fixer');

Route::get('/model/tables/{connection}', 'ModelController@tables')->name('gii.model.tables');
Route::get('/model/table-to-model/{table}', 'ModelController@tableToModel')->name('gii.model.table-to-model');
Route::post('/model/preview', 'ModelController@preview')->name('gii.model.preview');
Route::post('/model/generate', 'ModelController@generate')->name('gii.model.generate');

Route::get('/form', 'FormController@index')->name('gii.form.index');
Route::get('/form/classes/{namespace}', 'FormController@classes')->name('gii.form.classes');
Route::post('/form/preview', 'FormController@preview')->name('gii.form.preview');
Route::post('/form/generate', 'FormController@generate')->name('gii.form.generate');

Route::get('/repository', 'RepositoryController@index')->name('gii.repository.index');
Route::get('/repository/classes/{namespace}', 'RepositoryController@classes')->name('gii.repository.classes');
Route::post('/repository/preview', 'RepositoryController@preview')->name('gii.repository.preview');
Route::post('/repository/generate', 'RepositoryController@generate')->name('gii.repository.generate');

Route::get('/resource', 'ResourceController@index')->name('gii.resource.index');
Route::get('/resource/classes/{namespace}', 'ResourceController@classes')->name('gii.resource.classes');
Route::post('/resource/preview', 'ResourceController@preview')->name('gii.resource.preview');
Route::post('/resource/generate', 'ResourceController@generate')->name('gii.resource.generate');

Route::get('/ctrl', 'CtrlController@index')->name('gii.ctrl.index');
Route::get('/ctrl/classes/{class}', 'CtrlController@classes')->name('gii.ctrl.classes');
Route::post('/ctrl/preview', 'CtrlController@preview')->name('gii.ctrl.preview');
Route::post('/ctrl/generate', 'CtrlController@generate')->name('gii.ctrl.generate');

Route::get('/curd', 'CurdController@index')->name('gii.curd.index');
Route::get('/curd/classes-by-model/{model}', 'CurdController@guessByModel')->name('gii.curd.guess-by-model');
Route::get('/curd/classes-by-ctrl/{controller}', 'CurdController@guessByCtrl')->name('gii.curd.guess-by-ctrl');
Route::post('/curd/preview', 'CurdController@preview')->name('gii.curd.preview');
Route::post('/curd/generate', 'CurdController@generate')->name('gii.curd.generate');

Route::get('/sdk', 'SdkController@index')->name('gii.sdk.index');
Route::post('/sdk/preview', 'SdkController@preview')->name('gii.sdk.preview');
Route::post('/sdk/generate', 'SdkController@generate')->name('gii.sdk.generate');
Route::get('/sdk/download', 'SdkController@download')->name('gii.sdk.download');

Route::get('/tester', 'TesterController@index')->name('gii.tester.index');
