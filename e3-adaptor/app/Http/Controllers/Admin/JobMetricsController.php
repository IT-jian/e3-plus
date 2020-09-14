<?php


namespace App\Http\Controllers\Admin;


use Laravel\Horizon\Http\Controllers\JobMetricsController as Controller;

class JobMetricsController extends Controller
{
    /**
     * Get metrics for a given job.
     *
     * @param string $slug
     * @return \Illuminate\Support\Collection
     */
    public function show($slug)
    {
        $slug = urldecode($slug);

        return parent::show($slug);
    }
}