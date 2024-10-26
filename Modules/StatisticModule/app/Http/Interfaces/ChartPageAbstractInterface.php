<?php

namespace Modules\StatisticModule\Http\Interfaces;

interface ChartPageAbstractInterface
{
    public function newsletterChartBuilder($startDate, $endDate);
    public function wishlistChartBuilder($startDate, $endDate);
    public function downloadsChartBuilder($startDate, $endDate);
    public function likesChartBuilder($startDate, $endDate, $tofilm = false);
    public function commentariesChartBuilder($startDate, $endDate);
    public function bannersChartBuilder($startDate, $endDate);
    public function sponsorsChartBuilder($startDate, $endDate);
}
