<?php

namespace App\Http\Controllers;

use App\Models\ExpendCategory as Category;
use App\Services\CategoryFinanceService;
use App\Http\Requests\CategoryFinance\CreateCategoryRequest;
use App\Http\Requests\CategoryFinance\UpdateCategoryRequest;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CategoryFinanceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        return view('expend.category.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        Category::create($request->all());

        return response()->json(['success' => 'Sukses Menambahkan Data Kategori']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        Category::findOrfail($id)->update($request->all());

        return response()->json(['success' => 'Sukses Mengedit Data Kategori']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        Category::destroy($id);

        return response()->json(['success' => 'Sukses Menghapus Data Kategori']);
    }

    // Get Datatables
    public function datatables(CategoryFinanceService $category): Object
    {
        return $category->getDatatables();
    }

    // Select Data
    public function select(CategoryFinanceService $category, Request $request): Object
    {
        return $category->selectData($request->name);
    }
}
