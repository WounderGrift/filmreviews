<?php

namespace Modules\ListModule\Http\Controllers;

use App\Models\Categories;
use App\Models\Repacks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ListModule\Http\Abstractes\DynamicMenuAbstract;

class DynamicMenuController extends DynamicMenuAbstract
{
    const TITLE = "НАСТРОЙКА КАТЕГОРИЙ И РЕПАКОВ";
    const IN_OWNER_PANEL = true;

    public function index()
    {
        $categories   = Categories::query()->orderBy('id', 'ASC')->get();
        $repacks      = Repacks::query()->orderBy('id', 'ASC')->get();

        return view('listmodule::dynamic-menu', [
            'title' => self::TITLE,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'categories'   => $categories,
            'repacks'      => $repacks
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $categoriesAdd    = $request->input('categoriesAdd');
            $repacksAdd       = $request->input('repacksAdd');
            $categoriesRemove = $request->input('categoriesRemove');
            $repacksRemove    = $request->input('repacksRemove');

            if ($categoriesRemove)
                parent::categoriesItemsRemove($categoriesRemove);
            if ($repacksRemove)
                parent::repacksItemsRemove($repacksRemove);
            if ($categoriesAdd)
                parent::categoriesItemsAdd($categoriesAdd);
            if ($repacksAdd)
                parent::repacksItemsAdd($repacksAdd);

            DB::commit();
            return response()->json(['refresh' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Произошла ошибка при обновлении категорий и репаков: '
                . $e->getMessage()], 400, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
