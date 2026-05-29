<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware('auth')->group(function ()
{
	Route::get('/', 'HomeController@index')->name('home')->middleware('dashboard');
	
	Route::patch('/change_password', 'UserController@updatePassword')->name('change_password');
	Route::view('/change_password', 'change_password');

	Route::middleware('can:isAdmin')->group(function ()
	{

		Route::get('/stuff/printall', 'StuffController@printall')->name('stuff.printall');
		Route::delete('/stuff/batch/destroy', 'StuffController@destroyBatch')->name('stuff.destroy-batch');

		Route::prefix('/ppn')->name('ppn.')->group(function ()
		{
			Route::get('/', 'PpnController@index')->name('index');
			Route::get('/count', 'PpnController@count')->name('count');
			Route::get('/export', 'PpnController@export')->name('export');
			Route::get('/print', 'PpnController@print')->name('print');
			Route::post('/datatables', 'PpnController@datatables')->name('datatables');
			Route::post('/store', 'PpnController@store')->name('store');
			Route::post('/import', 'PpnController@import')->name('import');
			Route::delete('/{ppn}/destroy', 'PpnController@destroy')->name('destroy');
		});

		Route::prefix('/user')->name('user.')->group(function ()
		{
			Route::post('/datatables', 'UserController@datatables')->name('datatables');
			Route::post('/select', 'UserController@select')->name('select');
		});

		Route::prefix('/stock')->name('stock.')->group(function ()
		{
			Route::get('/printall', 'StockController@printall')->name('printall');
			Route::get('/export', 'StockController@export')->name('export');
			Route::post('/import', 'StockController@import')->name('import');
			Route::delete('/{id}', 'StockController@destroy')->name('destroy');
		});

		Route::prefix('/detail_stock')->name('detail_stock.')->group(function ()
		{
			Route::get('/', 'StockController@detailStock')->name('index');
			Route::get('/printall', 'StockController@detailPrintall')->name('printall');
			Route::get('/export', 'StockController@detailExport')->name('export');
			Route::post('/import', 'StockController@detailImport')->name('import');
			Route::post('/datatables', 'StockController@detailDatatables')->name('datatables');
		});

		Route::prefix('/opname')->name('opname.')->group(function ()
		{
			Route::get('/export', 'OpnameController@export')->name('export');
			Route::get('/print', 'OpnameController@print')->name('print');
			Route::post('/datatables', 'OpnameController@datatables')->name('datatables');
			Route::post('/import', 'OpnameController@import')->name('import');
		});

		Route::prefix('/transaction')->name('transaction.')->group(function ()
		{
			Route::get('/printall', 'TransactionController@printAll')->name('printall');			
			Route::get('/export', 'TransactionController@export')->name('export');			
			Route::post('/import', 'TransactionController@import')->name('import');			
			Route::delete('/destroy/{id}', 'TransactionController@destroy')->name('destroy');
		});

		Route::prefix('/detail_transaction')->name('detail_transaction.')->group(function ()
		{
			Route::get('/', 'TransactionController@detailTransaction')->name('index');		
			Route::get('/printall', 'TransactionController@detailPrintall')->name('printall');
			Route::get('/export', 'TransactionController@detailExport')->name('export');
			Route::post('/import', 'TransactionController@detailImport')->name('import');
			Route::post('/datatables', 'TransactionController@detailDatatables')->name('datatables');
			Route::get('/searchobat', 'TransactionController@searchobat')->name('searchobat');
			Route::get('/searchnama', 'TransactionController@searchnama')->name('searchnama');
			Route::post('/aksisearchobat', 'TransactionController@aksisearchobat')->name('aksisearchobat');
			Route::post('/aksisearchnama', 'TransactionController@aksisearchnama')->name('aksisearchnama');
		});

	
		Route::prefix('/setting')->name('setting.')->group(function ()
		{
			Route::put('/', 'SettingController@update')->name('update');
			Route::get('/', 'SettingController@index')->name('index');
		});
	});

	Route::middleware('can:isAdminGudang')->group(function ()
	{
	
		Route::get('/notification', 'HomeController@notification')->name('notification');

		Route::prefix('/barcode')->name('barcode.')->group(function ()
		{
			Route::view('/search', 'barcode.search')->name('search');
			Route::get('/print', 'BarcodeController@print')->name('print');
		});

		Route::prefix('/stuff')->name('stuff.')->group(function ()
		{
			Route::get('/export', 'StuffController@export')->name('export');
			// Route::get('/printallbarcode', 'StuffController@printAllBarcode')->name('printallbarcode');
			Route::get('/barcode/{stuff}', 'StuffController@barcode')->name('barcode');
			Route::get('/barcode/{stuff}/print', 'StuffController@printBarcode')->name('barcode.print');
			Route::post('/import', 'StuffController@import')->name('import');
		});

		Route::prefix('/category')->name('category.')->group(function ()
		{
			Route::get('/export', 'CategoryController@export')->name('export');
			Route::post('/import', 'CategoryController@import')->name('import');
			Route::post('/datatables', 'CategoryController@datatables')->name('datatables');
			Route::post('/select', 'CategoryController@select')->name('select');
		});

		Route::prefix('/rack')->name('rack.')->group(function ()
		{
			Route::get('/export', 'RackController@export')->name('export');
			Route::post('/import', 'RackController@import')->name('import');
			Route::post('/datatables', 'RackController@datatables')->name('datatables');
			Route::post('/select', 'RackController@select')->name('select');
		});
		
		Route::prefix('/distributor')->name('distributor.')->group(function ()
		{
			Route::get('/export', 'DistributorController@export')->name('export');
			Route::post('/import', 'DistributorController@import')->name('import');
			Route::post('/datatables', 'DistributorController@datatables')->name('datatables');
			Route::post('/select', 'DistributorController@select')->name('select');
		});
		Route::prefix('/daylicash')->name('daylicash.')->group(function ()
		{
			Route::get('/export', 'DaylicashController@export')->name('export');
			Route::post('/import', 'DaylicashController@import')->name('import');
			Route::post('/datatables', 'DaylicashController@datatables')->name('datatables');
			Route::post('/select', 'DaylicashController@select')->name('select');
		});

		Route::prefix('/stock')->name('stock.')->group(function ()
		{
			Route::get('/', 'StockController@index')->name('index');
			
			Route::view('/create', 'stock.create')->name('create');
			Route::view('/edit/{id}', 'stock.edit')->name('edit');

			Route::get('/detail/{id}', 'StockController@detail')->name('detail');
			Route::get('/print/{id}', 'StockController@print')->name('print');

			Route::post('/datatables', 'StockController@datatables')->name('datatables');			
		});

		Route::prefix('/finance')->name('finance.')->group(function ()
		{
			Route::get('/printall', 'FinanceController@printall')->name('printall');
			Route::get('/export', 'FinanceController@export')->name('export');
			Route::post('/import', 'FinanceController@import')->name('import');
			Route::post('/datatables', 'FinanceController@datatables')->name('datatables');
		});

		//new cash harian
		Route::prefix('/cash')->name('cash.')->group(function ()
		{
			Route::get('/printall', 'CashController@printall')->name('printall');
			Route::get('/export', 'CashController@export')->name('export');
			Route::post('/import', 'CashController@import')->name('import');
			Route::post('/datatables', 'CashController@datatables')->name('datatables');
		});



		Route::prefix('/category_finance')->name('category_finance.')->group(function ()
		{
			Route::post('/datatables', 'CategoryFinanceController@datatables')->name('datatables');
			Route::post('/select', 'CategoryFinanceController@select')->name('select');
		});
	});
	
	Route::middleware('can:isAdminKasir')->group(function ()
	{

		Route::prefix('/transaction')->name('transaction.')->group(function ()
		{
			Route::get('/', 'TransactionController@index')->name('index');
			Route::view('/create', 'transaction.create')->name('create');
			
			Route::post('/datatables', 'TransactionController@datatables')->name('datatables');
			
			Route::get('/detail/{id}', 'TransactionController@detail')->name('detail');
			Route::get('/print/{id}', 'TransactionController@print')->name('print');
			Route::patch('/cancel/{id}', 'TransactionController@cancel')->name('cancel');
		});

	});

	Route::prefix('/stuff')->name('stuff.')->group(function ()
	{
		Route::post('/datatables', 'StuffController@datatables')->name('datatables');
		Route::post('/select', 'StuffController@select')->name('select');
	});
	
	Route::prefix('/report')->name('report.')->group(function ()
	{
		Route::middleware('can:isAdmin')->group(function ()
		{
			Route::get('/expend', 'ReportController@expend')->name('expend');
			Route::get('/accumulation', 'ReportController@accumulation')->name('accumulation');
			Route::get('/accumulation/new', 'ReportController@accumulationNew')->name('accumulation.new');
			Route::get('/transaction', 'ReportController@transaction')->name('transaction');
			Route::get('/sell', 'ReportController@sell')->name('sell');
			Route::get('/stock', 'ReportController@stock')->name('stock');
			Route::get('/buy', 'ReportController@buy')->name('buy');
			Route::get('/print/expend', 'ReportController@printExpend')->name('expend.print');
			Route::get('/print/accumulation', 'ReportController@printAccumulation')->name('accumulation.print');
			Route::get('/print/accumulation/new', 'ReportController@printAccumulationNew')->name('accumulation.print.new');
			Route::get('/print/transaction', 'ReportController@printTransaction')->name('transaction.print');
			Route::get('/print/sell', 'ReportController@printSell')->name('sell.print');
			Route::get('/print/stock', 'ReportController@printStock')->name('stock.print');
			Route::get('/print/buy', 'ReportController@printBuy')->name('buy.print');
		});

		Route::middleware('can:isAdminKasir')->group(function ()
		{
			Route::get('/transaction/today', 'ReportController@transactionToday')->name('transaction.today');
			Route::get('/transaction/month', 'ReportController@transactionMonth')->name('transaction.month');
			Route::get('/print/transaction/today', 'ReportController@printTransactionToday')->name('transaction.print.today');
			Route::get('/print/transaction/month', 'ReportController@printTransactionMonth')->name('transaction.print.month');
			Route::get('/sell/today', 'ReportController@sellToday')->name('sell.today');
			Route::get('/sell/month', 'ReportController@sellMonth')->name('sell.month');
			Route::get('/print/sell/today', 'ReportController@printSellToday')->name('sell.print.today');
			Route::get('/print/sell/month', 'ReportController@printSellMonth')->name('sell.print.month');
		});

		Route::middleware('can:isAdminGudang')->group(function ()
		{
			Route::get('/stock/today', 'ReportController@stockToday')->name('stock.today');
			Route::get('/stock/month', 'ReportController@stockMonth')->name('stock.month');
			Route::get('/buy/today', 'ReportController@buyToday')->name('buy.today');
			Route::get('/buy/month', 'ReportController@buyMonth')->name('buy.month');
			Route::get('/print/stock/today', 'ReportController@printStockToday')->name('stock.print.today');
			Route::get('/print/stock/month', 'ReportController@printStockMonth')->name('stock.print.month');
			Route::get('/print/buy/today', 'ReportController@printBuyToday')->name('buy.print.today');
			Route::get('/print/buy/month', 'ReportController@printBuyMonth')->name('buy.print.month');
		});

	});

	Route::resource('/category', 'CategoryController')->except(['create', 'edit', 'show'])->middleware('can:isAdminGudang');
	Route::resource('/rack', 'RackController')->except(['create', 'edit', 'show'])->middleware('can:isAdminGudang');
	Route::resource('/daylicash', 'DaylicashController')->except([ 'edit', 'show'])->middleware('can:isAdminGudang');
	Route::resource('/distributor', 'DistributorController')->except(['edit', 'show'])->middleware('can:isAdminGudang');
	Route::resource('/stuff', 'StuffController')->except(['edit'])->except(['edit'])->middleware('can:isAdminGudang');
	Route::resource('/user', 'UserController')->except(['edit', 'show'])->middleware('can:isAdmin');
	Route::resource('/finance', 'FinanceController')->except(['create', 'edit', 'show'])->middleware('can:isAdmin');
	Route::resource('/cash', 'CashController')->except(['create', 'edit', 'show'])->middleware('can:isAdmin');
	Route::resource('/category_finance', 'CategoryFinanceController')->except(['create', 'edit', 'show'])->middleware('can:isAdmin');
	Route::resource('/opname', 'OpnameController')->except(['show', 'update'])->middleware('can:isAdmin');
});

Route::namespace('Auth')->group(function ()
{
	Route::get('/login', 'LoginController@showLoginForm');
	Route::post('/login', 'LoginController@login')->name('login');
	
	Route::get('/logout', 'LoginController@logout')->name('logout');
});