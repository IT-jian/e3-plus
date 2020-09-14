<?php
/**
 * hellow world
 */
namespace App\Http\Controllers;

/**
 * Class HelloworldController
 * @package App\Http\Controllers
 */
class HelloworldController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index($method = null)
    {
        return 'Hello World!';
    }
}
