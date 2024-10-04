<?php

namespace Modules\StatisticModule\Http\Interfaces;

use Illuminate\Http\Request;

interface ChartPageInterface
{
    public function profilesTable($search);
    public function profilesChart(Request $request);
    public function activityChart(Request $request);
    public function commentariesTable($search);
    public function bannersChart(Request $request);
    public function bannersTable();
}
